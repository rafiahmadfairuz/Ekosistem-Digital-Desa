<?php

namespace App\Http\Controllers\Bumdes;

use App\Http\Controllers\Controller;
use App\Models\BusinessExpense;
use App\Services\AuditLogger;
use App\Services\BusinessReportService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BusinessReportController extends Controller
{
    private const CATEGORIES = [
        'network' => 'Jaringan dan infrastruktur',
        'maintenance' => 'Perawatan dan perbaikan',
        'utilities' => 'Listrik, air, dan utilitas',
        'staff' => 'Operasional tenaga kerja',
        'supplies' => 'Perlengkapan dan bahan',
        'transport' => 'Transportasi',
        'other' => 'Lainnya',
    ];

    public function index(Request $request, BusinessReportService $reports): View
    {
        $this->authorize('viewAny', BusinessExpense::class);
        $validated = $request->validate(['month' => ['nullable', 'date_format:Y-m']]);
        $month = $validated['month'] ?? now()->format('Y-m');
        $start = CarbonImmutable::createFromFormat('!Y-m', $month)->startOfMonth();
        $end = $start->endOfMonth();
        $expenses = BusinessExpense::query()
            ->with(['recorder', 'voider'])
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->orderByDesc('expense_date')
            ->orderByDesc('id')
            ->simplePaginate(20)
            ->withQueryString();

        return view('bumdes.reports.index', [
            'month' => $month,
            'summary' => $reports->summarize($start, $end),
            'expenses' => $expenses,
            'categories' => self::CATEGORIES,
        ]);
    }

    public function storeExpense(Request $request, BusinessReportService $reports, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('create', BusinessExpense::class);
        $data = $request->validate([
            'category' => ['required', 'in:'.implode(',', array_keys(self::CATEGORIES))],
            'description' => ['required', 'string', 'min:5', 'max:1000'],
            'amount' => ['required', 'decimal:0,2', 'gt:0', 'max:999999999999.99'],
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
        ]);
        $reports->recordExpense($request->user(), $data, $audit);

        return redirect()->route('bumdes.reports.index', ['month' => substr($data['expense_date'], 0, 7)])
            ->with('status', 'Pengeluaran dicatat pada laporan.');
    }

    public function voidExpense(Request $request, BusinessExpense $expense, BusinessReportService $reports, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('void', $expense);
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);
        $reports->voidExpense($expense, $request->user(), $data['reason'], $audit);

        return redirect()->route('bumdes.reports.index', ['month' => $expense->expense_date->format('Y-m')])
            ->with('status', 'Pengeluaran dibatalkan. Riwayatnya tetap disimpan dan nilainya dikeluarkan dari total.');
    }
}
