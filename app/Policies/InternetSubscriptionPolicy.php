<?php

namespace App\Policies;

use App\Models\InternetSubscription;
use App\Models\User;

class InternetSubscriptionPolicy
{
    public function manageStatus(User $user, InternetSubscription $subscription): bool
    {
        return $user->hasRole('BUMDes Manager')
            && $user->bumdesStaff()->where('employment_status', 'active')->exists();
    }
}
