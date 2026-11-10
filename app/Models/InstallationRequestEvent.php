<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['installation_request_id', 'actor_id', 'from_status', 'to_status', 'note'])]
class InstallationRequestEvent extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['note' => 'encrypted', 'created_at' => 'immutable_datetime'];
    }

    public function installationRequest(): BelongsTo
    {
        return $this->belongsTo(InstallationRequest::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
