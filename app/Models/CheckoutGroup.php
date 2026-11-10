<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['buyer_id', 'checkout_number', 'request_key', 'grand_total', 'status', 'buyer_archived_at'])]
class CheckoutGroup extends Model
{
    protected function casts(): array
    {
        return ['grand_total' => 'decimal:2', 'buyer_archived_at' => 'immutable_datetime'];
    }

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderByDesc('created_at')->orderByDesc('id');
    }

    public function payment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Payment::class, 'checkout_group_id')->latestOfMany();
    }
}
