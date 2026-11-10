<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'session_reference', 'resident_id', 'operator_id', 'service_type', 'reference_type',
    'reference_id', 'status', 'notes', 'started_at', 'awaiting_confirmation_at',
    'identity_verified_at', 'resident_confirmed_at', 'completed_at', 'cancelled_at', 'cancellation_reason',
    'requested_at', 'accepted_at', 'rejected_at', 'rejection_reason',
])]
class AssistedServiceSession extends Model
{
    public const SERVICE_TYPES = [
        'correspondence', 'internet_subscription', 'internet_complaint', 'marketplace', 'general',
    ];

    protected function casts(): array
    {
        return [
            'notes' => 'encrypted',
            'started_at' => 'immutable_datetime',
            'identity_verified_at' => 'immutable_datetime',
            'requested_at' => 'immutable_datetime',
            'accepted_at' => 'immutable_datetime',
            'rejected_at' => 'immutable_datetime',
            'awaiting_confirmation_at' => 'immutable_datetime',
            'resident_confirmed_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
        ];
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(AssistedServiceLog::class, 'session_id')->orderBy('created_at')->orderBy('id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AssistedServiceMessage::class, 'session_id')->orderBy('created_at')->orderBy('id');
    }
}
