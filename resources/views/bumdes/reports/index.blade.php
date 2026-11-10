<x-layouts.app title="Laporan usaha BUMDes">
    <main id="main" class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-8">
        <x-ui.page-heading title="Laporan usaha BUMDes" description="Pantau pengguna, langganan aktif, pendapatan yang sudah dibayar, dan pengeluaran operasional." />

        <section class="rounded-card border border-line bg-paper p-5" aria-labelledby="report-period-heading">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h2 id="report-period-heading" class="text-lg font-semibold">Ringkasan bulanan</h2>
                    <p class="mt-1 text-sm text-muted">Pendapatan dihitung dari pembayaran terverifikasi. Pengeluaran hanya menghitung catatan yang masih berlaku.</p>
                </div>
                <form method="GET" action="{{ route('bumdes.reports.index') }}" class="flex flex-wrap items-end gap-3">
                    <label for="month" class="text-sm font-medium">Pilih bulan</label>
                    <input id="month" name="month" type="month" value="{{ $month }}" class="min-h-11 rounded-control border border-line bg-paper px-3 text-base">
                    <x-ui.button type="submit" variant="secondary">Tampilkan laporan</x-ui.button>
                </form>
            </div>
            <dl class="mt-5 grid min-w-0 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                <div class="min-w-0 rounded-control border border-line bg-canvas p-4"><dt class="text-sm text-muted">Akun warga aktif</dt><dd class="mt-2 break-words text-2xl font-semibold tabular-nums">{{ number_format($summary['active_resident_accounts'], 0, ',', '.') }}</dd></div>
                <div class="min-w-0 rounded-control border border-line bg-canvas p-4"><dt class="text-sm text-muted">Warga berlangganan aktif</dt><dd class="mt-2 break-words text-2xl font-semibold tabular-nums">{{ number_format($summary['active_subscribers'], 0, ',', '.') }}</dd></div>
                <div class="min-w-0 rounded-control border border-line bg-canvas p-4"><dt class="text-sm text-muted">Pendapatan terbayar · {{ $month }}</dt><dd class="mt-2 break-words text-xl font-semibold tabular-nums">Rp{{ number_format((float) $summary['confirmed_income'], 2, ',', '.') }}</dd></div>
                <div class="min-w-0 rounded-control border border-line bg-canvas p-4"><dt class="text-sm text-muted">Pengeluaran dicatat · {{ $month }}</dt><dd class="mt-2 break-words text-xl font-semibold tabular-nums">Rp{{ number_format((float) $summary['operating_expenses'], 2, ',', '.') }}</dd></div>
                <div class="min-w-0 rounded-control border border-line bg-canvas p-4"><dt class="text-sm text-muted">Selisih pendapatan dan pengeluaran</dt><dd class="mt-2 break-words text-xl font-semibold tabular-nums">Rp{{ number_format((float) $summary['operating_balance'], 2, ',', '.') }}</dd></div>
            </dl>
            <p class="mt-3 text-xs leading-5 text-muted">Selisih ini merupakan ringkasan operasional, bukan laporan akuntansi atau pajak resmi. Langganan aktif dihitung berdasarkan status dan tanggal berakhir hari ini.</p>
        </section>

        @if (session('status'))
            <x-ui.alert type="success">{{ session('status') }}</x-ui.alert>
        @endif

        <section class="grid items-start gap-6 xl:grid-cols-[minmax(0,0.8fr)_minmax(0,1.2fr)]" aria-label="Pencatatan dan riwayat pengeluaran">
            <div class="rounded-card border border-line bg-paper p-5">
                <h2 class="text-lg font-semibold">Catat pengeluaran operasional</h2>
                <p class="mt-1 text-sm leading-6 text-muted">Catat biaya yang benar-benar dikeluarkan. Data tidak dihapus; jika salah, batalkan dengan alasan lalu buat catatan pengganti.</p>
                <form method="POST" action="{{ route('bumdes.reports.expenses.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label for="category" class="block text-sm font-medium">Jenis pengeluaran</label>
                        <select id="category" name="category" required class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base">
                            <option value="">Pilih jenis…</option>
                            @foreach ($categories as $value => $label)
                                <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('category')<p class="mt-1 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium">Keperluan</label>
                        <textarea id="description" name="description" required minlength="5" maxlength="1000" rows="3" class="mt-1 w-full rounded-control border border-line bg-paper px-3 py-2 text-base" placeholder="Contoh: Penggantian kabel jaringan di balai desa…">{{ old('description') }}</textarea>
                        @error('description')<p class="mt-1 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="amount" class="block text-sm font-medium">Jumlah biaya (Rp)</label>
                        <input id="amount" name="amount" type="number" inputmode="decimal" min="0.01" max="999999999999.99" step="0.01" value="{{ old('amount') }}" required class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base" placeholder="Contoh: 250000">
                        @error('amount')<p class="mt-1 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="expense_date" class="block text-sm font-medium">Tanggal pengeluaran</label>
                        <input id="expense_date" name="expense_date" type="date" max="{{ today()->toDateString() }}" value="{{ old('expense_date', now()->toDateString()) }}" required class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base">
                        @error('expense_date')<p class="mt-1 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <x-ui.button type="submit">Simpan pengeluaran</x-ui.button>
                </form>
            </div>

            <div class="rounded-card border border-line bg-paper p-5">
                <h2 class="text-lg font-semibold">Riwayat pengeluaran · {{ $month }}</h2>
                <p class="mt-1 text-sm text-muted">Catatan yang dibatalkan tetap terlihat sebagai jejak koreksi dan tidak masuk ke total.</p>
                <div class="mt-4 divide-y divide-line border-y border-line">
                    @forelse ($expenses as $expense)
                        <article class="py-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <h3 class="break-words font-medium">{{ $categories[$expense->category] ?? 'Lainnya' }} · Rp{{ number_format((float) $expense->amount, 2, ',', '.') }}</h3>
                                    <p class="mt-1 break-words text-sm leading-6">{{ $expense->description }}</p>
                                    <p class="mt-1 text-xs text-muted">{{ $expense->expense_date->format('d/m/Y') }} · Dicatat {{ $expense->recorder?->username ?? 'akun yang sudah dihapus' }}</p>
                                </div>
                                @if ($expense->status === 'recorded')
                                    <span class="shrink-0 rounded-full border border-emerald-700 px-3 py-1 text-xs font-medium text-emerald-900">Dicatat</span>
                                @else
                                    <span class="shrink-0 rounded-full border border-red-700 px-3 py-1 text-xs font-medium text-red-900">Dibatalkan</span>
                                @endif
                            </div>
                            @if ($expense->status === 'recorded')
                                <details class="mt-3">
                                    <summary class="min-h-11 cursor-pointer py-2 text-sm font-medium text-red-800 focus-visible:outline-2 focus-visible:outline-offset-2">Batalkan catatan ini…</summary>
                                    <form method="POST" action="{{ route('bumdes.reports.expenses.void', $expense) }}" data-confirm="Catatan ini akan ditandai batal dan dikeluarkan dari total. Riwayatnya tetap tersimpan. Lanjutkan?" class="mt-2 flex flex-wrap items-end gap-2">
                                        @csrf
                                        <label for="void-reason-{{ $expense->id }}" class="min-w-48 flex-1 text-sm">Alasan pembatalan<input id="void-reason-{{ $expense->id }}" name="reason" required minlength="5" maxlength="500" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base" placeholder="Contoh: nominal salah dicatat…"></label>
                                        <button type="submit" class="min-h-11 rounded-control border border-red-700 px-4 text-sm font-semibold text-red-800 focus-visible:outline-2 focus-visible:outline-offset-2">Batalkan pengeluaran</button>
                                    </form>
                                </details>
                            @else
                                <p class="mt-2 text-sm text-muted">Dibatalkan {{ $expense->voided_at?->format('d/m/Y H:i') }} oleh {{ $expense->voider?->username ?? 'akun yang sudah dihapus' }}. Alasan: {{ $expense->void_reason }}</p>
                            @endif
                        </article>
                    @empty
                        <p class="py-8 text-sm text-muted">Belum ada pengeluaran untuk bulan ini.</p>
                    @endforelse
                </div>
                <div class="mt-4">{{ $expenses->links() }}</div>
            </div>
        </section>
    </main>
</x-layouts.app>
