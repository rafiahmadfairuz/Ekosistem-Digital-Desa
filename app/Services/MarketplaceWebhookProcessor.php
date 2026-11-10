<?php

namespace App\Services;

use App\Contracts\MarketplacePaymentProvider;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\PaymentAllocation;
use App\Models\Order;
use App\Models\OrderStatusEvent;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarketplaceWebhookProcessor
{
    public function __construct(
        private readonly MarketplacePaymentProvider $provider,
        private readonly AuditLogger $audit,
        private readonly MarketplaceNotifier $notifier,
    ) {}

    /** @param array<string, mixed> $payload
     * @param  array<string, string>  $headers
     */
    public function process(array $payload, array $headers): bool
    {
        $verified = $this->provider->verifyWebhook($payload, $headers);
        abort_unless($verified !== null, 403, 'Notifikasi pembayaran tidak dapat diverifikasi.');

        if (($verified['type'] ?? null) === 'split') {
            return $this->processSplit($verified);
        }

        foreach (['provider_reference', 'order_reference', 'amount', 'currency', 'status', 'event_reference'] as $required) {
            abort_unless(isset($verified[$required]) && is_string($verified[$required]) && trim($verified[$required]) !== '', 422, 'Data hasil verifikasi pembayaran tidak lengkap.');
        }
        abort_unless(in_array($verified['status'], ['paid', 'failed', 'cancelled'], true), 422, 'Status pembayaran dari penyedia tidak didukung.');
        abort_unless($verified['currency'] === 'IDR', 422, 'Mata uang pembayaran tidak cocok.');

        try {
            return DB::transaction(function () use ($verified): bool {
                $payments = Payment::query()->where('provider_reference', $verified['provider_reference'])->orderBy('id')->lockForUpdate()->get();
                abort_unless($payments->count() === 1, 409, 'Referensi pembayaran tidak mengarah ke satu transaksi yang pasti.');
                $payment = $payments->firstOrFail();
                abort_unless($payment->invoice_id === null && (($payment->order_id !== null && $payment->checkout_group_id === null) || ($payment->order_id === null && $payment->checkout_group_id !== null)), 409, 'Pembayaran bukan bagian dari checkout marketplace.');
                abort_unless($payment->payment_method === 'gateway' && $payment->payment_provider !== 'cod', 409, 'Pembayaran ini tidak menggunakan gateway online.');
                $payment->loadMissing('order', 'checkoutGroup.orders');
                $orderReference = $payment->checkoutGroup?->checkout_number ?? $payment->order?->order_number;
                abort_unless(is_string($orderReference) && hash_equals($orderReference, $verified['order_reference']), 409, 'Referensi pesanan tidak cocok dengan pembayaran.');

                $existingEvent = PaymentEvent::query()->where('event_reference', $verified['event_reference'])->first();
                if ($existingEvent !== null) {
                    abort_unless((int) $existingEvent->payment_id === (int) $payment->getKey(), 409, 'Referensi notifikasi telah digunakan untuk pembayaran lain.');

                    return false;
                }

                abort_unless(in_array($payment->status, ['pending', 'authorized'], true), 409, 'Status pembayaran tidak dapat diubah dari tahap saat ini.');
                if ($this->toCents((string) $payment->amount) !== $this->toCents($verified['amount'])) {
                    throw ValidationException::withMessages(['payment' => 'Jumlah yang dikonfirmasi penyedia tidak cocok dengan pesanan.']);
                }

                $payment->forceFill([
                    'status' => $verified['status'],
                    'paid_at' => $verified['status'] === 'paid' ? now() : null,
                ])->save();
                PaymentEvent::query()->create([
                    'payment_id' => $payment->getKey(),
                    'event_type' => 'gateway.'.$verified['status'],
                    'event_reference' => $verified['event_reference'],
                    'payload' => ['status' => $verified['status'], 'currency' => 'IDR'],
                    'signature_verified' => true,
                    'received_at' => now(),
                ]);
                $this->audit->record('marketplace.gateway_payment_updated', 'payment', (int) $payment->getKey(), ['status' => $verified['status']]);
                if ($payment->checkout_group_id !== null) {
                    $group = $payment->checkoutGroup()->lockForUpdate()->firstOrFail();
                    $group->forceFill(['status' => $verified['status'] === 'paid' ? 'settlement_pending' : 'payment_failed'])->save();
                    if ($verified['status'] === 'paid') {
                        $this->publishGroupOrdersIfSplit($payment);
                        $this->notifier->toUser($group->buyer, 'Pembayaran diterima', 'Pembayaran checkout diterima. Pesanan akan diteruskan setelah pembagian dana ke setiap penjual terkonfirmasi.', 'checkout_group', (int) $group->getKey());
                    } else {
                        $this->cancelUnpaidGroup($payment);
                        $this->notifier->toUser($group->buyer, 'Pembayaran tidak selesai', 'Pembayaran checkout kedaluwarsa atau gagal. Stok telah dilepas dan penjual tidak menerima pesanan.', 'checkout_group', (int) $group->getKey());
                    }
                } else {
                    $payment->loadMissing('order.buyer');
                    $this->notifier->toUser($payment->order->buyer, 'Pembaruan pembayaran', $verified['status'] === 'paid'
                        ? 'Penyedia pembayaran telah mengonfirmasi pembayaran pesanan.'
                        : 'Penyedia pembayaran tidak menyelesaikan transaksi. Hubungi penjual sebelum mengirim pembayaran lain.', 'order', (int) $payment->order_id);
                }

                return true;
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            $payment = Payment::query()->where('provider_reference', $verified['provider_reference'])->first();
            $event = PaymentEvent::query()->where('event_reference', $verified['event_reference'])->first();
            if ($payment !== null && $event !== null && (int) $event->payment_id === (int) $payment->getKey()) {
                return false;
            }

            throw $exception;
        }
    }

    /** @param array<string, mixed> $verified */
    private function processSplit(array $verified): bool
    {
        abort_unless(($verified['currency'] ?? null) === 'IDR', 422, 'Mata uang alokasi tidak cocok.');

        try {
            return DB::transaction(function () use ($verified): bool {
                $payment = Payment::query()->where('provider_reference', $verified['provider_reference'])
                    ->orWhereHas('checkoutGroup', fn ($query) => $query->where('checkout_number', $verified['provider_reference']))
                    ->lockForUpdate()->firstOrFail();
                abort_unless($payment->checkout_group_id !== null && $payment->xendit_split_rule_id === $verified['split_rule_id'], 409, 'Notifikasi split tidak cocok dengan checkout.');
                $allocation = PaymentAllocation::query()->where('payment_id', $payment->getKey())
                    ->where('route_reference', $verified['route_reference'])->lockForUpdate()->firstOrFail();
                abort_unless(hash_equals($allocation->destination_account_id, $verified['destination_account_id'])
                    && $this->toCents((string) $allocation->amount) === $this->toCents($verified['amount']), 409, 'Nilai atau tujuan alokasi tidak cocok dengan catatan checkout.');

                $existing = PaymentEvent::query()->where('event_reference', $verified['event_reference'])->first();
                if ($existing !== null) {
                    abort_unless((int) $existing->payment_id === (int) $payment->getKey(), 409, 'Notifikasi split sudah digunakan oleh pembayaran lain.');

                    return false;
                }
                abort_unless($allocation->status === 'pending' || $allocation->status === $verified['status'], 409, 'Status alokasi tidak dapat diubah.');
                $allocation->forceFill([
                    'status' => $verified['status'],
                    'provider_split_reference' => $verified['split_reference'],
                    'failure_code' => $verified['failure_code'],
                    'completed_at' => $verified['status'] === 'completed' ? now() : null,
                ])->save();
                PaymentEvent::query()->create([
                    'payment_id' => $payment->getKey(),
                    'event_type' => 'xendit.split.'.$verified['status'],
                    'event_reference' => $verified['event_reference'],
                    'payload' => ['route_reference' => $allocation->route_reference, 'status' => $verified['status'], 'currency' => 'IDR'],
                    'signature_verified' => true,
                    'received_at' => now(),
                ]);

                if ($verified['status'] === 'failed') {
                    $payment->checkoutGroup()->update(['status' => 'settlement_failed']);
                    $group = $payment->checkoutGroup()->with('buyer')->firstOrFail();
                    $this->notifier->toUser($group->buyer, 'Pembagian dana perlu ditinjau', 'Pembayaran diterima, tetapi pembagian dana ke salah satu penjual gagal. Pesanan belum diteruskan; hubungi pengelola untuk rekonsiliasi.', 'checkout_group', (int) $group->getKey());
                } else {
                    $this->publishGroupOrdersIfSplit($payment);
                }
                $this->audit->record('marketplace.payment_allocation_updated', 'payment_allocation', (int) $allocation->getKey(), ['status' => $verified['status']]);

                return true;
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            if (PaymentEvent::query()->where('event_reference', $verified['event_reference'])->exists()) {
                return false;
            }

            throw $exception;
        }
    }

    private function publishGroupOrdersIfSplit(Payment $payment): void
    {
        $payment->loadMissing('checkoutGroup.orders.seller.user', 'allocations');
        if ($payment->status !== 'paid' || $payment->allocations->isEmpty() || $payment->allocations->contains(fn (PaymentAllocation $allocation): bool => $allocation->status !== 'completed')) {
            return;
        }
        $group = $payment->checkoutGroup;
        if ($group->status === 'pending_seller') {
            return;
        }
        $group->forceFill(['status' => 'pending_seller'])->save();
        foreach ($group->orders as $order) {
            if ($order->status !== 'awaiting_payment') {
                continue;
            }
            $order->forceFill(['status' => 'pending_seller'])->save();
            OrderStatusEvent::query()->create(['order_id' => $order->getKey(), 'actor_id' => null, 'from_status' => 'awaiting_payment', 'to_status' => 'pending_seller', 'created_at' => now()]);
            $this->notifier->toUser($order->seller->user, 'Pesanan lunas siap diproses', 'Pembayaran checkout dan alokasi dana untuk pesanan Anda telah dikonfirmasi.', 'order', (int) $order->getKey());
            $this->notifier->toUser($group->buyer, 'Pesanan diteruskan', 'Pesanan telah diteruskan kepada setiap penjual.', 'order', (int) $order->getKey());
        }
    }

    private function cancelUnpaidGroup(Payment $payment): void
    {
        $payment->loadMissing('checkoutGroup.orders.items');
        foreach ($payment->checkoutGroup->orders as $order) {
            if ($order->status !== 'awaiting_payment') {
                continue;
            }
            $order->forceFill(['status' => 'cancelled', 'cancellation_reason' => 'Pembayaran checkout tidak selesai.'])->save();
            OrderStatusEvent::query()->create(['order_id' => $order->getKey(), 'actor_id' => null, 'from_status' => 'awaiting_payment', 'to_status' => 'cancelled', 'reason' => 'Pembayaran checkout tidak selesai.', 'created_at' => now()]);
            foreach ($order->items as $item) {
                $product = \App\Models\Product::query()->whereKey($item->product_id)->lockForUpdate()->first();
                if ($product !== null) {
                    $product->increment('stock', (int) $item->quantity);
                    if ($product->status === 'out_of_stock') {
                        $product->forceFill(['status' => 'active'])->save();
                    }
                }
            }
        }
    }

    private function toCents(string $amount): int
    {
        if (! preg_match('/^\d{1,12}(?:\.\d{1,2})?$/', $amount)) {
            throw ValidationException::withMessages(['payment' => 'Format jumlah pembayaran tidak valid.']);
        }
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }
}
