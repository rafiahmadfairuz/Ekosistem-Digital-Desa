<?php

namespace App\Services;

use App\Contracts\WifiGateway;
use App\Models\User;
use App\Models\WifiNetwork;
use LogicException;

class MikrotikWifiGateway implements WifiGateway
{
    public function openSession(User $user, WifiNetwork $network, int $minutes): bool
    {
        throw new LogicException('MikroTik integration is not configured. No credentials or RouterOS client are available in this prototype.');
    }
}
