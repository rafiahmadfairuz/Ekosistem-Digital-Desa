<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['resident_id', 'package_id', 'installation_request_id', 'start_date', 'end_date', 'status'])]
class InternetSubscription extends Model
{
    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date'];
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(InternetPackage::class, 'package_id');
    }

    public function installationRequest(): BelongsTo
    {
        return $this->belongsTo(InstallationRequest::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(SubscriptionInvoice::class, 'subscription_id');
    }

    public function wifiAccessLogs(): HasMany
    {
        return $this->hasMany(WifiAccessLog::class, 'subscription_id');
    }
}
