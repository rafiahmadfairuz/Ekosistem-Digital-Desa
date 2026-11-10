<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['resident_id', 'package_id', 'installation_address', 'contact_phone', 'requested_at', 'scheduled_at', 'completed_at', 'status', 'reviewed_by', 'review_note'])]
class InstallationRequest extends Model
{
    protected function casts(): array
    {
        return ['installation_address' => 'encrypted', 'contact_phone' => 'encrypted', 'review_note' => 'encrypted', 'requested_at' => 'immutable_datetime', 'scheduled_at' => 'immutable_datetime', 'completed_at' => 'immutable_datetime'];
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(InternetPackage::class, 'package_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(InstallationRequestEvent::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(InternetSubscription::class);
    }
}
