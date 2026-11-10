<?php

namespace App\Policies;

use App\Models\InstallationRequest;
use App\Models\User;

class InstallationRequestPolicy
{
    public function view(User $user, InstallationRequest $request): bool
    {
        return ($user->hasRole('Resident') && (int) $request->resident?->user_id === (int) $user->id)
            || $this->staff($user);
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Resident') && $user->resident()->exists();
    }

    public function review(User $user): bool
    {
        return $this->staff($user);
    }

    private function staff(User $user): bool
    {
        return ($user->hasRole('BUMDes Operator') || $user->hasRole('BUMDes Manager')) && $user->bumdesStaff()->where('employment_status', 'active')->exists();
    }
}
