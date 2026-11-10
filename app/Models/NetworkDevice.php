<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['wifi_network_id', 'device_name', 'integration_type', 'device_identifier', 'device_status', 'configuration', 'last_seen_at'])]
class NetworkDevice extends Model
{
    protected function casts(): array
    {
        return ['configuration' => 'array', 'last_seen_at' => 'immutable_datetime'];
    }

    public function network(): BelongsTo
    {
        return $this->belongsTo(WifiNetwork::class, 'wifi_network_id');
    }
}
