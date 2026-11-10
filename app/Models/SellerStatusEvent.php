<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable(['seller_id', 'actor_id', 'from_status', 'to_status', 'reason', 'created_at'])]
class SellerStatusEvent extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['reason' => 'encrypted', 'created_at' => 'immutable_datetime'];
    }

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Seller status history is append-only.'));
        static::deleting(fn (): never => throw new LogicException('Seller status history is append-only.'));
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
