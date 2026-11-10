<?php

namespace App\Policies;

use App\Models\Cart;
use App\Models\User;

class CartPolicy
{
    public function manage(User $user, Cart $cart): bool
    {
        return $user->account_status === 'active'
            && $user->hasRole('Resident')
            && (int) $cart->user_id === (int) $user->getKey();
    }

    public function checkout(User $user): bool
    {
        return $user->account_status === 'active' && $user->hasRole('Resident');
    }
}
