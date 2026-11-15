<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'description'])]
class Role extends Model
{
    /**
     * Return the Indonesian label for a stored role name.
     */
    public static function displayName(string $name): string
    {
        return match ($name) {
            'Super Admin' => 'Administrator Utama',
            'Village Admin' => 'Admin Desa',
            'BUMDes Manager' => 'Pengelola BUMDes',
            'BUMDes Operator' => 'Operator BUMDes',
            'Village Head' => 'Kepala Desa',
            'Resident' => 'Warga',
            'Seller' => 'Penjual',
            'RT' => 'Ketua RT',
            'RW' => 'Ketua RW',
            default => $name,
        };
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles')
            ->withPivot(['assigned_at', 'assigned_by']);
    }
}
