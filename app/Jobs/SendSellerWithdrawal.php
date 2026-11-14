<?php

namespace App\Jobs;

use App\Models\SellerBalanceEntry;
use App\Models\SellerWithdrawal;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class SendSellerWithdrawal implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [30, 120, 300];

    public function __construct(public int $sellerWithdrawalId) {}

    public function handle(): void
    {
        $withdrawal = DB::transaction(function (): ?SellerWithdrawal {
            $withdrawal = SellerWithdrawal::query()->whereKey($this->sellerWithdrawalId)->lockForUpdate()->firstOrFail();
            if (! in_array($withdrawal->status, ['queued', 'processing'], true)) {
                return null;
            }
            $withdrawal->forceFill(['status' => 'processing'])->save();

            return $withdrawal;
        }, 3);
        if ($withdrawal === null) {
            return;
        }
        if (! config('services.xendit.payouts_enabled') || ! config('services.xendit.marketplace_enabled')
            || ! in_array(config('services.xendit.mode'), ['test', 'live'], true)
            || blank(config('services.xendit.secret_key'))) {
            $this->finishFailed($withdrawal, 'PAYOUT_CONFIGURATION_MISSING');

            return;
        }

        $account = $withdrawal->account_snapshot;
        $holderName = trim((string) $account['account_holder_name']);
        $recipient = [
            'type' => $account['recipient_type'],
            'relationship' => 'SUPPLIER',
            'account_details' => [
                'currency' => 'IDR',
                'account_country' => 'ID',
                'account_holder_name' => $holderName,
                'account_number' => $account['account_number'],
                'routing_type_1' => 'SWIFT',
                'routing_value_1' => $account['bank_swift_code'],
            ],
            'address' => ['country' => 'ID', 'street_line_1' => mb_substr($account['recipient_address'], 0, 255)],
        ];
        if ($account['recipient_type'] === 'BUSINESS') {
            $recipient['business_name'] = mb_substr($account['business_name'], 0, 50);
        } else {
            $nameParts = preg_split('/\s+/u', $holderName, 2) ?: [$holderName];
            $recipient['given_name'] = mb_substr($nameParts[0] ?? $holderName, 0, 50);
            $recipient['surname'] = mb_substr($nameParts[1] ?? $nameParts[0] ?? $holderName, 0, 50);
        }
        $body = [
            'reference_id' => $withdrawal->reference_id,
            'recipient' => $recipient,
            'payout_details' => ['source_currency' => 'IDR', 'source_amount' => (int) round((float) $withdrawal->amount), 'destination_currency' => 'IDR'],
            'source_of_fund' => config('services.xendit.payout_source_of_fund'),
            'purpose_code' => config('services.xendit.payout_purpose_code'),
            'description' => 'Pencairan saldo toko',
        ];
        $response = Http::baseUrl(rtrim((string) config('services.xendit.base_url'), '/'))
            ->withBasicAuth((string) config('services.xendit.secret_key'), '')
            ->withHeaders(['api-version' => '2025-09-01', 'idempotency-key' => $withdrawal->reference_id])
            ->acceptJson()->asJson()->timeout((int) config('services.xendit.timeout', 10))
            ->post('/v3/payouts', $body);

        if ($response->failed()) {
            if ($response->serverError() || $this->attempts() < $this->tries) {
                throw new RuntimeException('Xendit withdrawal request failed; retrying with the same idempotency key.');
            }
            $this->finishFailed($withdrawal, Str::limit((string) $response->json('error_code', 'XENDIT_API_ERROR'), 100, ''));

            return;
        }
        $payoutId = $response->json('payout_id');
        $providerStatus = $response->json('status');
        if (! is_string($payoutId) || ! is_string($providerStatus)) {
            throw new RuntimeException('Xendit withdrawal response is incomplete.');
        }
        $localStatus = match ($providerStatus) {
            'ACCEPTED', 'ROUTING', 'REQUESTED', 'READY', 'LOCKED' => 'accepted',
            'PENDING_COMPLIANCE_ASSESSMENT' => 'pending_compliance',
            'SUCCEEDED' => 'succeeded',
            'FAILED' => 'failed',
            'REJECTED' => 'rejected',
            'REVERSED' => 'reversed',
            default => throw new RuntimeException('Xendit withdrawal status is not supported.'),
        };
        $withdrawal->forceFill([
            'xendit_payout_id' => $payoutId,
            'status' => $localStatus,
            'completed_at' => in_array($localStatus, ['succeeded', 'failed', 'rejected', 'reversed'], true) ? now() : null,
        ])->save();
        if (in_array($localStatus, ['failed', 'rejected', 'reversed'], true)) {
            $this->releaseReservedBalance($withdrawal, (string) $withdrawal->failure_code);
        }
    }

    public function failed(?Throwable $exception): void
    {
        SellerWithdrawal::query()->whereKey($this->sellerWithdrawalId)->where('status', 'processing')->update([
            'status' => 'needs_reconciliation',
            'failure_code' => 'XENDIT_RESPONSE_UNCERTAIN',
            'updated_at' => now(),
        ]);
    }

    private function finishFailed(SellerWithdrawal $withdrawal, string $failureCode): void
    {
        DB::transaction(function () use ($withdrawal, $failureCode): void {
            $locked = SellerWithdrawal::query()->whereKey($withdrawal->getKey())->lockForUpdate()->firstOrFail();
            if (in_array($locked->status, ['failed', 'rejected', 'reversed', 'succeeded'], true)) {
                return;
            }
            $locked->forceFill(['status' => 'failed', 'failure_code' => $failureCode, 'completed_at' => now()])->save();
            $this->releaseReservedBalance($locked, $failureCode);
        }, 3);
    }

    private function releaseReservedBalance(SellerWithdrawal $withdrawal, string $failureCode): void
    {
        SellerBalanceEntry::query()->firstOrCreate(
            ['reference_id' => 'withdrawal-release-'.$withdrawal->getKey()],
            [
                'seller_id' => $withdrawal->seller_id,
                'seller_withdrawal_id' => $withdrawal->getKey(),
                'entry_type' => 'withdrawal_release',
                'amount' => $withdrawal->amount,
                'currency' => 'IDR',
                'description' => 'Saldo pencairan dikembalikan setelah status '.$failureCode,
            ]
        );
    }
}
