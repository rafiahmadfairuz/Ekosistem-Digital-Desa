<?php

namespace App\Services;

use App\Models\User;

class MarketplaceNotifier
{
    public function __construct(private readonly InAppNotificationService $notifications) {}

    public function toUser(User $user, string $title, string $message, string $referenceType, int $referenceId): void
    {
        $this->notifications->toUser($user, $title, $message, $referenceType, $referenceId);
    }

    public function toActiveMarketplaceReviewers(string $title, string $message, string $referenceType, int $referenceId): void
    {
        $this->notifications->toActiveRoles(['BUMDes Manager'], $title, $message, $referenceType, $referenceId);
    }
}
