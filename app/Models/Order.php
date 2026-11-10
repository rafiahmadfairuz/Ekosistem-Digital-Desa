<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['checkout_group_id', 'buyer_id', 'seller_id', 'order_number', 'total_amount', 'payment_method', 'status', 'rejection_reason', 'cancellation_reason', 'accepted_at', 'completed_at'])]
class Order extends Model
{
    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'rejection_reason' => 'encrypted',
            'cancellation_reason' => 'encrypted',
            'accepted_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
        ];
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(CheckoutGroup::class, 'checkout_group_id');
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'order_id')->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'order_id')->orderByDesc('created_at')->orderByDesc('id');
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    public function statusEvents(): HasMany
    {
        return $this->hasMany(OrderStatusEvent::class)->orderBy('created_at')->orderBy('id');
    }
}
