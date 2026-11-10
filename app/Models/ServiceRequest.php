<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['resident_id', 'service_type', 'subject', 'description', 'status', 'assigned_to', 'resolution_note', 'resolved_at'])]
class ServiceRequest extends Model
{
    protected function casts(): array
    {
        return ['description' => 'encrypted', 'resolution_note' => 'encrypted', 'resolved_at' => 'immutable_datetime'];
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
