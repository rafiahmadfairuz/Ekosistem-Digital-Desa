<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable(['letter_request_id', 'document_number', 'private_file_path', 'document_hash', 'version', 'signed_by', 'signed_at', 'signature_type', 'issued_at', 'created_at'])]
#[Hidden(['private_file_path'])]
class LetterDocument extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'signed_at' => 'immutable_datetime',
            'issued_at' => 'immutable_datetime',
            'created_at' => 'immutable_datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Issued correspondence documents are immutable.'));
        static::deleting(fn (): never => throw new LogicException('Issued correspondence documents are immutable.'));
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(LetterRequest::class, 'letter_request_id');
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signed_by');
    }
}
