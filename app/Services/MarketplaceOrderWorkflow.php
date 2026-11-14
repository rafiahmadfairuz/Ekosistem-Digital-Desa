<?php

namespace App\Services;

use App\Models\Delivery;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\Product;
use App\Models\Seller;
use App\Models\SellerBalanceEntry;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MarketplaceOrderWorkflow
{
    public function __construct(private readonly AuditLogger $audit, private readonly MarketplaceNotifier $notifier) {}

    public function sellerDecision(User $seller, Order $order, string $decision, ?string $reason): Order
    {
        abort_unless($seller->can('manage', $order), 403);
        if ($decision === 'rejected' && trim((string) $reason) === '') {
            throw ValidationException::withMessages(['reason' => 'Alasan penolakan pesanan wajib diisi.']);
        }

        return DB::transaction(function () use ($seller, $order, $decision, $reason): Order {
            $locked = Order::query()->whereKey($order->getKey())->lockForUpdate()->with(['seller', 'buyer', 'items', 'payment'])->firstOrFail();
            abort_unless($seller->can('manage', $locked) && $locked->status === 'pending_seller', 409);
            abort_unless(in_array($decision, ['accepted', 'rejected'], true), 422);
            $from = $locked->status;
            $to = $decision;
            $locked->forceFill([
                'status' => $to,
                'accepted_at' => $to === 'accepted' ? now() : null,
                'rejection_reason' => $to === 'rejected' ? trim((string) $reason) : null,
            ])->save();
            $locked->statusEvents()->create([
                'actor_id' => $seller->getKey(), 'from_status' => $from, 'to_status' => $to,
                'reason' => $reason === null ? null : trim($reason), 'created_at' => now(),
            ]);

            if ($to === 'rejected') {
                $this->restoreStock($locked, $seller);
                $payment = Payment::query()->where('order_id', $locked->getKey())->lockForUpdate()->firstOrFail();
                if ($payment->status === 'pending') {
                    $payment->forceFill(['status' => 'cancelled'])->save();
                    PaymentEvent::query()->create([
                        'payment_id' => $payment->getKey(),
                        'event_type' => $payment->payment_provider === 'xendit' ? 'xendit.order_rejected' : 'cod.order_rejected',
                        'signature_verified' => false,
                        'received_at' => now(),
                    ]);
                }
            }

            $this->audit->record('marketplace.seller_order_decision', 'order', (int) $locked->getKey(), ['decision' => $decision]);
            $this->notifier->toUser($locked->buyer, 'Pembaruan pesanan', $to === 'accepted'
                ? 'Penjual menerima pesanan Anda.'
                : 'Penjual menolak pesanan. Buka rincian pesanan untuk membaca alasannya.', 'order', (int) $locked->getKey());

            return $locked;
        });
    }

    public function advanceOrder(User $seller, Order $order, string $nextStatus): Order
    {
        abort_unless($seller->can('manage', $order), 403);

        return DB::transaction(function () use ($seller, $order, $nextStatus): Order {
            $locked = Order::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($seller->can('manage', $locked), 403);
            $allowed = [
                'accepted' => ['processing'],
                'processing' => ['ready_for_delivery'],
            ];
            abort_unless(in_array($nextStatus, $allowed[$locked->status] ?? [], true), 409, 'Tahap pesanan tidak dapat dilewati.');
            $from = $locked->status;
            $locked->forceFill(['status' => $nextStatus])->save();
            $locked->statusEvents()->create(['actor_id' => $seller->getKey(), 'from_status' => $from, 'to_status' => $nextStatus, 'created_at' => now()]);
            if ($nextStatus === 'ready_for_delivery') {
                $delivery = $locked->delivery()->lockForUpdate()->firstOrFail();
                $delivery->forceFill(['delivery_status' => 'ready'])->save();
                $delivery->events()->create(['actor_id' => $seller->getKey(), 'from_status' => 'preparing', 'to_status' => 'ready', 'created_at' => now()]);
            }
            $this->audit->record('marketplace.order_status_changed', 'order', (int) $locked->getKey(), ['from_status' => $from, 'to_status' => $nextStatus]);

            return $locked;
        });
    }

    public function updateDelivery(User $seller, Order $order, string $nextStatus, ?string $reference, ?string $note): Delivery
    {
        abort_unless($seller->can('manage', $order), 403);

        return DB::transaction(function () use ($seller, $order, $nextStatus, $reference, $note): Delivery {
            $lockedOrder = Order::query()->whereKey($order->getKey())->lockForUpdate()->with('seller')->firstOrFail();
            abort_unless($seller->can('manage', $lockedOrder), 403);
            $delivery = Delivery::query()->where('order_id', $lockedOrder->getKey())->lockForUpdate()->firstOrFail();
            $allowed = match ($delivery->delivery_status) {
                'ready' => $delivery->delivery_method === 'pickup' ? ['delivered', 'failed'] : ['in_delivery', 'failed'],
                'in_delivery' => ['delivered', 'failed'],
                'failed' => $delivery->delivery_method === 'pickup' ? ['delivered'] : ['in_delivery'],
                default => [],
            };
            abort_unless(in_array($nextStatus, $allowed, true), 409, 'Status pengiriman tidak dapat dilewati.');
            $from = $delivery->delivery_status;
            $delivery->forceFill([
                'delivery_status' => $nextStatus,
                'delivery_reference' => $reference === null ? $delivery->delivery_reference : trim($reference),
                'delivered_at' => $nextStatus === 'delivered' ? now() : $delivery->delivered_at,
            ])->save();
            $delivery->events()->create([
                'actor_id' => $seller->getKey(), 'from_status' => $from, 'to_status' => $nextStatus,
                'note' => $note === null ? null : trim($note), 'created_at' => now(),
            ]);
            if ($nextStatus === 'in_delivery') {
                $this->setOrderStatus($lockedOrder, $seller, 'in_delivery');
            } elseif ($nextStatus === 'delivered') {
                $this->setOrderStatus($lockedOrder, $seller, 'delivered');
            } elseif ($nextStatus === 'failed') {
                $this->setOrderStatus($lockedOrder, $seller, 'delivery_failed');
            }
            $this->audit->record('marketplace.delivery_status_changed', 'order', (int) $lockedOrder->getKey(), ['from_status' => $from, 'to_status' => $nextStatus]);
            $this->notifier->toUser($lockedOrder->buyer, 'Pembaruan pengiriman', match ($nextStatus) {
                'in_delivery' => 'Pesanan Anda telah diserahkan untuk diantar.',
                'delivered' => 'Penjual menandai pesanan telah diserahkan. Konfirmasikan setelah Anda menerima barang.',
                'failed' => 'Pengiriman mengalami kendala. Hubungi penjual untuk tindak lanjut.',
                default => 'Status pengiriman diperbarui.',
            }, 'order', (int) $lockedOrder->getKey());

            return $delivery;
        });
    }

    public function cancelByBuyer(User $buyer, Order $order, string $reason): Order
    {
        abort_unless($buyer->can('cancel', $order), 403);

        return DB::transaction(function () use ($buyer, $order, $reason): Order {
            $locked = Order::query()->whereKey($order->getKey())->lockForUpdate()->with('items')->firstOrFail();
            abort_unless($buyer->can('cancel', $locked), 409);
            $from = $locked->status;
            $locked->forceFill(['status' => 'cancelled', 'cancellation_reason' => trim($reason)])->save();
            $locked->statusEvents()->create(['actor_id' => $buyer->getKey(), 'from_status' => $from, 'to_status' => 'cancelled', 'reason' => trim($reason), 'created_at' => now()]);
            $this->restoreStock($locked, $buyer);
            $payment = Payment::query()->where('order_id', $locked->getKey())->lockForUpdate()->first();
            if ($payment !== null && $payment->status === 'pending') {
                $payment->forceFill(['status' => 'cancelled'])->save();
                PaymentEvent::query()->create(['payment_id' => $payment->getKey(), 'event_type' => 'cod.buyer_cancelled', 'signature_verified' => false, 'received_at' => now()]);
            }
            $this->audit->record('marketplace.buyer_cancelled_order', 'order', (int) $locked->getKey());
            $this->notifier->toUser($locked->seller->user, 'Pesanan dibatalkan', 'Pembeli membatalkan pesanan sebelum diproses.', 'order', (int) $locked->getKey());

            return $locked;
        });
    }

    public function confirmReceipt(User $buyer, Order $order): Order
    {
        abort_unless($buyer->can('confirmReceipt', $order), 403);

        return DB::transaction(function () use ($buyer, $order): Order {
            $locked = Order::query()->whereKey($order->getKey())->lockForUpdate()->with('delivery')->firstOrFail();
            abort_unless($buyer->can('confirmReceipt', $locked), 409);
            $delivery = Delivery::query()->where('order_id', $locked->getKey())->lockForUpdate()->firstOrFail();
            $delivery->forceFill(['delivery_status' => 'confirmed', 'buyer_confirmed_at' => now()])->save();
            $delivery->events()->create(['actor_id' => $buyer->getKey(), 'from_status' => 'delivered', 'to_status' => 'confirmed', 'created_at' => now()]);
            $locked->forceFill(['status' => 'completed', 'completed_at' => now()])->save();
            $locked->statusEvents()->create(['actor_id' => $buyer->getKey(), 'from_status' => 'delivered', 'to_status' => 'completed', 'created_at' => now()]);
            $this->creditSellerBalance($locked);
            $this->audit->record('marketplace.buyer_receipt_confirmed', 'order', (int) $locked->getKey());
            $this->notifier->toUser($locked->seller->user, 'Pesanan selesai', 'Pembeli telah mengonfirmasi penerimaan pesanan.', 'order', (int) $locked->getKey());

            return $locked;
        });
    }

    public function confirmCodCollection(User $actor, Payment $payment): Payment
    {
        $payment->loadMissing('order.seller');
        $order = $payment->order;
        abort_unless($order !== null && $payment->payment_provider === 'cod', 404);
        $isSeller = (int) $order->seller?->user_id === (int) $actor->getKey()
            && $actor->hasRole('Seller')
            && $actor->account_status === 'active';
        $isActiveStaff = $actor->account_status === 'active'
            && ($actor->hasRole('BUMDes Manager') || $actor->hasRole('BUMDes Operator'))
            && $actor->bumdesStaff()->where('employment_status', 'active')->exists();
        abort_unless($isSeller || $isActiveStaff, 403);

        return DB::transaction(function () use ($actor, $payment, $order): Payment {
            $lockedOrder = Order::query()->whereKey($order->getKey())->lockForUpdate()->with('delivery')->firstOrFail();
            $lockedPayment = Payment::query()->whereKey($payment->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($lockedPayment->status === 'pending'
                && in_array($lockedOrder->status, ['delivered', 'completed'], true)
                && $lockedOrder->delivery?->delivery_status === 'confirmed', 409, 'COD hanya dapat dikonfirmasi setelah penerimaan barang dikonfirmasi pembeli.');
            $lockedPayment->forceFill(['status' => 'paid', 'paid_at' => now(), 'submitted_by' => $actor->getKey()])->save();
            PaymentEvent::query()->create([
                'payment_id' => $lockedPayment->getKey(),
                'event_type' => 'cod.collection_confirmed',
                'event_reference' => 'cod-collection-'.$lockedPayment->getKey(),
                'signature_verified' => false,
                'received_at' => now(),
            ]);
            if ($lockedOrder->status === 'completed') {
                $this->creditSellerBalance($lockedOrder);
            }
            $this->audit->record('marketplace.cod_collection_confirmed', 'payment', (int) $lockedPayment->getKey(), ['order_id' => (int) $lockedOrder->getKey()]);
            $this->notifier->toUser($lockedOrder->buyer, 'Pembayaran COD dikonfirmasi', 'Pengumpulan pembayaran tunai untuk pesanan Anda telah dikonfirmasi.', 'order', (int) $lockedOrder->getKey());

            return $lockedPayment;
        });
    }

    private function setOrderStatus(Order $order, User $actor, string $status): void
    {
        $from = $order->status;
        $order->forceFill(['status' => $status])->save();
        $order->statusEvents()->create(['actor_id' => $actor->getKey(), 'from_status' => $from, 'to_status' => $status, 'created_at' => now()]);
    }

    private function creditSellerBalance(Order $order): void
    {
        $seller = Seller::query()->whereKey($order->seller_id)->lockForUpdate()->firstOrFail();
        $paymentIsPaid = Payment::query()->where('status', 'paid')->where(function ($query) use ($order): void {
            $query->where('order_id', $order->getKey());
            if ($order->checkout_group_id !== null) {
                $query->orWhere('checkout_group_id', $order->checkout_group_id);
            }
        })->exists();
        if (! $paymentIsPaid) {
            return;
        }

        SellerBalanceEntry::query()->firstOrCreate(
            ['order_id' => $order->getKey(), 'entry_type' => 'sale_earning'],
            [
                'seller_id' => $seller->getKey(),
                'reference_id' => 'sale-order-'.$order->getKey(),
                'amount' => $order->total_amount,
                'currency' => 'IDR',
                'description' => 'Pendapatan pesanan '.$order->order_number,
            ]
        );
    }

    private function restoreStock(Order $order, User $actor): void
    {
        foreach ($order->items as $item) {
            $product = Product::withTrashed()->whereKey($item->product_id)->lockForUpdate()->first();
            if ($product === null) {
                continue;
            }
            $from = $product->status;
            $product->increment('stock', (int) $item->quantity);
            if ($from === 'out_of_stock' && (int) $product->fresh()->stock > 0 && $product->category?->is_active) {
                $product->forceFill(['status' => 'active'])->save();
                ProductModerationEvent::query()->create([
                    'product_id' => $product->getKey(), 'actor_id' => $actor->getKey(),
                    'from_status' => $from, 'to_status' => 'active', 'created_at' => now(),
                ]);
            }
        }
    }
}
