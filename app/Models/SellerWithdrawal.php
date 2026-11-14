<?php

namespace App\Models;

use Database\Factories\SellerWithdrawalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['seller_id', 'seller_payout_account_id', 'request_key', 'reference_id', 'xendit_payout_id', 'amount', 'currency', 'status', 'failure_code', 'account_snapshot', 'requested_at', 'completed_at'])]
#[Hidden(['account_snapshot'])]
class SellerWithdrawal extends Model
{
    /** @use HasFactory<SellerWithdrawalFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'account_snapshot' => 'encrypted:array',
            'requested_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function payoutAccount(): BelongsTo
    {
        return $this->belongsTo(SellerPayoutAccount::class, 'seller_payout_account_id');
    }

    public function balanceEntries(): HasMany
    {
        return $this->hasMany(SellerBalanceEntry::class);
    }
}
