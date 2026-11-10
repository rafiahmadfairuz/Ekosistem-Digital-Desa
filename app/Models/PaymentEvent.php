<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['payment_id', 'event_type', 'event_reference', 'payload', 'signature_verified', 'received_at'])]
class PaymentEvent extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['payload' => 'array', 'signature_verified' => 'boolean', 'received_at' => 'immutable_datetime'];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
