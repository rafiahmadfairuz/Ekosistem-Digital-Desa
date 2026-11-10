<?php

namespace App\Policies;

use App\Models\User;

class InternetPackagePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function manage(User $user): bool
    {
        return $user->hasRole('BUMDes Manager') && $user->bumdesStaff()->where('employment_status', 'active')->exists();
    }
}
