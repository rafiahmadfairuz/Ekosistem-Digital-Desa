<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable(['order_id', 'actor_id', 'from_status', 'to_status', 'reason', 'created_at'])]
class OrderStatusEvent extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['reason' => 'encrypted', 'created_at' => 'immutable_datetime'];
    }

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Order status history is append-only.'));
        static::deleting(fn (): never => throw new LogicException('Order status history is append-only.'));
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
