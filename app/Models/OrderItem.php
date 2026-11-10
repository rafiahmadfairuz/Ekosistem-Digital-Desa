<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable(['order_id', 'product_id', 'product_name_snapshot', 'unit_price', 'quantity', 'subtotal', 'attributes_snapshot'])]
#[Hidden(['attributes_snapshot'])]
class OrderItem extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'attributes_snapshot' => 'array',
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Order item snapshots are immutable.'));
        static::deleting(fn (): never => throw new LogicException('Order item snapshots are retained.'));
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
