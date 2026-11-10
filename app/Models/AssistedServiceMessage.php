<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable(['session_id', 'sender_id', 'body'])]
class AssistedServiceMessage extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['body' => 'encrypted', 'created_at' => 'immutable_datetime'];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Assistance messages are append-only.'));
        static::deleting(fn () => throw new LogicException('Assistance messages are append-only.'));
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AssistedServiceSession::class, 'session_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
