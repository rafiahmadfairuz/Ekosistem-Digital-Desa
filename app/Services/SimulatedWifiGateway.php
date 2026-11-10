<?php

namespace App\Services;

use App\Contracts\WifiGateway;
use App\Models\User;
use App\Models\WifiNetwork;

class SimulatedWifiGateway implements WifiGateway
{
    public function openSession(User $user, WifiNetwork $network, int $minutes): bool
    {
        // Application prototype only. No device is contacted and no network access is enforced.
        return $minutes > 0 && $network->status === 'active';
    }
}
