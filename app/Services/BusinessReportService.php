<?php

namespace App\Services;

use App\Models\BusinessExpense;
use App\Models\InternetSubscription;
use App\Models\Payment;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class BusinessReportService
{
    /** @return array{active_resident_accounts: int, active_subscribers: int, confirmed_income: string, operating_expenses: string, operating_balance: string} */
    public function summarize(CarbonImmutable $start, CarbonImmutable $end): array
    {
        $incomeCents = $this->amountToCents((string) Payment::query()
            ->where('status', 'paid')
            ->whereBetween('paid_at', [$start, $end])
            ->sum('amount'));
        $expenseCents = $this->amountToCents((string) BusinessExpense::query()
            ->where('status', 'recorded')
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->sum('amount'));
        $income = $this->formatCents($incomeCents);
        $expenses = $this->formatCents($expenseCents);

        return [
            'active_resident_accounts' => User::query()
                ->where('account_status', 'active')
                ->whereHas('roles', fn ($query) => $query->where('name', 'Resident'))
                ->count(),
            'active_subscribers' => InternetSubscription::query()
                ->where('status', 'active')
                ->where(fn ($query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', today()))
                ->distinct()
                ->count('resident_id'),
            'confirmed_income' => $income,
            'operating_expenses' => $expenses,
            'operating_balance' => $this->formatCents($incomeCents - $expenseCents),
        ];
    }

    /** @param array{category: string, description: string, amount: string, expense_date: string} $data */
    public function recordExpense(User $actor, array $data, AuditLogger $audit): BusinessExpense
    {
        return DB::transaction(function () use ($actor, $data, $audit): BusinessExpense {
            $expense = BusinessExpense::query()->create([
                ...$data,
                'status' => 'recorded',
                'recorded_by' => $actor->getKey(),
            ]);
            $audit->record('business_expense.recorded', 'business_expense', (int) $expense->getKey(), [
                'category' => $expense->category,
                'amount' => $expense->amount,
            ]);

            return $expense;
        });
    }

    public function voidExpense(BusinessExpense $expense, User $actor, string $reason, AuditLogger $audit): void
    {
        DB::transaction(function () use ($expense, $actor, $reason, $audit): void {
            $lockedExpense = BusinessExpense::query()->lockForUpdate()->findOrFail($expense->getKey());
            abort_unless($lockedExpense->status === 'recorded', 409, 'Pengeluaran ini sudah dibatalkan.');
            $lockedExpense->update([
                'status' => 'voided',
                'voided_by' => $actor->getKey(),
                'voided_at' => now(),
                'void_reason' => $reason,
            ]);
            $audit->record('business_expense.voided', 'business_expense', (int) $lockedExpense->getKey(), [
                'category' => $lockedExpense->category,
                'reason_present' => filled($reason),
            ]);
        });
    }

    private function amountToCents(string $amount): int
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '00');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function formatCents(int $amount): string
    {
        $sign = $amount < 0 ? '-' : '';
        $absoluteAmount = abs($amount);

        return sprintf('%s%d.%02d', $sign, intdiv($absoluteAmount, 100), $absoluteAmount % 100);
    }
}
