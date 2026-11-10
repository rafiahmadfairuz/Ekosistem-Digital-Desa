<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable(['seller_id', 'uploaded_by', 'document_type', 'private_file_path', 'original_filename', 'mime_type', 'file_size', 'file_hash', 'uploaded_at'])]
#[Hidden(['private_file_path', 'file_hash'])]
class SellerDocument extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['uploaded_at' => 'immutable_datetime'];
    }

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Seller documents are immutable.'));
        static::deleting(fn (): never => throw new LogicException('Seller documents are retained.'));
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
