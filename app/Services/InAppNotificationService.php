<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class InAppNotificationService
{
    public function toUser(User $user, string $title, string $message, ?string $referenceType = null, ?int $referenceId = null): void
    {
        DB::table('notifications')->insert([
            'user_id' => $user->getKey(),
            'title' => mb_substr($title, 0, 180),
            'message' => mb_substr($message, 0, 2000),
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'created_at' => now(),
        ]);
    }

    /** @param array<int, string> $roleNames */
    public function toActiveRoles(array $roleNames, string $title, string $message, ?string $referenceType = null, ?int $referenceId = null): void
    {
        $users = User::query()
            ->where('account_status', 'active')
            ->whereHas('roles', fn ($query) => $query->whereIn('name', $roleNames))
            ->when(collect($roleNames)->contains(fn (string $role): bool => str_starts_with($role, 'BUMDes')),
                fn ($query) => $query->whereHas('bumdesStaff', fn ($staff) => $staff->where('employment_status', 'active')))
            ->get();

        foreach ($users as $user) {
            $this->toUser($user, $title, $message, $referenceType, $referenceId);
        }
    }
}
