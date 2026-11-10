<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['resident_id', 'created_by', 'letter_type_id', 'request_number', 'submission_channel', 'encrypted_data', 'letter_type_snapshot', 'status', 'resident_confirmed_at', 'submitted_at', 'completed_at', 'rejection_reason', 'revision_number'])]
#[Hidden(['encrypted_data', 'letter_type_snapshot'])]
class LetterRequest extends Model
{
    protected function casts(): array
    {
        return [
            'resident_confirmed_at' => 'immutable_datetime',
            'submitted_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
            'encrypted_data' => 'array',
            'letter_type_snapshot' => 'array',
        ];
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function letterType(): BelongsTo
    {
        return $this->belongsTo(LetterType::class);
    }

    public function effectiveLetterType(): LetterType
    {
        $letterType = $this->letterType;
        $snapshot = $this->letter_type_snapshot;
        if (! is_array($snapshot)) {
            return $letterType;
        }

        $letterType->setAttribute('code', $snapshot['code']);
        $letterType->setAttribute('name', $snapshot['name']);
        $letterType->setAttribute('required_fields', $snapshot['required_fields'] ?? []);
        $letterType->setAttribute('required_attachments', $snapshot['required_attachments'] ?? []);
        $letterType->setAttribute('template_path', $snapshot['template_path'] ?? null);

        return $letterType;
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(LetterApproval::class)->orderBy('reviewed_at')->orderBy('id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(LetterRequestVersion::class)->orderBy('version');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(LetterAttachment::class)->orderBy('uploaded_at');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(LetterDocument::class)->orderBy('version');
    }
}
