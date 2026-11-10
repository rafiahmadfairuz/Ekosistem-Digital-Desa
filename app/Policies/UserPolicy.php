<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }

    public function manageAccess(User $user, User $target): bool
    {
        return $user->hasRole('Super Admin') && $user->getKey() !== $target->getKey();
    }
}
