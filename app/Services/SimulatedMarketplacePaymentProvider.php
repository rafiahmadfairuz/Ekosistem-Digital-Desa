<?php

namespace App\Services;

use App\Contracts\MarketplacePaymentProvider;
use App\Models\Order;
use App\Models\Payment;
use App\Models\CheckoutGroup;
use App\Models\PaymentEvent;
use Illuminate\Validation\ValidationException;

class SimulatedMarketplacePaymentProvider implements MarketplacePaymentProvider
{
    public function createCashOnDeliveryPayment(Order $order): Payment
    {
        $payment = Payment::query()->create([
            'order_id' => $order->getKey(),
            'invoice_id' => null,
            'payment_method' => 'cash',
            'payment_provider' => 'cod',
            'amount' => $order->total_amount,
            'status' => 'pending',
        ]);
        PaymentEvent::query()->create([
            'payment_id' => $payment->getKey(),
            'event_type' => 'cod.payment_pending',
            'signature_verified' => false,
            'received_at' => now(),
        ]);

        return $payment;
    }

    public function createPendingGatewayPayment(Order $order): Payment
    {
        throw ValidationException::withMessages(['payment_method' => 'Pembayaran online belum dikonfigurasi. Pilih COD atau minta administrator mengatur Xendit.']);
    }

    public function createPendingCheckoutGroupPayment(CheckoutGroup $group): Payment
    {
        throw ValidationException::withMessages(['payment_method' => 'Pembayaran multi-penjual online belum dikonfigurasi. Pilih COD atau minta administrator mengaktifkan Xendit XenPlatform.']);
    }

    public function createOnlinePaymentSession(Payment $payment): Payment
    {
        throw ValidationException::withMessages(['payment' => 'Pembayaran online belum dikonfigurasi.']);
    }

    public function verifyWebhook(array $payload, array $headers): ?array
    {
        return null;
    }
}
