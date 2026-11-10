<?php

namespace App\Policies;

use App\Models\ServiceRequest;
use App\Models\User;

class ServiceRequestPolicy
{
    public function view(User $user, ServiceRequest $serviceRequest): bool
    {
        return ($user->hasRole('Resident') && (int) $serviceRequest->resident?->user_id === (int) $user->id)
            || $this->staff($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Resident') && $user->resident()->exists();
    }

    public function manage(User $user): bool
    {
        return $this->staff($user);
    }

    private function staff(User $user): bool
    {
        return ($user->hasRole('BUMDes Operator') || $user->hasRole('BUMDes Manager')) && $user->bumdesStaff()->where('employment_status', 'active')->exists();
    }
}
