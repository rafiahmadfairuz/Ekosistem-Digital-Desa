<?php

namespace App\Policies;

use App\Models\AssistedServiceSession;
use App\Models\User;

class AssistedServiceSessionPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isActiveBumdesStaff($user) || $user->hasRole('Resident');
    }

    public function view(User $user, AssistedServiceSession $session): bool
    {
        if ($user->hasRole('Resident') && (int) $session->resident?->user_id === (int) $user->getKey()) {
            return true;
        }

        return $this->isActiveBumdesStaff($user)
            && ($user->hasRole('BUMDes Manager') || $session->status === 'pending' || (int) $session->operator_id === (int) $user->getKey());
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Resident')
            && $user->account_status === 'active'
            && $user->resident?->region_verified_at !== null;
    }

    public function accept(User $user, AssistedServiceSession $session): bool
    {
        return $this->isActiveBumdesStaff($user) && $session->status === 'pending';
    }

    public function reject(User $user, AssistedServiceSession $session): bool
    {
        return $this->accept($user, $session);
    }

    public function update(User $user, AssistedServiceSession $session): bool
    {
        return $this->isActiveBumdesStaff($user)
            && ($user->hasRole('BUMDes Manager') || (int) $session->operator_id === (int) $user->getKey())
            && $session->status === 'in_progress';
    }

    public function requestConfirmation(User $user, AssistedServiceSession $session): bool
    {
        return $this->isActiveBumdesStaff($user)
            && ($user->hasRole('BUMDes Manager') || (int) $session->operator_id === (int) $user->getKey())
            && $session->status === 'in_progress';
    }

    public function attachReference(User $user, AssistedServiceSession $session): bool
    {
        return $this->update($user, $session) && $session->reference_type === null;
    }

    public function viewConversation(User $user, AssistedServiceSession $session): bool
    {
        if ($session->status === 'pending' || $session->status === 'rejected') {
            return false;
        }

        return ($user->hasRole('Resident') && (int) $session->resident?->user_id === (int) $user->getKey())
            || ($this->isActiveBumdesStaff($user) && (int) $session->operator_id === (int) $user->getKey());
    }

    public function sendMessage(User $user, AssistedServiceSession $session): bool
    {
        return $this->viewConversation($user, $session)
            && in_array($session->status, ['in_progress', 'awaiting_resident_confirmation'], true);
    }

    public function confirm(User $user, AssistedServiceSession $session): bool
    {
        return $user->hasRole('Resident')
            && (int) $session->resident?->user_id === (int) $user->getKey()
            && $session->status === 'awaiting_resident_confirmation';
    }

    public function cancel(User $user, AssistedServiceSession $session): bool
    {
        if ($user->hasRole('Resident') && (int) $session->resident?->user_id === (int) $user->getKey()) {
            return $session->status === 'pending';
        }

        return $session->reference_type !== 'letter_request'
            && $this->isActiveBumdesStaff($user)
            && ($user->hasRole('BUMDes Manager') || (int) $session->operator_id === (int) $user->getKey())
            && in_array($session->status, ['in_progress', 'awaiting_resident_confirmation'], true);
    }

    public function viewReports(User $user): bool
    {
        return $user->hasRole('BUMDes Manager') && $user->bumdesStaff()->where('employment_status', 'active')->exists();
    }

    private function isActiveBumdesStaff(User $user): bool
    {
        return ($user->hasRole('BUMDes Manager') || $user->hasRole('BUMDes Operator'))
            && $user->bumdesStaff()->where('employment_status', 'active')->exists();
    }
}
