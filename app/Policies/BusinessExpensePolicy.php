<?php

namespace App\Policies;

use App\Models\BusinessExpense;
use App\Models\User;

class BusinessExpensePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isActiveManager($user);
    }

    public function create(User $user): bool
    {
        return $this->isActiveManager($user);
    }

    public function void(User $user, BusinessExpense $expense): bool
    {
        return $this->isActiveManager($user);
    }

    private function isActiveManager(User $user): bool
    {
        return $user->hasRole('BUMDes Manager')
            && $user->bumdesStaff()->where('employment_status', 'active')->exists();
    }
}
