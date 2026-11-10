<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable(['letter_request_id', 'uploaded_by', 'request_version', 'attachment_key', 'private_file_path', 'original_filename', 'mime_type', 'file_size', 'file_hash', 'uploaded_at'])]
#[Hidden(['private_file_path', 'file_hash'])]
class LetterAttachment extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['uploaded_at' => 'immutable_datetime'];
    }

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Correspondence attachments are immutable.'));
        static::deleting(fn (): never => throw new LogicException('Correspondence attachments are immutable.'));
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(LetterRequest::class, 'letter_request_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
