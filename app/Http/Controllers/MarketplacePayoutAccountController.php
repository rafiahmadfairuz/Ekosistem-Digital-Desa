<?php

namespace App\Http\Controllers;

use App\Jobs\SendSellerWithdrawal;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Seller;
use App\Models\SellerBalanceEntry;
use App\Models\SellerPayoutAccount;
use App\Models\SellerWithdrawal;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MarketplacePayoutAccountController extends Controller
{
    public function edit(Request $request): View
    {
        $this->authorize('create', Product::class);
        $seller = $request->user()->seller()->with('payoutAccount')->firstOrFail();
        $balance = $seller->balanceEntries()->sum('amount');
        $earned = $seller->balanceEntries()->where('entry_type', 'sale_earning')->sum('amount');
        $pendingWithdrawals = $seller->withdrawals()->whereIn('status', ['queued', 'processing', 'accepted', 'pending_compliance', 'needs_reconciliation'])->sum('amount');
        $soldItems = OrderItem::query()->whereHas('order', function ($query) use ($seller): void {
            $query->where('seller_id', $seller->getKey())->where('status', 'completed')
                ->where(function ($paid): void {
                    $paid->whereHas('payments', fn ($payment) => $payment->where('status', 'paid'))
                        ->orWhereHas('group.payment', fn ($payment) => $payment->where('status', 'paid'));
                });
        })->sum('quantity');
        $withdrawals = $seller->withdrawals()->latest()->paginate(15);
        $entries = $seller->balanceEntries()->with(['order', 'withdrawal'])->latest()->paginate(15, ['*'], 'entry_page');
        $requestKey = (string) Str::uuid();
        $withdrawalsEnabled = (bool) config('services.xendit.payouts_enabled')
            && (bool) config('services.xendit.marketplace_enabled')
            && in_array(config('services.xendit.mode'), ['test', 'live'], true)
            && filled(config('services.xendit.secret_key'))
            && filled(config('services.xendit.payout_webhook_token'));

        return view('marketplace.seller.payout-account', compact('seller', 'balance', 'earned', 'pendingWithdrawals', 'soldItems', 'withdrawals', 'entries', 'requestKey', 'withdrawalsEnabled'));
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('create', Product::class);
        $seller = $request->user()->seller()->firstOrFail();
        $validated = $request->validate([
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_swift_code' => ['required', 'string', 'regex:/^[A-Za-z0-9]{8}([A-Za-z0-9]{3})?$/'],
            'account_holder_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'regex:/^[0-9]{5,34}$/'],
            'recipient_address' => ['required', 'string', 'max:500'],
            'recipient_type' => ['required', 'in:INDIVIDUAL,BUSINESS'],
        ]);
        DB::transaction(function () use ($seller, $validated, $audit): void {
            $lockedSeller = Seller::query()->whereKey($seller->getKey())->lockForUpdate()->firstOrFail();
            $existing = SellerPayoutAccount::query()->where('seller_id', $lockedSeller->getKey())->lockForUpdate()->first();
            $account = SellerPayoutAccount::query()->updateOrCreate(
                ['seller_id' => $lockedSeller->getKey()],
                [...$validated, 'bank_swift_code' => strtoupper($validated['bank_swift_code']), 'status' => 'pending', 'reviewed_by' => null, 'reviewed_at' => null, 'rejection_reason' => null]
            );
            $audit->record($existing === null ? 'marketplace.payout_account_submitted' : 'marketplace.payout_account_changed', 'seller_payout_account', (int) $account->getKey());
        }, 3);

        return to_route('marketplace.seller.payout-account.edit')->with('status', 'Rekening payout tersimpan dan menunggu verifikasi pengelola.');
    }

    public function requestWithdrawal(Request $request, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('create', Product::class);
        abort_unless((bool) config('services.xendit.payouts_enabled')
            && (bool) config('services.xendit.marketplace_enabled')
            && in_array(config('services.xendit.mode'), ['test', 'live'], true)
            && filled(config('services.xendit.secret_key'))
            && filled(config('services.xendit.payout_webhook_token')), 503, 'Pencairan Xendit belum tersedia.');
        $validated = $request->validate([
            'request_key' => ['required', 'uuid'],
            'amount' => ['required', 'integer', 'min:1', 'max:999999999999'],
        ]);
        $seller = $request->user()->seller()->firstOrFail();
        $withdrawal = DB::transaction(function () use ($seller, $validated, $audit): SellerWithdrawal {
            $lockedSeller = Seller::query()->whereKey($seller->getKey())->lockForUpdate()->firstOrFail();
            $existing = SellerWithdrawal::query()->where('request_key', $validated['request_key'])->first();
            if ($existing !== null) {
                abort_unless((int) $existing->seller_id === (int) $lockedSeller->getKey(), 409);

                return $existing;
            }
            $account = SellerPayoutAccount::query()->where('seller_id', $lockedSeller->getKey())->lockForUpdate()->first();
            abort_unless($account !== null && $account->status === 'verified', 422, 'Rekening pencairan harus diverifikasi pengelola terlebih dahulu.');
            $balanceCents = $this->toCents((string) $lockedSeller->balanceEntries()->sum('amount'));
            $amountCents = $this->toCents((string) $validated['amount']);
            abort_unless($amountCents <= $balanceCents, 422, 'Jumlah pencairan melebihi saldo tersedia.');

            $withdrawal = SellerWithdrawal::query()->create([
                'seller_id' => $lockedSeller->getKey(),
                'seller_payout_account_id' => $account->getKey(),
                'request_key' => $validated['request_key'],
                'reference_id' => 'withdrawal-'.Str::uuid(),
                'amount' => $this->fromCents($amountCents),
                'currency' => 'IDR',
                'status' => 'queued',
                'account_snapshot' => [
                    'bank_name' => $account->bank_name,
                    'bank_swift_code' => $account->bank_swift_code,
                    'account_holder_name' => $account->account_holder_name,
                    'account_number' => $account->account_number,
                    'recipient_address' => $account->recipient_address,
                    'recipient_type' => $account->recipient_type,
                    'business_name' => $lockedSeller->business_name,
                ],
                'requested_at' => now(),
            ]);
            SellerBalanceEntry::query()->create([
                'seller_id' => $lockedSeller->getKey(),
                'seller_withdrawal_id' => $withdrawal->getKey(),
                'entry_type' => 'withdrawal_reservation',
                'reference_id' => 'withdrawal-reserve-'.$withdrawal->getKey(),
                'amount' => '-'.$this->fromCents($amountCents),
                'currency' => 'IDR',
                'description' => 'Saldo ditahan untuk pencairan '.$withdrawal->reference_id,
            ]);
            SendSellerWithdrawal::dispatch((int) $withdrawal->getKey());
            $audit->record('marketplace.seller_withdrawal_requested', 'seller_withdrawal', (int) $withdrawal->getKey(), ['amount' => $withdrawal->amount]);

            return $withdrawal;
        }, 3);

        return to_route('marketplace.seller.payout-account.edit')->with('status', 'Permintaan pencairan tercatat. Saldo sudah ditahan dan status transfer akan diperbarui setelah konfirmasi Xendit.');
    }

    public function managementIndex(): View
    {
        $this->authorize('viewAny', Seller::class);
        $accounts = SellerPayoutAccount::query()->with(['seller.user', 'reviewer'])->latest()->paginate(25);
        $withdrawals = SellerWithdrawal::query()->with(['seller', 'payoutAccount'])
            ->whereIn('status', ['failed', 'rejected', 'reversed', 'needs_reconciliation', 'pending_compliance'])
            ->latest()->paginate(25, ['*'], 'withdrawal_page');

        return view('marketplace.management.payout-accounts', compact('accounts', 'withdrawals'));
    }

    public function review(Request $request, SellerPayoutAccount $account, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('viewAny', Seller::class);
        $validated = $request->validate([
            'status' => ['required', 'in:verified,rejected'],
            'reason' => ['required_if:status,rejected', 'nullable', 'string', 'max:500'],
        ]);
        DB::transaction(function () use ($account, $request, $validated, $audit): void {
            $locked = SellerPayoutAccount::query()->whereKey($account->getKey())->lockForUpdate()->firstOrFail();
            $locked->forceFill([
                'status' => $validated['status'],
                'reviewed_by' => $request->user()->getKey(),
                'reviewed_at' => now(),
                'rejection_reason' => $validated['status'] === 'rejected' ? $validated['reason'] : null,
            ])->save();
            $audit->record('marketplace.payout_account_reviewed', 'seller_payout_account', (int) $locked->getKey(), ['status' => $validated['status']]);
        }, 3);

        return back()->with('status', 'Status rekening payout diperbarui.');
    }

    private function toCents(string $amount): int
    {
        if (! preg_match('/^\d{1,12}(?:\.\d{1,2})?$/', $amount)) {
            abort(422, 'Format jumlah saldo tidak valid.');
        }
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }

    private function fromCents(int $amount): string
    {
        return intdiv($amount, 100).'.'.str_pad((string) ($amount % 100), 2, '0', STR_PAD_LEFT);
    }
}
