<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'wifi_network_id', 'subscription_id', 'authentication_status', 'session_reference', 'client_identifier_hash', 'ip_address', 'connected_at', 'disconnected_at'])]
class WifiAccessLog extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['connected_at' => 'immutable_datetime', 'disconnected_at' => 'immutable_datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function network(): BelongsTo
    {
        return $this->belongsTo(WifiNetwork::class, 'wifi_network_id');
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(InternetSubscription::class);
    }
}
