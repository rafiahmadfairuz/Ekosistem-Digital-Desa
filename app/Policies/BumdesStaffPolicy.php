<?php

namespace App\Policies;

use App\Models\BumdesStaff;
use App\Models\User;

class BumdesStaffPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('BUMDes Manager');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('BUMDes Manager');
    }

    public function update(User $user, BumdesStaff $staff): bool
    {
        return $user->hasRole('BUMDes Manager');
    }

    public function delete(User $user, BumdesStaff $staff): bool
    {
        return $user->hasRole('BUMDes Manager');
    }
}
