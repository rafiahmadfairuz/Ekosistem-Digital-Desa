<?php

namespace App\Services;

use App\Contracts\MarketplacePaymentProvider;
use App\Models\CheckoutGroup;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentAllocation;
use App\Models\PaymentEvent;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class XenditMarketplacePaymentProvider implements MarketplacePaymentProvider
{
    public function createCashOnDeliveryPayment(Order $order): Payment
    {
        return app(SimulatedMarketplacePaymentProvider::class)->createCashOnDeliveryPayment($order);
    }

    public function createPendingGatewayPayment(Order $order): Payment
    {
        return Payment::query()->create([
            'order_id' => $order->getKey(),
            'invoice_id' => null,
            'payment_method' => 'gateway',
            'payment_provider' => 'xendit',
            'amount' => $order->total_amount,
            'status' => 'pending',
        ]);
    }

    public function createPendingCheckoutGroupPayment(CheckoutGroup $group): Payment
    {
        $group->loadMissing('orders.seller');
        abort_unless($group->orders->isNotEmpty(), 409, 'Checkout tidak memiliki pesanan untuk dibayar.');
        $payoutBasisPoints = $this->sellerPayoutBasisPoints();

        $payment = Payment::query()->create([
            'checkout_group_id' => $group->getKey(),
            'invoice_id' => null,
            'order_id' => null,
            'payment_method' => 'gateway',
            'payment_provider' => 'xendit',
            'amount' => $group->grand_total,
            'platform_retained_amount' => '0.00',
            'status' => 'pending',
        ]);

        $allocatedCents = 0;
        $groupTotalCents = $this->toCents((string) $group->grand_total);
        foreach ($group->orders as $order) {
            $accountId = (string) $order->seller?->xendit_account_id;
            abort_unless($accountId !== '', 409, 'Akun penjual belum terhubung ke Xendit XenPlatform.');
            // Xendit split routes are IDR whole-rupiah amounts. Round each seller route down;
            // the remaining rupiah stay with the platform along with the fee reserve.
            $allocationCents = intdiv($this->toCents((string) $order->total_amount) * $payoutBasisPoints, 1_000_000) * 100;
            abort_unless($allocationCents > 0, 422, 'Nilai pencairan penjual terlalu kecil setelah pembulatan.');
            $allocatedCents += $allocationCents;
            PaymentAllocation::query()->create([
                'payment_id' => $payment->getKey(),
                'order_id' => $order->getKey(),
                'seller_id' => $order->seller_id,
                'destination_account_id' => $accountId,
                'route_reference' => 'alloc-'.$payment->getKey().'-'.$order->getKey(),
                'amount' => $this->fromCents($allocationCents),
                'status' => 'pending',
            ]);
        }
        abort_unless($allocatedCents < $groupTotalCents, 422, 'Pembagian dana harus menyisakan saldo untuk biaya Xendit.');
        $payment->forceFill(['platform_retained_amount' => $this->fromCents($groupTotalCents - $allocatedCents)])->save();

        return $payment;
    }

    public function createOnlinePaymentSession(Payment $payment): Payment
    {
        abort_unless($payment->payment_provider === 'xendit' && $payment->payment_method === 'gateway', 409);

        return DB::transaction(function () use ($payment): Payment {
            $lockedPayment = Payment::query()->whereKey($payment->getKey())->lockForUpdate()->with(['order.items', 'checkoutGroup.orders.items', 'allocations'])->firstOrFail();
            abort_unless($lockedPayment->payment_provider === 'xendit' && $lockedPayment->payment_method === 'gateway', 409);
            abort_unless($lockedPayment->status === 'pending', 409, 'Status pembayaran ini tidak dapat diproses.');

            if ($lockedPayment->checkout_url !== null && $lockedPayment->provider_reference !== null) {
                return $lockedPayment;
            }

            $secretKey = (string) config('services.xendit.secret_key');
            $webhookToken = (string) config('services.xendit.webhook_token');
            abort_if($secretKey === '' || $webhookToken === '', 503, 'Kunci API dan token webhook Xendit harus dikonfigurasi sebelum pembayaran diaktifkan.');
            $group = $lockedPayment->checkoutGroup;
            $order = $lockedPayment->order;
            abort_unless(($group !== null && $order === null) || ($group === null && $order !== null), 409, 'Pembayaran harus terhubung ke tepat satu pesanan atau checkout.');
            if ($group !== null) {
                abort_unless($group->orders->isNotEmpty() && $lockedPayment->allocations->isNotEmpty(), 409, 'Alokasi pembayaran penjual belum tersedia.');
                $allocatedCents = $lockedPayment->allocations->sum(fn (PaymentAllocation $allocation): int => $this->toCents((string) $allocation->amount));
                $totalCents = $this->toCents((string) $lockedPayment->amount);
                $retainedCents = $this->toCents((string) $lockedPayment->platform_retained_amount);
                abort_unless($allocatedCents > 0 && $allocatedCents + $retainedCents === $totalCents && $retainedCents > 0, 409, 'Alokasi penjual dan saldo cadangan platform harus sama dengan total checkout serta menyisakan biaya transaksi.');
                foreach ($lockedPayment->allocations as $allocation) {
                    abort_unless($allocation->destination_account_id !== '', 409, 'Akun tujuan pembayaran salah satu penjual belum terhubung.');
                }
            }
            $buyerId = $group?->buyer_id ?? $order?->buyer_id;
            abort_unless((int) $buyerId > 0, 409);
            $totalAmount = (string) $lockedPayment->amount;
            abort_unless(preg_match('/^\d{1,12}\.00$/', $totalAmount) === 1, 422, 'Xendit IDR hanya mendukung nilai rupiah bulat untuk pesanan ini.');

            $orderItems = $group !== null ? $group->orders->flatMap->items : $order->items;
            $items = $orderItems->map(function ($item): array {
                $unitPrice = (string) $item->unit_price;
                abort_unless(preg_match('/^\d{1,12}\.00$/', $unitPrice) === 1, 422, 'Harga produk harus berupa rupiah bulat untuk pembayaran Xendit.');

                return [
                    'reference_id' => 'order-item-'.$item->getKey(),
                    'name' => mb_substr($item->product_name_snapshot, 0, 255),
                    'type' => 'PHYSICAL_PRODUCT',
                    'category' => mb_substr((string) data_get($item->attributes_snapshot, 'category', 'Produk'), 0, 255),
                    'net_unit_amount' => (int) explode('.', $unitPrice, 2)[0],
                    'quantity' => (int) $item->quantity,
                    'currency' => 'IDR',
                ];
            })->all();

            $payload = [
                'reference_id' => $group?->checkout_number ?? $order->order_number,
                'session_type' => 'PAY',
                'mode' => 'PAYMENT_LINK',
                'amount' => (int) explode('.', $totalAmount, 2)[0],
                'currency' => 'IDR',
                'country' => 'ID',
                'locale' => 'id',
                'description' => 'Pembayaran checkout '.($group?->checkout_number ?? $order->order_number),
                'allow_save_payment_method' => 'DISABLED',
                'items' => $items,
                'metadata' => $group !== null ? ['checkout_number' => $group->checkout_number] : ['order_number' => $order->order_number],
            ];

            $successUrl = $group !== null ? route('marketplace.orders.group', $group) : route('marketplace.orders.show', $order);
            if (str_starts_with($successUrl, 'https://')) {
                $payload['success_return_url'] = $successUrl;
                $payload['cancel_return_url'] = $successUrl;
            }

            if ($group !== null && $lockedPayment->xendit_split_rule_id === null) {
                try {
                    $splitResponse = Http::withBasicAuth($secretKey, '')
                        ->acceptJson()
                        ->timeout((int) config('services.xendit.timeout', 10))
                        ->withHeader('Idempotency-Key', 'marketplace-split-'.$lockedPayment->getKey())
                        ->post(rtrim((string) config('services.xendit.base_url'), '/').'/split_rules', [
                            'name' => 'Marketplace '.$group->checkout_number,
                            'description' => 'Alokasi dana checkout marketplace ke sub-account penjual.',
                            'routes' => $lockedPayment->allocations->map(fn (PaymentAllocation $allocation): array => [
                                'flat_amount' => (int) explode('.', (string) $allocation->amount, 2)[0],
                                'currency' => 'IDR',
                                'destination_account_id' => $allocation->destination_account_id,
                                'reference_id' => $allocation->route_reference,
                            ])->all(),
                        ]);
                } catch (ConnectionException) {
                    throw ValidationException::withMessages(['payment' => 'Xendit tidak dapat dihubungi saat menyiapkan pembagian dana. Coba kembali; pesanan belum diteruskan ke penjual.']);
                }

                if (! $splitResponse->successful() || ! is_string($splitResponse->json('id'))) {
                    $errorCode = $splitResponse->json('error_code');
                    Log::warning('Xendit rejected marketplace split rule creation.', [
                        'http_status' => $splitResponse->status(),
                        'error_code' => is_string($errorCode) ? $errorCode : 'UNKNOWN',
                        'request_id' => $splitResponse->header('x-request-id') ?? $splitResponse->header('request-id'),
                    ]);
                    $message = match ($errorCode) {
                        'REQUEST_FORBIDDEN_ERROR' => 'Akun atau kunci API belum diizinkan membuat split XenPlatform. Minta Xendit mengaktifkan fitur XenPlatform untuk akun Anda.',
                        'DESTINATION_ACCOUNT_NOT_FOUND', 'INVALID_SOURCE_OR_DESTINATION_ERROR' => 'Business ID salah satu sub-account penjual tidak ditemukan atau tidak terhubung ke akun XenPlatform ini.',
                        'INVALID_FEE_AMOUNT', 'INSUFFICIENT_BALANCE' => 'Aturan split ditolak karena saldo cadangan mungkin tidak cukup untuk biaya Xendit. Sesuaikan persentase payout dengan biaya provider.',
                        default => 'Xendit belum dapat membuat pembagian dana. Periksa aktivasi XenPlatform, izin API, dan Business ID sub-account penjual.',
                    };
                    throw ValidationException::withMessages(['payment' => $message.' Pesanan belum diteruskan ke penjual.']);
                }

                $lockedPayment->forceFill(['xendit_split_rule_id' => $splitResponse->json('id')])->save();
            }

            try {
                $request = Http::withBasicAuth($secretKey, '')
                    ->acceptJson()
                    ->timeout((int) config('services.xendit.timeout', 10))
                    ->withHeader('Idempotency-Key', 'marketplace-payment-'.$lockedPayment->getKey());
                if ($group !== null) {
                    $request = $request->withHeader('with-split-rule', (string) $lockedPayment->xendit_split_rule_id);
                }
                $response = $request->post(rtrim((string) config('services.xendit.base_url'), '/').'/sessions', $payload);
            } catch (ConnectionException) {
                throw ValidationException::withMessages(['payment' => 'Xendit tidak dapat dihubungi saat ini. Coba kembali beberapa saat lagi.']);
            }

            if (! $response->successful()) {
                $errorCode = $response->json('error_code');
                $requestId = $response->header('x-request-id') ?? $response->header('request-id');
                Log::warning('Xendit rejected marketplace payment session creation.', [
                    'http_status' => $response->status(),
                    'error_code' => is_string($errorCode) ? $errorCode : 'UNKNOWN',
                    'request_id' => is_string($requestId) ? $requestId : null,
                ]);

                $message = match ($errorCode) {
                    'INVALID_API_KEY' => 'Kunci API Xendit tidak valid atau tidak memiliki izin untuk membuat sesi pembayaran.',
                    'INVALID_COUNTRY', 'INVALID_CURRENCY', 'INVALID_AMOUNT', 'API_VALIDATION_ERROR' => 'Data pembayaran ditolak oleh validasi Xendit. Periksa konfigurasi produk dan pesanan, lalu coba kembali.',
                    'REQUEST_FORBIDDEN_ERROR' => 'Akun atau kunci API Xendit belum diizinkan untuk Payment Session atau XenPlatform split. Minta Xendit mengaktifkan fitur tersebut untuk akun Anda.',
                    default => 'Xendit menolak pembuatan sesi pembayaran. Periksa mode dan izin kunci API; administrator dapat memeriksa kode error pada log.',
                };

                throw ValidationException::withMessages(['payment' => $message]);
            }

            $sessionId = $response->json('payment_session_id');
            $checkoutUrl = $response->json('payment_link_url');
            $host = is_string($checkoutUrl) ? parse_url($checkoutUrl, PHP_URL_HOST) : null;
            $allowedHosts = ['checkout.xendit.co', 'checkout-staging.xendit.co', 'xen.to', 'dev.xen.to'];
            abort_unless(is_string($sessionId) && $sessionId !== '' && is_string($checkoutUrl)
                && parse_url($checkoutUrl, PHP_URL_SCHEME) === 'https'
                && is_string($host) && in_array(strtolower($host), $allowedHosts, true), 502, 'Xendit mengembalikan tautan checkout yang tidak valid.');

            $lockedPayment->forceFill([
                'provider_reference' => $sessionId,
                'checkout_url' => $checkoutUrl,
                'expires_at' => $response->json('expires_at'),
            ])->save();
            PaymentEvent::query()->create([
                'payment_id' => $lockedPayment->getKey(),
                'event_type' => 'xendit.session_created',
                'event_reference' => 'xendit-session-created-'.$sessionId,
                'payload' => ['mode' => config('services.xendit.mode'), 'currency' => 'IDR', 'checkout_group_id' => $group?->getKey()],
                'signature_verified' => false,
                'received_at' => now(),
            ]);

            return $lockedPayment->refresh();
        }, 3);
    }

    public function verifyWebhook(array $payload, array $headers): ?array
    {
        $expectedToken = (string) config('services.xendit.webhook_token');
        $receivedToken = $headers['x-callback-token'] ?? '';
        if ($expectedToken === '' || ! is_string($receivedToken) || ! hash_equals($expectedToken, $receivedToken)) {
            return null;
        }

        $event = $payload['event'] ?? null;
        $data = $payload['data'] ?? null;
        if ($event === 'split.payment' && is_array($data)) {
            $status = $data['status'] ?? null;
            if (! is_string($data['id'] ?? null) || ! is_string($data['split_rule_id'] ?? null)
                || ! is_string($data['reference_id'] ?? null) || ! is_string($data['payment_reference_id'] ?? null)
                || ! is_string($data['destination_account_id'] ?? null) || ! is_numeric($data['amount'] ?? null)
                || ! is_string($data['currency'] ?? null) || ! in_array($status, ['COMPLETED', 'FAILED'], true)) {
                return null;
            }

            return [
                'type' => 'split',
                'provider_reference' => $data['payment_reference_id'],
                'split_rule_id' => $data['split_rule_id'],
                'route_reference' => $data['reference_id'],
                'split_reference' => $data['id'],
                'destination_account_id' => $data['destination_account_id'],
                'amount' => number_format((float) $data['amount'], 2, '.', ''),
                'currency' => $data['currency'],
                'status' => $status === 'COMPLETED' ? 'completed' : 'failed',
                'failure_code' => is_string($data['failure_code'] ?? null) ? $data['failure_code'] : null,
                'event_reference' => 'xendit-split-'.hash('sha256', $data['id'].'|'.$status),
            ];
        }
        if (! is_string($event) || ! is_array($data) || ! in_array($event, ['payment_session.completed', 'payment_session.expired'], true)) {
            return null;
        }

        $sessionId = $data['payment_session_id'] ?? null;
        $orderReference = $data['reference_id'] ?? null;
        $amount = $data['amount'] ?? null;
        $currency = $data['currency'] ?? null;
        if (! is_string($sessionId) || ! is_string($orderReference) || ! is_numeric($amount) || ! is_string($currency)) {
            return null;
        }
        if (($data['status'] ?? null) !== ($event === 'payment_session.completed' ? 'COMPLETED' : 'EXPIRED')) {
            return null;
        }

        return [
            'type' => 'session',
            'provider_reference' => $sessionId,
            'order_reference' => $orderReference,
            'amount' => number_format((float) $amount, 2, '.', ''),
            'currency' => $currency,
            'status' => $event === 'payment_session.completed' ? 'paid' : 'failed',
            'event_reference' => 'xendit-session-'.hash('sha256', $event.'|'.$sessionId),
        ];
    }

    private function toCents(string $amount): int
    {
        if (! preg_match('/^\d{1,12}(?:\.\d{1,2})?$/', $amount)) {
            return -1;
        }
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }

    private function fromCents(int $amount): string
    {
        return intdiv($amount, 100).'.'.str_pad((string) ($amount % 100), 2, '0', STR_PAD_LEFT);
    }

    private function sellerPayoutBasisPoints(): int
    {
        $value = (string) config('services.xendit.marketplace_seller_payout_percent');
        if (! preg_match('/^(?:\d{1,2})(?:\.\d{1,2})?$/', $value) || (float) $value <= 0 || (float) $value >= 100) {
            throw ValidationException::withMessages(['payment_method' => 'Pembayaran Xendit multi-penjual belum dikonfigurasi. Administrator harus menetapkan persentase pencairan penjual di bawah 100% dan menyisakan saldo yang cukup untuk biaya transaksi.']);
        }
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }
}
