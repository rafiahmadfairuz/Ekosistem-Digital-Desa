<?php

namespace App\Services;

use App\Models\SellerBalanceEntry;
use App\Models\SellerWithdrawal;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class MarketplacePayoutWebhookProcessor
{
    /** @param array<string, mixed> $payload */
    public function process(array $payload, string $callbackToken): bool
    {
        $expectedToken = (string) config('services.xendit.payout_webhook_token');
        abort_unless($expectedToken !== '' && $callbackToken !== '' && hash_equals($expectedToken, $callbackToken), 403, 'Notifikasi payout tidak dapat diverifikasi.');
        $event = $payload['event'] ?? null;
        $data = $payload['data'] ?? null;
        abort_unless(is_string($event) && is_array($data), 422, 'Data notifikasi payout tidak lengkap.');
        $statusMap = [
            'v3_payout.succeeded' => ['SUCCEEDED', 'succeeded'],
            'v3_payout.failed' => ['FAILED', 'failed'],
            'v3_payout.reversed' => ['REVERSED', 'reversed'],
            'v3_payout.rejected' => ['REJECTED', 'rejected'],
            'v3_payout.pending_compliance' => ['PENDING_COMPLIANCE_ASSESSMENT', 'pending_compliance'],
        ];
        abort_unless(isset($statusMap[$event]), 422, 'Jenis notifikasi payout tidak didukung.');
        foreach (['payout_id', 'reference_id', 'status'] as $required) {
            abort_unless(isset($data[$required]) && is_string($data[$required]) && trim($data[$required]) !== '', 422, 'Data notifikasi payout tidak lengkap.');
        }
        abort_unless($data['status'] === $statusMap[$event][0], 422, 'Status payout tidak sesuai dengan jenis notifikasi.');
        abort_unless(($data['destination_currency'] ?? 'IDR') === 'IDR' && ($data['source_currency'] ?? 'IDR') === 'IDR', 422, 'Mata uang payout tidak cocok.');
        $eventReference = 'xendit-withdrawal-'.hash('sha256', $data['payout_id'].'|'.$event.'|'.($payload['created'] ?? ''));

        try {
            return DB::transaction(function () use ($data, $event, $eventReference, $statusMap): bool {
                $withdrawal = SellerWithdrawal::query()->where('reference_id', $data['reference_id'])->lockForUpdate()->firstOrFail();
                if ($withdrawal->xendit_payout_id === null && $withdrawal->status === 'needs_reconciliation') {
                    $withdrawal->forceFill(['xendit_payout_id' => $data['payout_id']])->save();
                }
                abort_unless($withdrawal->xendit_payout_id !== null && hash_equals($withdrawal->xendit_payout_id, $data['payout_id']), 409, 'Referensi payout tidak cocok.');
                if (isset($data['source_amount'])) {
                    abort_unless((int) $data['source_amount'] === (int) round((float) $withdrawal->amount), 409, 'Jumlah payout tidak cocok.');
                }
                $existingEventWithdrawalId = DB::table('seller_withdrawal_events')->where('event_reference', $eventReference)->value('seller_withdrawal_id');
                if ($existingEventWithdrawalId !== null) {
                    abort_unless((int) $existingEventWithdrawalId === (int) $withdrawal->getKey(), 409, 'Notifikasi payout digunakan oleh permintaan lain.');

                    return false;
                }
                $newStatus = $statusMap[$event][1];
                $allowedStatuses = [
                    'accepted' => ['succeeded', 'failed', 'reversed', 'rejected', 'pending_compliance'],
                    'pending_compliance' => ['succeeded', 'failed', 'rejected', 'pending_compliance'],
                    'succeeded' => ['reversed'],
                    'needs_reconciliation' => ['succeeded', 'failed', 'rejected', 'reversed', 'pending_compliance'],
                    'failed' => [], 'rejected' => [], 'reversed' => [],
                ];
                abort_unless(in_array($newStatus, $allowedStatuses[$withdrawal->status] ?? [], true), 409, 'Status payout tidak dapat berubah dari tahap saat ini.');
                $withdrawal->forceFill([
                    'status' => $newStatus,
                    'failure_code' => $data['failure_code'] ?? null,
                    'completed_at' => in_array($newStatus, ['succeeded', 'failed', 'rejected', 'reversed'], true) ? now() : null,
                ])->save();
                DB::table('seller_withdrawal_events')->insert([
                    'seller_withdrawal_id' => $withdrawal->getKey(),
                    'event_type' => $event,
                    'event_reference' => $eventReference,
                    'payload' => json_encode(['payout_id' => $withdrawal->xendit_payout_id, 'status' => $newStatus], JSON_THROW_ON_ERROR),
                    'signature_verified' => 1,
                    'received_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                if (in_array($newStatus, ['failed', 'rejected', 'reversed'], true)) {
                    SellerBalanceEntry::query()->firstOrCreate(
                        ['reference_id' => 'withdrawal-release-'.$withdrawal->getKey()],
                        [
                            'seller_id' => $withdrawal->seller_id,
                            'seller_withdrawal_id' => $withdrawal->getKey(),
                            'entry_type' => 'withdrawal_release',
                            'amount' => $withdrawal->amount,
                            'currency' => 'IDR',
                            'description' => 'Saldo pencairan dikembalikan setelah status '.$newStatus,
                        ]
                    );
                }
                app(AuditLogger::class)->record('marketplace.seller_withdrawal_status_updated', 'seller_withdrawal', (int) $withdrawal->getKey(), ['status' => $newStatus]);

                return true;
            }, 3);
        } catch (UniqueConstraintViolationException $exception) {
            if (DB::table('seller_withdrawal_events')->where('event_reference', $eventReference)->exists()) {
                return false;
            }

            throw $exception;
        }
    }
}
