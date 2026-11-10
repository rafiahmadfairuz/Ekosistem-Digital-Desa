<?php

namespace App\Policies;

use App\Models\Seller;
use App\Models\User;

class SellerPolicy
{
    public function apply(User $user): bool
    {
        return $user->account_status === 'active'
            && $user->hasRole('Resident')
            && $user->resident()->exists()
            && ! $user->seller()->exists();
    }

    public function viewAny(User $user): bool
    {
        return $this->isMarketplaceManager($user);
    }

    public function view(User $user, Seller $seller): bool
    {
        return (int) $seller->user_id === (int) $user->getKey() || $this->isMarketplaceManager($user);
    }

    public function update(User $user, Seller $seller): bool
    {
        return $user->account_status === 'active'
            && $user->hasRole('Resident')
            && $user->resident()->exists()
            && (int) $seller->user_id === (int) $user->getKey()
            && in_array($seller->verification_status, ['rejected', 'inactive'], true);
    }

    public function review(User $user): bool
    {
        return $this->isMarketplaceManager($user);
    }

    private function isMarketplaceManager(User $user): bool
    {
        return $user->account_status === 'active'
            && $user->hasRole('BUMDes Manager')
            && $user->bumdesStaff()->where('employment_status', 'active')->exists();
    }
}
