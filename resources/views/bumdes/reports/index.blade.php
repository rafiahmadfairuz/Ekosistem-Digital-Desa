<x-layouts.app title="Laporan usaha BUMDes">
    <main id="main" class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        {{-- Header Halaman & Filter Bulan --}}
        <header class="flex flex-col gap-5 border-b border-slate-200/80 pb-6 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    Laporan usaha BUMDes
                </h1>
                <p class="mt-1.5 text-sm leading-relaxed text-slate-500">
                    Pantau pengguna aktif, pelanggan berjalan, pendapatan terverifikasi, serta rincian pengeluaran operasional desa.
                </p>
            </div>

            <form
                method="GET"
                action="{{ route('bumdes.reports.index') }}"
                class="flex flex-wrap items-end gap-3 rounded-2xl border border-slate-200/90 bg-white p-3.5 shadow-md shadow-slate-900/5"
            >
                <div>
                    <label for="month" class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                        Periode laporan
                    </label>
                    <input
                        id="month"
                        name="month"
                        type="month"
                        value="{{ $month }}"
                        class="mt-1 min-h-10 rounded-xl border border-slate-200 bg-slate-50/70 px-3 text-sm font-semibold text-slate-800 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                    >
                </div>

                <button
                    type="submit"
                    class="inline-flex min-h-10 cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow-md hover:shadow-emerald-600/25 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                >
                    Tampilkan laporan
                </button>
            </form>
        </header>

        {{-- Notifikasi Status --}}
        @if (session('status'))
            <div role="status" class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50/90 px-4 py-3.5 text-sm font-medium text-emerald-900 shadow-sm">
                <svg class="mt-0.5 size-4 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" />
                    <path d="m9 12 2 2 4-4" />
                </svg>
                <p>{{ session('status') }}</p>
            </div>
        @endif

        {{-- Ringkasan Bulanan (Operasional & Keuangan) --}}
        @php
            $balance = (float) $summary['operating_balance'];
            $isSurplus = $balance >= 0;

            $formatNominalSize = static function (string $text): string {
                $len = mb_strlen($text);
                return match (true) {
                    $len <= 13 => 'text-2xl',
                    $len <= 17 => 'text-xl',
                    default => 'text-lg',
                };
            };

            $incomeText = 'Rp' . number_format((float) $summary['confirmed_income'], 2, ',', '.');
            $expenseText = 'Rp' . number_format((float) $summary['operating_expenses'], 2, ',', '.');
            $balanceText = ($balance < 0 ? '−Rp' : 'Rp') . number_format(abs($balance), 2, ',', '.');
        @endphp

        <section class="space-y-4" aria-labelledby="report-period-heading">
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <div>
                    <h2 id="report-period-heading" class="text-lg font-bold text-slate-900">
                        Ringkasan bulanan · {{ $month }}
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-500">
                        Pendapatan dihitung dari pembayaran terverifikasi. Pengeluaran hanya menghitung catatan yang masih berlaku.
                    </p>
                </div>
            </div>

            {{-- Baris 1: 3 Metrik Keuangan Utama dengan Shadow Bertingkat --}}
            <dl class="grid gap-5 lg:grid-cols-3">
                {{-- Kartu Utama: Selisih Operasional (Elevated Shadow) --}}
                <div class="relative min-w-0 overflow-hidden rounded-2xl border {{ $isSurplus ? 'border-emerald-200/90 bg-gradient-to-br from-emerald-50/85 via-white to-white shadow-lg shadow-emerald-950/5' : 'border-rose-200/90 bg-gradient-to-br from-rose-50/85 via-white to-white shadow-lg shadow-rose-950/5' }} p-6 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-xl">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="size-2.5 shrink-0 rounded-full {{ $isSurplus ? 'bg-emerald-500' : 'bg-rose-500' }}" aria-hidden="true"></span>
                            <dt class="text-xs font-bold uppercase tracking-wider {{ $isSurplus ? 'text-emerald-800' : 'text-rose-800' }}">
                                Selisih operasional
                            </dt>
                        </div>
                        <span class="rounded-full px-2.5 py-0.5 text-[11px] font-semibold {{ $isSurplus ? 'bg-emerald-100/80 text-emerald-800' : 'bg-rose-100/80 text-rose-800' }}">
                            {{ $isSurplus ? 'Surplus' : 'Defisit' }}
                        </span>
                    </div>
                    <dd class="mt-3.5 {{ $formatNominalSize($balanceText) }} font-bold leading-tight tracking-tight text-slate-900 tabular-nums break-words">
                        {{ $balanceText }}
                    </dd>
                    <p class="mt-2 text-xs text-slate-500">
                        Pendapatan dikurangi pengeluaran tercatat
                    </p>
                </div>

                {{-- Kartu Pendapatan Terbayar --}}
                <div class="min-w-0 rounded-2xl border border-slate-200/90 bg-white p-6 shadow-md shadow-slate-900/5 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                    <div class="flex items-center gap-2">
                        <span class="size-2 shrink-0 rounded-full bg-sky-500" aria-hidden="true"></span>
                        <dt class="truncate text-xs font-semibold text-slate-500">
                            Pendapatan terbayar · {{ $month }}
                        </dt>
                    </div>
                    <dd class="mt-3.5 {{ $formatNominalSize($incomeText) }} font-bold leading-tight tracking-tight text-slate-900 tabular-nums break-words">
                        {{ $incomeText }}
                    </dd>
                    <p class="mt-2 text-xs text-slate-400">
                        Total pembayaran masuk terkonfirmasi
                    </p>
                </div>

                {{-- Kartu Pengeluaran Dicatat --}}
                <div class="min-w-0 rounded-2xl border border-slate-200/90 bg-white p-6 shadow-md shadow-slate-900/5 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
                    <div class="flex items-center gap-2">
                        <span class="size-2 shrink-0 rounded-full bg-amber-500" aria-hidden="true"></span>
                        <dt class="truncate text-xs font-semibold text-slate-500">
                            Pengeluaran dicatat · {{ $month }}
                        </dt>
                    </div>
                    <dd class="mt-3.5 {{ $formatNominalSize($expenseText) }} font-bold leading-tight tracking-tight text-slate-900 tabular-nums break-words">
                        {{ $expenseText }}
                    </dd>
                    <p class="mt-2 text-xs text-slate-400">
                        Biaya operasional berstatus aktif
                    </p>
                </div>
            </dl>

            {{-- Baris 2: 2 Metrik Basis Pengguna & Pelanggan --}}
            <dl class="grid gap-5 sm:grid-cols-2">
                <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200/90 bg-white px-6 py-4 shadow-sm transition-shadow duration-200 hover:shadow-md">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="size-2 shrink-0 rounded-full bg-indigo-500" aria-hidden="true"></span>
                            <dt class="text-xs font-semibold text-slate-500">Akun warga aktif</dt>
                        </div>
                        <p class="mt-1 text-[11px] text-slate-400">Pengguna terdaftar yang dapat mengakses layanan</p>
                    </div>
                    <dd class="shrink-0 text-2xl font-bold text-slate-900 tabular-nums">
                        {{ number_format($summary['active_resident_accounts'], 0, ',', '.') }}
                    </dd>
                </div>

                <div class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200/90 bg-white px-6 py-4 shadow-sm transition-shadow duration-200 hover:shadow-md">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="size-2 shrink-0 rounded-full bg-teal-500" aria-hidden="true"></span>
                            <dt class="text-xs font-semibold text-slate-500">Warga berlangganan aktif</dt>
                        </div>
                        <p class="mt-1 text-[11px] text-slate-400">Dihitung berdasarkan status dan masa aktif hari ini</p>
                    </div>
                    <dd class="shrink-0 text-2xl font-bold text-slate-900 tabular-nums">
                        {{ number_format($summary['active_subscribers'], 0, ',', '.') }}
                    </dd>
                </div>
            </dl>

            {{-- Catatan Kaki Akuntansi --}}
            <div class="flex items-start gap-2.5 rounded-xl border border-slate-200/80 bg-slate-50/90 px-4 py-3 text-xs leading-relaxed text-slate-500">
                <svg class="mt-0.5 size-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 16v-4" />
                    <path d="M12 8h.01" />
                </svg>
                <p>Selisih ini merupakan ringkasan operasional, bukan laporan akuntansi atau pajak resmi. Langganan aktif dihitung berdasarkan status dan tanggal berakhir hari ini.</p>
            </div>
        </section>

        {{-- Kolom Utama: Form Pencatatan (5 cols) & Riwayat Pengeluaran (7 cols) --}}
        <section class="grid items-start gap-6 xl:grid-cols-12" aria-label="Pencatatan dan riwayat pengeluaran">
            {{-- Form Catat Pengeluaran (Elevated Card) --}}
            <form
                method="POST"
                action="{{ route('bumdes.reports.expenses.store') }}"
                class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-lg shadow-slate-900/5 xl:col-span-5 xl:sticky xl:top-6"
            >
                @csrf
                <div class="border-b border-slate-100 bg-slate-50/70 px-6 py-4">
                    <h2 class="text-base font-bold text-slate-900">Catat pengeluaran operasional</h2>
                    <p class="mt-0.5 text-xs leading-relaxed text-slate-500">
                        Catat biaya yang benar-benar dikeluarkan. Data tidak dihapus; jika salah, batalkan dengan alasan lalu buat catatan pengganti.
                    </p>
                </div>

                <div class="space-y-4 p-6">
                    <div>
                        <label for="category" class="block text-xs font-semibold text-slate-700">
                            Jenis pengeluaran <span class="text-rose-500">*</span>
                        </label>
                        <select
                            id="category"
                            name="category"
                            required
                            class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-sm text-slate-900 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                        >
                            <option value="">Pilih jenis…</option>
                            @foreach ($categories as $value => $label)
                                <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('category')
                            <p class="mt-1 text-xs text-rose-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-xs font-semibold text-slate-700">
                            Keperluan <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            id="description"
                            name="description"
                            required
                            minlength="5"
                            maxlength="1000"
                            rows="3"
                            placeholder="Contoh: Penggantian kabel jaringan di balai desa…"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm leading-relaxed text-slate-900 placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-rose-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="amount" class="block text-xs font-semibold text-slate-700">
                                Jumlah biaya <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative mt-1.5">
                                <span class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-xs font-semibold text-slate-400">
                                    Rp
                                </span>
                                <input
                                    id="amount"
                                    name="amount"
                                    type="number"
                                    inputmode="decimal"
                                    min="0.01"
                                    max="999999999999.99"
                                    step="0.01"
                                    value="{{ old('amount') }}"
                                    required
                                    placeholder="250000"
                                    class="min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 pr-3.5 pl-9.5 text-sm font-semibold text-slate-900 tabular-nums placeholder:font-normal placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                >
                            </div>
                            @error('amount')
                                <p class="mt-1 text-xs text-rose-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="expense_date" class="block text-xs font-semibold text-slate-700">
                                Tanggal pengeluaran <span class="text-rose-500">*</span>
                            </label>
                            <input
                                id="expense_date"
                                name="expense_date"
                                type="date"
                                max="{{ today()->toDateString() }}"
                                value="{{ old('expense_date', now()->toDateString()) }}"
                                required
                                class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-sm font-medium text-slate-900 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                            >
                            @error('expense_date')
                                <p class="mt-1 text-xs text-rose-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="flex justify-end border-t border-slate-100 bg-slate-50/70 px-6 py-4">
                    <button
                        type="submit"
                        class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-emerald-600/20 transition hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-600/25 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:w-auto"
                    >
                        Simpan pengeluaran
                    </button>
                </div>
            </form>

            {{-- Riwayat Pengeluaran (7 cols) --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-md shadow-slate-900/5 xl:col-span-7">
                <div class="border-b border-slate-100 bg-slate-50/70 px-6 py-4">
                    <h2 class="text-base font-bold text-slate-900">Riwayat pengeluaran · {{ $month }}</h2>
                    <p class="mt-0.5 text-xs text-slate-500">
                        Catatan yang dibatalkan tetap terlihat sebagai jejak koreksi dan tidak dihitung dalam total pengeluaran.
                    </p>
                </div>

                <div class="divide-y divide-slate-100 px-6">
                    @forelse ($expenses as $expense)
                        @php
                            $isRecorded = $expense->status === 'recorded';
                        @endphp
                        <article class="py-5 {{ $isRecorded ? '' : 'opacity-80' }}">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-md bg-slate-100 px-2.5 py-0.5 text-xs font-semibold text-slate-700">
                                            {{ $categories[$expense->category] ?? 'Lainnya' }}
                                        </span>
                                        <span class="text-xs text-slate-400 tabular-nums">
                                            {{ $expense->expense_date->format('d/m/Y') }}
                                        </span>
                                    </div>

                                    <p class="mt-2 break-words text-sm font-medium leading-relaxed text-slate-800">
                                        {{ $expense->description }}
                                    </p>

                                    <p class="mt-1.5 text-xs text-slate-400">
                                        Dicatat oleh <span class="font-medium text-slate-600">{{ $expense->recorder?->username ?? 'akun yang sudah dihapus' }}</span>
                                    </p>
                                </div>

                                <div class="flex shrink-0 flex-col items-end gap-2">
                                    <p class="text-base font-bold tabular-nums {{ $isRecorded ? 'text-slate-900' : 'text-slate-400 line-through' }}">
                                        Rp{{ number_format((float) $expense->amount, 2, ',', '.') }}
                                    </p>

                                    @if ($isRecorded)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold text-emerald-800">
                                            <span class="size-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                                            <span>Dicatat</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-rose-200 bg-rose-50 px-2.5 py-0.5 text-[11px] font-semibold text-rose-700">
                                            <span class="size-1.5 rounded-full bg-rose-500" aria-hidden="true"></span>
                                            <span>Dibatalkan</span>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            @if ($isRecorded)
                                <details class="group mt-3.5 rounded-xl border border-slate-200/80 bg-slate-50/70">
                                    <summary class="flex min-h-10 cursor-pointer list-none items-center justify-between px-3.5 py-2 text-xs font-semibold text-rose-700 transition hover:bg-rose-50/50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-600">
                                        <span>Batalkan catatan ini…</span>
                                        <svg class="size-4 text-rose-400 transition-transform duration-200 group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="m6 9 6 6 6-6" />
                                        </svg>
                                    </summary>

                                    <form
                                        method="POST"
                                        action="{{ route('bumdes.reports.expenses.void', $expense) }}"
                                        data-confirm="Catatan ini akan ditandai batal dan dikeluarkan dari total. Riwayatnya tetap tersimpan. Lanjutkan?"
                                        class="flex flex-col gap-3 border-t border-slate-200/70 bg-white p-3.5 sm:flex-row sm:items-end"
                                    >
                                        @csrf
                                        <div class="min-w-0 flex-1">
                                            <label for="void-reason-{{ $expense->id }}" class="block text-xs font-semibold text-slate-700">
                                                Alasan pembatalan <span class="text-rose-500">*</span>
                                            </label>
                                            <input
                                                id="void-reason-{{ $expense->id }}"
                                                name="reason"
                                                required
                                                minlength="5"
                                                maxlength="500"
                                                placeholder="Contoh: nominal salah dicatat…"
                                                class="mt-1 min-h-10 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 text-xs text-slate-900 placeholder:text-slate-400 transition focus:border-rose-500 focus:bg-white focus:ring-2 focus:ring-rose-500/15 focus:outline-none sm:text-sm"
                                            >
                                        </div>

                                        <button
                                            type="submit"
                                            class="inline-flex min-h-10 shrink-0 cursor-pointer items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-xs font-semibold text-rose-700 shadow-2xs transition hover:bg-rose-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-600"
                                        >
                                            Batalkan pengeluaran
                                        </button>
                                    </form>
                                </details>
                            @else
                                <div class="mt-3 rounded-xl border border-rose-200/70 bg-rose-50/60 px-3.5 py-2.5 text-xs leading-relaxed text-rose-800">
                                    Dibatalkan {{ $expense->voided_at?->format('d/m/Y H:i') }} oleh <strong class="font-semibold">{{ $expense->voider?->username ?? 'akun yang sudah dihapus' }}</strong>.
                                    <span class="block mt-0.5 text-rose-700">Alasan: {{ $expense->void_reason }}</span>
                                </div>
                            @endif
                        </article>
                    @empty
                        <div class="py-10 text-center">
                            <p class="text-sm font-semibold text-slate-700">Belum ada pengeluaran</p>
                            <p class="mt-1 text-xs text-slate-500">Belum ada catatan pengeluaran operasional untuk periode {{ $month }}.</p>
                        </div>
                    @endforelse
                </div>

                @if ($expenses->hasPages())
                    <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-4">
                        {{ $expenses->links() }}
                    </div>
                @endif
            </div>
        </section>
    </main>
</x-layouts.app>
