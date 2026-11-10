<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'description', 'monthly_price', 'bandwidth_download_kbps', 'bandwidth_upload_kbps', 'validity_days', 'is_active'])]
class InternetPackage extends Model
{
    protected function casts(): array
    {
        return ['monthly_price' => 'decimal:2', 'is_active' => 'boolean'];
    }

    public function installationRequests(): HasMany
    {
        return $this->hasMany(InstallationRequest::class, 'package_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(InternetSubscription::class, 'package_id');
    }
}
