<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['payment_id', 'order_id', 'seller_id', 'destination_account_id', 'route_reference', 'amount', 'status', 'provider_split_reference', 'failure_code', 'completed_at'])]
class PaymentAllocation extends Model
{
    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'completed_at' => 'immutable_datetime'];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }
}
