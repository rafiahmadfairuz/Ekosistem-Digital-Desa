<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'business_name', 'business_description', 'business_address', 'contact_phone', 'verification_status', 'xendit_account_id', 'reviewed_by', 'reviewed_at', 'rejection_reason'])]
#[Hidden(['business_address', 'contact_phone', 'rejection_reason'])]
class Seller extends Model
{
    protected function casts(): array
    {
        return [
            'business_address' => 'encrypted',
            'contact_phone' => 'encrypted',
            'rejection_reason' => 'encrypted',
            'reviewed_at' => 'immutable_datetime',
        ];
    }

    public function isApproved(): bool
    {
        return $this->verification_status === 'approved';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function statusEvents(): HasMany
    {
        return $this->hasMany(SellerStatusEvent::class)->orderBy('created_at')->orderBy('id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SellerDocument::class);
    }
}
