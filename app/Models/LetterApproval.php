<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable(['letter_request_id', 'reviewer_id', 'actor_role', 'approval_level', 'decision', 'comments', 'resident_visible', 'reviewed_at'])]
#[Hidden(['comments'])]
class LetterApproval extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['resident_visible' => 'boolean', 'reviewed_at' => 'immutable_datetime'];
    }

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Correspondence approval history is append-only.'));
        static::deleting(fn (): never => throw new LogicException('Correspondence approval history is append-only.'));
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(LetterRequest::class, 'letter_request_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
