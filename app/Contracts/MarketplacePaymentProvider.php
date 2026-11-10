<?php

namespace App\Contracts;

use App\Models\Order;
use App\Models\Payment;
use App\Models\CheckoutGroup;

interface MarketplacePaymentProvider
{
    public function createCashOnDeliveryPayment(Order $order): Payment;

    public function createPendingGatewayPayment(Order $order): Payment;

    public function createPendingCheckoutGroupPayment(CheckoutGroup $group): Payment;

    public function createOnlinePaymentSession(Payment $payment): Payment;

    /** @param array<string, mixed> $payload
     * @param  array<string, string>  $headers
     * @return array{provider_reference: string, order_reference: string, amount: string, currency: string, status: string, event_reference: string}|null
     */
    public function verifyWebhook(array $payload, array $headers): ?array;
}
