<?php

namespace App\Services;

use App\Models\CheckoutGroup;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutHistoryService
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function archiveForBuyer(User $buyer, CheckoutGroup $group): void
    {
        DB::transaction(function () use ($buyer, $group): void {
            $locked = CheckoutGroup::query()->whereKey($group->getKey())->lockForUpdate()->firstOrFail();
            abort_unless((int) $locked->buyer_id === (int) $buyer->getKey(), 404);
            abort_if($locked->buyer_archived_at !== null, 409, 'Checkout ini sudah disembunyikan dari riwayat.');

            $orders = $locked->orders()->with('payment')->lockForUpdate()->get();
            $groupPayment = Payment::query()->where('checkout_group_id', $locked->getKey())->lockForUpdate()->first();
            if ($orders->isEmpty() || $orders->contains(fn ($order): bool => ! in_array($order->status, ['completed', 'cancelled', 'rejected'], true))) {
                throw ValidationException::withMessages(['history' => 'Riwayat hanya dapat disembunyikan setelah seluruh pesanan selesai, dibatalkan, atau ditolak.']);
            }

            $completedOrdersHaveConfirmedPayment = $groupPayment !== null
                ? $groupPayment->status === 'paid'
                : ! $orders->contains(fn ($order): bool => $order->status === 'completed' && $order->payment?->status !== 'paid');
            if (! $completedOrdersHaveConfirmedPayment) {
                throw ValidationException::withMessages(['history' => 'Pesanan selesai harus memiliki pembayaran yang sudah dikonfirmasi sebelum riwayat disembunyikan.']);
            }

            $locked->forceFill(['buyer_archived_at' => now()])->save();
            $this->audit->record('marketplace.checkout_history_archived', 'checkout_group', (int) $locked->getKey(), [
                'order_count' => $orders->count(),
            ]);
        });
    }
}
