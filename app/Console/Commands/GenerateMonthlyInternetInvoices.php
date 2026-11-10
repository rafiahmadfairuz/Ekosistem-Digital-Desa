<?php

namespace App\Console\Commands;

use App\Models\InternetSubscription;
use App\Models\SubscriptionInvoice;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

#[Signature('internet:invoices:generate {--month= : Billing month as YYYY-MM; defaults to this month}')]
#[Description('Create one monthly invoice for each active internet subscription')]
class GenerateMonthlyInternetInvoices extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $month = $this->option('month') ?: now()->format('Y-m');
        try {
            $start = Carbon::createFromFormat('!Y-m', $month);
        } catch (\Throwable) {
            $this->error('Month must use YYYY-MM format.');

            return self::FAILURE;
        }
        if (! $start || $start->format('Y-m') !== $month) {
            $this->error('Month must use YYYY-MM format.');

            return self::FAILURE;
        }
        $periodStart = $start->copy()->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();
        $created = 0;
        SubscriptionInvoice::query()->where('status', 'unpaid')->whereDate('due_date', '<', today())->update(['status' => 'overdue']);
        InternetSubscription::query()->with('package')->where('status', 'active')->whereDate('start_date', '<=', $periodEnd)->where(function ($query) use ($periodStart): void {
            $query->whereNull('end_date')->orWhereDate('end_date', '>=', $periodStart);
        })->chunkById(100, function ($subscriptions) use ($periodStart, $periodEnd, &$created): void {
            foreach ($subscriptions as $subscription) {
                $alreadyIssued = $subscription->invoices()->whereDate('billing_period_start', $periodStart->toDateString())->exists();
                if ($alreadyIssued) {
                    continue;
                }

                try {
                    $amount = $subscription->package->monthly_price;
                    $subscription->invoices()->create([
                        'invoice_number' => 'INV-'.$periodStart->format('Ym').'-'.Str::upper(Str::random(10)),
                        'billing_period_start' => $periodStart->toDateString(),
                        'billing_period_end' => $periodEnd->toDateString(),
                        'amount' => $amount,
                        'due_date' => $periodStart->copy()->addDays(7)->toDateString(),
                        'status' => (float) $amount === 0.0 ? 'paid' : 'unpaid',
                        'paid_at' => (float) $amount === 0.0 ? now() : null,
                    ]);
                    $created++;
                } catch (UniqueConstraintViolationException) {
                    // Another scheduler process created this subscription/month first.
                }
            }
        });
        $this->info("Created {$created} invoice(s) for {$month}; existing billing periods were left unchanged.");

        return self::SUCCESS;
    }
}
