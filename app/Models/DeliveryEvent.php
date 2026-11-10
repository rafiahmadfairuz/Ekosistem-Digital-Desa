<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable(['delivery_id', 'actor_id', 'from_status', 'to_status', 'note', 'created_at'])]
class DeliveryEvent extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['note' => 'encrypted', 'created_at' => 'immutable_datetime'];
    }

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Delivery history is append-only.'));
        static::deleting(fn (): never => throw new LogicException('Delivery history is append-only.'));
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
