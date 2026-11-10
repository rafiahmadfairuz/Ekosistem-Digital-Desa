<x-layouts.app title="Laporan operasional BUMDes">
    <main id="main" class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-8">
        <x-ui.page-heading title="Laporan operasional BUMDes" description="Ringkasan sesi bantuan berdasarkan aktivitas yang tercatat. Ini bukan laporan keuangan." />
        <form method="GET" action="{{ route('bumdes.assistance.reports') }}" class="flex flex-wrap items-end gap-3 rounded-card border border-line bg-paper p-4">
            <div><label for="from" class="block text-sm font-medium">Dari tanggal</label><input id="from" name="from" type="date" value="{{ $from }}" class="mt-1 min-h-11 rounded-control border border-line bg-paper px-3 text-base"></div>
            <div><label for="to" class="block text-sm font-medium">Sampai tanggal</label><input id="to" name="to" type="date" value="{{ $to }}" class="mt-1 min-h-11 rounded-control border border-line bg-paper px-3 text-base"></div>
            <x-ui.button type="submit" variant="secondary">Tampilkan laporan</x-ui.button>
        </form>

        <dl class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([['Jumlah sesi', $total], ['Selesai', $completed], ['Menunggu atau berlangsung', $inProgress], ['Dibatalkan', $cancelled]] as [$label, $value])
                <div class="rounded-card border border-line bg-paper p-5"><dt class="text-sm text-muted">{{ $label }}</dt><dd class="mt-2 text-2xl font-semibold tabular-nums">{{ number_format($value, 0, ',', '.') }}</dd></div>
            @endforeach
        </dl>

        <section class="rounded-card border border-line bg-paper p-5" aria-labelledby="category-report-heading">
            <h2 id="category-report-heading" class="text-lg font-semibold">Sesi per layanan dan status</h2>
            @if ($counts->isEmpty())
                <p class="mt-4 text-sm text-muted">Tidak ada sesi pada rentang tanggal ini.</p>
            @else
                <div class="mt-4 overflow-x-auto"><table class="w-full min-w-[34rem] text-left text-sm"><thead><tr class="border-b border-line"><th scope="col" class="py-3 pr-4">Layanan</th><th scope="col" class="py-3 pr-4">Status</th><th scope="col" class="py-3 text-right">Jumlah</th></tr></thead><tbody>
                    @foreach ($counts as $row)
                        <tr class="border-b border-line last:border-b-0"><td class="py-3 pr-4">{{ $serviceLabels[$row->service_type] ?? $row->service_type }}</td><td class="py-3 pr-4">{{ $statusLabels[$row->status] ?? $row->status }}</td><td class="py-3 text-right tabular-nums">{{ number_format((int) $row->total, 0, ',', '.') }}</td></tr>
                    @endforeach
                </tbody></table></div>
            @endif
        </section>

        <section class="rounded-card border border-line bg-paper p-5" aria-labelledby="operator-report-heading">
            <h2 id="operator-report-heading" class="text-lg font-semibold">Aktivitas per petugas</h2>
            @if ($operatorCounts->isEmpty())
                <p class="mt-4 text-sm text-muted">Belum ada aktivitas petugas pada rentang tanggal ini.</p>
            @else
                <ul class="mt-3 divide-y divide-line">
                    @foreach ($operatorCounts as $row)
                        <li class="flex flex-wrap justify-between gap-2 py-3"><span>{{ $row->operator?->username ?? 'Petugas nonaktif' }}</span><span class="font-semibold tabular-nums">{{ number_format((int) $row->total, 0, ',', '.') }} sesi</span></li>
                    @endforeach
                </ul>
            @endif
        </section>
    </main>
</x-layouts.app>
