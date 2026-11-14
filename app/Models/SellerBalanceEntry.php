<?php

namespace App\Models;

use Database\Factories\SellerBalanceEntryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable(['seller_id', 'order_id', 'seller_withdrawal_id', 'entry_type', 'reference_id', 'amount', 'currency', 'description'])]
#[Hidden(['reference_id'])]
class SellerBalanceEntry extends Model
{
    /** @use HasFactory<SellerBalanceEntryFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    protected static function booted(): void
    {
        static::updating(fn (): never => throw new LogicException('Seller balance entries are immutable.'));
        static::deleting(fn (): never => throw new LogicException('Seller balance entries are retained.'));
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(SellerWithdrawal::class, 'seller_withdrawal_id');
    }
}
