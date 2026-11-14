<?php

namespace App\Models;

use Database\Factories\SellerPayoutAccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['seller_id', 'bank_name', 'bank_swift_code', 'account_holder_name', 'account_number', 'recipient_address', 'recipient_type', 'status', 'reviewed_by', 'reviewed_at', 'rejection_reason'])]
#[Hidden(['account_number', 'account_holder_name', 'recipient_address'])]
class SellerPayoutAccount extends Model
{
    /** @use HasFactory<SellerPayoutAccountFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'account_number' => 'encrypted',
            'account_holder_name' => 'encrypted',
            'recipient_address' => 'encrypted',
            'rejection_reason' => 'encrypted',
            'reviewed_at' => 'immutable_datetime',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function maskedAccountNumber(): string
    {
        return '•••• '.substr((string) $this->account_number, -4);
    }
}
