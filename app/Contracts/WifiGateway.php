<?php

namespace App\Contracts;

use App\Models\User;
use App\Models\WifiNetwork;

interface WifiGateway
{
    /** Returns true only when a simulated/application-side session was opened. */
    public function openSession(User $user, WifiNetwork $network, int $minutes): bool;
}
