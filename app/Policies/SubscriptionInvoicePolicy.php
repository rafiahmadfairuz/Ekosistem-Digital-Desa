<?php

namespace App\Policies;

use App\Models\SubscriptionInvoice;
use App\Models\User;

class SubscriptionInvoicePolicy
{
    public function view(User $user, SubscriptionInvoice $invoice): bool
    {
        return ($user->hasRole('Resident') && (int) $invoice->subscription?->resident?->user_id === (int) $user->id)
            || (($user->hasRole('BUMDes Manager') || $user->hasRole('BUMDes Operator')) && $user->bumdesStaff()->where('employment_status', 'active')->exists());
    }

    public function pay(User $user, SubscriptionInvoice $invoice): bool
    {
        return $user->hasRole('Resident')
            && (int) $invoice->subscription?->resident?->user_id === (int) $user->id
            && in_array($invoice->status, ['unpaid', 'overdue'], true)
            && (float) $invoice->amount > 0;
    }

    public function confirm(User $user): bool
    {
        return ($user->hasRole('BUMDes Manager') || $user->hasRole('BUMDes Operator'))
            && $user->bumdesStaff()->where('employment_status', 'active')->exists();
    }
}
