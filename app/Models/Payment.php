<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['order_id', 'invoice_id', 'checkout_group_id', 'payment_method', 'payment_provider', 'provider_reference', 'checkout_url', 'xendit_split_rule_id', 'amount', 'platform_retained_amount', 'status', 'submitted_by', 'paid_at', 'expires_at'])]
class Payment extends Model
{
    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'platform_retained_amount' => 'decimal:2', 'paid_at' => 'immutable_datetime', 'expires_at' => 'immutable_datetime'];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(SubscriptionInvoice::class, 'invoice_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function checkoutGroup(): BelongsTo
    {
        return $this->belongsTo(CheckoutGroup::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(PaymentEvent::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class);
    }
}
