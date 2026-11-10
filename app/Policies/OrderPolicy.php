<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return (int) $order->buyer_id === (int) $user->getKey()
            || ((int) $order->seller?->user_id === (int) $user->getKey() && $order->status !== 'awaiting_payment');
    }

    public function manage(User $user, Order $order): bool
    {
        return $user->account_status === 'active'
            && (int) $order->seller?->user_id === (int) $user->getKey()
            && $order->status !== 'awaiting_payment'
            && $user->hasRole('Seller');
    }

    public function cancel(User $user, Order $order): bool
    {
        return (int) $order->buyer_id === (int) $user->getKey()
            && in_array($order->status, ['pending_seller', 'awaiting_payment'], true)
            && $order->group?->payment?->status !== 'paid'
            && $order->payment?->status !== 'paid';
    }

    public function confirmReceipt(User $user, Order $order): bool
    {
        return (int) $order->buyer_id === (int) $user->getKey()
            && $order->status === 'delivered'
            && $order->delivery?->delivery_status === 'delivered'
            && $order->delivery?->buyer_confirmed_at === null;
    }
}
