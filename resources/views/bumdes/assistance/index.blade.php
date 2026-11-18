<x-layouts.app :title="$isResident ? 'Riwayat bantuan BUMDes' : 'Sesi bantuan warga'">
    <main id="main" class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        {{-- Header Halaman Minimalis --}}
        <header class="flex flex-col gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="max-w-2xl">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    {{ $isResident ? 'Permintaan bantuan saya' : 'Permintaan dan sesi bantuan' }}
                </h1>
                <p class="mt-1.5 text-sm leading-relaxed text-slate-500">
                    {{ $isResident ? 'Ajukan bantuan layanan digital dan pantau progres hingga petugas BUMDes menerima serta mendampingi Anda.' : 'Tinjau permintaan bantuan warga, terima atau tolak dengan alasan jelas, lalu catat pendampingan yang dilakukan.' }}
                </p>
            </div>

            <div class="shrink-0">
                @if ($canCreate)
                    <a
                        href="{{ route('bumdes.assistance.create') }}"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow-md hover:shadow-emerald-600/25 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                    >
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M12 5v14" />
                            <path d="M5 12h14" />
                        </svg>
                        <span>Ajukan bantuan</span>
                    </a>
                @elseif ($isResident)
                    <a
                        href="{{ route('profile.show') }}"
                        class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs transition hover:-translate-y-0.5 hover:border-emerald-600 hover:bg-emerald-50/40 hover:text-emerald-700 hover:shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                    >
                        Lengkapi profil warga
                    </a>
                @endif
            </div>
        </header>

        {{-- Ringkasan 4 Metrik Operasional --}}
        @php
            $metricItems = [
                ['label' => 'Jumlah permintaan', 'value' => $totals['all'], 'dot' => 'bg-slate-400', 'note' => 'Total tercatat'],
                ['label' => 'Menunggu petugas', 'value' => $totals['pending'], 'dot' => 'bg-amber-500', 'note' => 'Antrean peninjauan'],
                ['label' => 'Sedang berlangsung', 'value' => $totals['in_progress'], 'dot' => 'bg-sky-500', 'note' => 'Dalam pendampingan'],
                ['label' => 'Selesai', 'value' => $totals['completed'], 'dot' => 'bg-emerald-500', 'note' => 'Layanan tuntas'],
            ];
        @endphp

        <dl class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Ringkasan operasional sesi bantuan">
            @foreach ($metricItems as $metric)
                @php
                    $formattedValue = number_format($metric['value'], 0, ',', '.');
                    $len = mb_strlen($formattedValue);
                    $sizeClass = match (true) {
                        $len <= 6 => 'text-2xl',
                        $len <= 10 => 'text-xl',
                        default => 'text-lg',
                    };
                @endphp
                <div class="min-w-0 rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm transition-shadow duration-200 hover:shadow-md">
                    <div class="flex items-center gap-2">
                        <span class="size-2 shrink-0 rounded-full {{ $metric['dot'] }}" aria-hidden="true"></span>
                        <dt class="truncate text-xs font-medium text-slate-500">{{ $metric['label'] }}</dt>
                    </div>
                    <dd class="mt-3 {{ $sizeClass }} font-bold leading-tight tracking-tight text-slate-900 tabular-nums break-words">
                        {{ $formattedValue }}
                    </dd>
                    <p class="mt-1.5 text-[11px] text-slate-400">{{ $metric['note'] }}</p>
                </div>
            @endforeach
        </dl>

        {{-- Aktivitas Layanan Terkait (Khusus Petugas / Manager) --}}
        @unless ($isResident)
            <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm" aria-labelledby="module-activity-heading">
                <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                    <h2 id="module-activity-heading" class="text-sm font-bold text-slate-900">Aktivitas layanan terkait</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Distribusi pendampingan lintas modul layanan desa.</p>
                </div>

                <div class="grid divide-y divide-slate-100 sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                    <div class="p-5">
                        <p class="text-xs font-medium text-slate-500">Surat berbantuan</p>
                        <p class="mt-1.5 text-xl font-bold text-slate-900 tabular-nums">
                            {{ number_format($assistedCorrespondenceCount, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="p-5">
                        <p class="text-xs font-medium text-slate-500">Sesi bantuan internet</p>
                        <p class="mt-1.5 text-xl font-bold text-slate-900 tabular-nums">
                            {{ number_format($internetAssistanceCount, 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="p-5">
                        <p class="text-xs font-medium text-slate-500">Panduan pasar desa</p>
                        <p class="mt-1.5 text-xl font-bold text-slate-900 tabular-nums">
                            {{ number_format($marketplaceAssistanceCount, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </section>
        @endunless

        {{-- Filter Status & Ringkasan Jenis Layanan --}}
        <div class="flex flex-col justify-between gap-4 rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs sm:flex-row sm:items-end sm:p-5">
            <form method="GET" action="{{ route('bumdes.assistance.index') }}" class="flex flex-wrap items-end gap-3">
                <div class="min-w-48">
                    <label for="status" class="block text-xs font-semibold text-slate-700">Filter status</label>
                    <select
                        id="status"
                        name="status"
                        class="mt-1.5 min-h-10 w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 text-sm text-slate-800 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                    >
                        <option value="">Semua status</option>
                        @foreach ($statusLabels as $key => $label)
                            <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <button
                    type="submit"
                    class="inline-flex min-h-10 cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                >
                    Terapkan
                </button>

                @if (request()->filled('status'))
                    <a
                        href="{{ route('bumdes.assistance.index') }}"
                        class="inline-flex min-h-10 items-center justify-center rounded-xl px-3 py-2 text-xs font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-800"
                    >
                        Reset
                    </a>
                @endif
            </form>

            @if ($activityCounts->isNotEmpty())
                <div class="flex flex-wrap items-center gap-2" aria-label="Jumlah sesi per jenis layanan">
                    @foreach ($activityCounts as $activity)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200/80 bg-slate-50/80 px-3 py-1 text-xs text-slate-600">
                            <span>{{ $serviceTypes[$activity->service_type] ?? $activity->service_type }}:</span>
                            <strong class="font-bold text-slate-900 tabular-nums">{{ number_format((int) $activity->total, 0, ',', '.') }}</strong>
                        </span>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Daftar Sesi Bantuan --}}
        <section aria-label="Daftar sesi bantuan" class="space-y-3.5">
            @forelse ($sessions as $session)
                @php
                    $sessionStatusStyles = [
                        'pending' => 'border-amber-200 bg-amber-50 text-amber-800',
                        'accepted' => 'border-sky-200 bg-sky-50 text-sky-800',
                        'in_progress' => 'border-sky-200 bg-sky-50 text-sky-800',
                        'awaiting_confirmation' => 'border-indigo-200 bg-indigo-50 text-indigo-800',
                        'completed' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                        'rejected' => 'border-rose-200 bg-rose-50 text-rose-700',
                        'cancelled' => 'border-rose-200 bg-rose-50 text-rose-700',
                    ];
                    $sessionDotStyles = [
                        'pending' => 'bg-amber-500',
                        'accepted' => 'bg-sky-500',
                        'in_progress' => 'bg-sky-500',
                        'awaiting_confirmation' => 'bg-indigo-500',
                        'completed' => 'bg-emerald-500',
                        'rejected' => 'bg-rose-500',
                        'cancelled' => 'bg-rose-500',
                    ];
                    $badgeClass = $sessionStatusStyles[$session->status] ?? 'border-slate-200 bg-slate-50 text-slate-700';
                    $dotClass = $sessionDotStyles[$session->status] ?? 'bg-slate-400';
                    $isUrgentAction = ! $isResident && $session->status === 'pending';
                @endphp

                <article class="group rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-500/70 hover:shadow-md">
                    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                        <div class="min-w-0 space-y-1.5">
                            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                                <span class="font-mono font-semibold text-slate-700" translate="no">{{ $session->session_reference }}</span>
                                <span class="text-slate-300" aria-hidden="true">·</span>
                                <span>Diajukan {{ $session->requested_at?->timezone(config('app.timezone'))->format('d M Y, H:i') }}</span>
                            </div>

                            <h2 class="break-words text-base font-bold text-slate-900 transition-colors group-hover:text-emerald-700">
                                {{ $serviceTypes[$session->service_type] ?? 'Bantuan layanan digital' }}
                            </h2>

                            @unless ($isResident)
                                <p class="text-xs text-slate-500 sm:text-sm">
                                    <span>Warga: <strong class="font-semibold text-slate-800">{{ $session->resident?->user?->username ?? 'Akun warga' }}</strong></span>
                                    <span class="mx-1.5 text-slate-300" aria-hidden="true">·</span>
                                    <span>Petugas: <span class="font-medium text-slate-700">{{ $session->operator?->username ?? 'Belum ditugaskan' }}</span></span>
                                </p>
                            @endunless
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3 sm:justify-end sm:border-t-0 sm:pt-0">
                            <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold {{ $badgeClass }}">
                                <span class="size-1.5 rounded-full {{ $dotClass }}" aria-hidden="true"></span>
                                <span>{{ $statusLabels[$session->status] ?? $session->status }}</span>
                            </span>

                            <a
                                href="{{ route('bumdes.assistance.show', $session) }}"
                                class="inline-flex min-h-10 items-center justify-center gap-1.5 rounded-xl px-4 py-2 text-xs font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 {{ $isUrgentAction ? 'bg-emerald-600 text-white shadow-sm shadow-emerald-600/20 hover:bg-emerald-700' : 'border border-slate-200 bg-white text-slate-700 shadow-2xs hover:border-emerald-600 hover:bg-emerald-50/40 hover:text-emerald-700' }}"
                            >
                                <span>{{ $isResident ? 'Lihat permintaan' : ($session->status === 'pending' ? 'Tinjau permintaan' : 'Kelola sesi') }}</span>
                                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M5 12h14" />
                                    <path d="m12 5 7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-slate-200/90 bg-white p-8 text-center shadow-sm">
                    <h2 class="text-base font-bold text-slate-900">Belum ada sesi bantuan</h2>
                    <p class="mx-auto mt-1.5 max-w-md text-xs leading-relaxed text-slate-500 sm:text-sm">
                        @if ($isResident)
                            Belum ada permintaan bantuan. Ajukan bantuan jika memerlukan pendampingan layanan digital.
                        @else
                            Permintaan bantuan warga yang menunggu dan sesi yang sudah diterima akan muncul di sini.
                        @endif
                    </p>
                    @if ($canCreate)
                        <div class="mt-5">
                            <a
                                href="{{ route('bumdes.assistance.create') }}"
                                class="inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                            >
                                Ajukan bantuan
                            </a>
                        </div>
                    @endif
                </div>
            @endforelse
        </section>

        <div class="pt-2">
            {{ $sessions->links() }}
        </div>
    </main>
</x-layouts.app>
