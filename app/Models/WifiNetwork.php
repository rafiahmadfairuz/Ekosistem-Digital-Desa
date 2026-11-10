<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'network_type', 'location', 'ssid', 'status', 'access_policy'])]
class WifiNetwork extends Model
{
    protected function casts(): array
    {
        return ['access_policy' => 'array'];
    }

    public function devices(): HasMany
    {
        return $this->hasMany(NetworkDevice::class);
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(WifiAccessLog::class);
    }
}
