<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['order_id', 'delivery_method', 'delivery_address', 'recipient_name', 'recipient_phone', 'delivery_reference', 'delivery_status', 'delivered_at', 'buyer_confirmed_at'])]
#[Hidden(['delivery_address', 'recipient_name', 'recipient_phone'])]
class Delivery extends Model
{
    protected function casts(): array
    {
        return [
            'delivery_address' => 'encrypted',
            'recipient_name' => 'encrypted',
            'recipient_phone' => 'encrypted',
            'delivered_at' => 'immutable_datetime',
            'buyer_confirmed_at' => 'immutable_datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(DeliveryEvent::class)->orderBy('created_at')->orderBy('id');
    }
}
