<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable(['letter_request_id', 'changed_by', 'version', 'encrypted_data', 'submitted_at'])]
#[Hidden(['encrypted_data'])]
class LetterRequestVersion extends Model
{
    protected function casts(): array
    {
        return ['submitted_at' => 'immutable_datetime', 'encrypted_data' => 'array'];
    }

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Correspondence request versions are immutable.'));
        static::deleting(fn (): never => throw new LogicException('Correspondence request versions are immutable.'));
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(LetterRequest::class, 'letter_request_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
