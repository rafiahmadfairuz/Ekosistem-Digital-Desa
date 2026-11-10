<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['subscription_id', 'invoice_number', 'billing_period_start', 'billing_period_end', 'amount', 'due_date', 'paid_at', 'status'])]
class SubscriptionInvoice extends Model
{
    protected function casts(): array
    {
        return ['billing_period_start' => 'date', 'billing_period_end' => 'date', 'due_date' => 'date', 'amount' => 'decimal:2', 'paid_at' => 'immutable_datetime'];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(InternetSubscription::class, 'subscription_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }
}
