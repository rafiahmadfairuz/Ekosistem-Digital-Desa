<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\Seller;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Product $product): bool
    {
        return (in_array($product->status, ['active', 'out_of_stock'], true)
            && $product->seller?->verification_status === 'approved'
            && $product->category?->is_active)
            || $this->owns($user, $product)
            || $this->isModerator($user);
    }

    public function create(User $user): bool
    {
        $seller = $user->seller;

        return $user->account_status === 'active'
            && $user->hasRole('Seller')
            && $seller instanceof Seller
            && $seller->verification_status === 'approved';
    }

    public function update(User $user, Product $product): bool
    {
        return $this->owns($user, $product)
            && $this->create($user)
            && ! in_array($product->status, ['archived'], true);
    }

    public function moderate(User $user): bool
    {
        return $this->isModerator($user);
    }

    private function owns(User $user, Product $product): bool
    {
        return (int) $product->seller?->user_id === (int) $user->getKey();
    }

    private function isModerator(User $user): bool
    {
        return $user->account_status === 'active'
            && $user->hasRole('BUMDes Manager')
            && $user->bumdesStaff()->where('employment_status', 'active')->exists();
    }
}
