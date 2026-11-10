<x-layouts.app :title="$isResident ? 'Riwayat bantuan BUMDes' : 'Sesi bantuan warga'">
    <main id="main" class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-8">
        <x-ui.page-heading
            :title="$isResident ? 'Permintaan bantuan saya' : 'Permintaan dan sesi bantuan'"
            :description="$isResident ? 'Ajukan bantuan digital dan pantau sampai petugas BUMDes menerima serta membantu Anda.' : 'Tinjau permintaan warga, terima atau tolak dengan alasan, lalu catat pendampingan yang Anda lakukan.'">
            <x-slot:actions>
                @if ($canCreate)
                    <a href="{{ route('bumdes.assistance.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-control bg-brand-strong px-4 text-sm font-semibold text-white hover:bg-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2">Ajukan bantuan</a>
                @elseif ($isResident)
                    <a href="{{ route('profile.show') }}" class="inline-flex min-h-11 items-center justify-center rounded-control border border-line px-4 text-sm font-semibold text-ink hover:bg-canvas focus-visible:outline-2 focus-visible:outline-offset-2">Lengkapi profil warga</a>
                @endif
            </x-slot:actions>
        </x-ui.page-heading>

        <dl class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan operasional sesi bantuan">
            @foreach ([['Jumlah permintaan', $totals['all']], ['Menunggu petugas', $totals['pending']], ['Sedang berlangsung', $totals['in_progress']], ['Selesai', $totals['completed']]] as [$label, $value])
                <div class="rounded-card border border-line bg-paper p-4"><dt class="text-sm text-muted">{{ $label }}</dt><dd class="mt-2 text-2xl font-semibold tabular-nums">{{ number_format($value, 0, ',', '.') }}</dd></div>
            @endforeach
        </dl>

        @unless ($isResident)
            <section class="grid gap-3 rounded-card border border-line bg-paper p-5 sm:grid-cols-3" aria-labelledby="module-activity-heading">
                <h2 id="module-activity-heading" class="sm:col-span-3 text-lg font-semibold">Aktivitas layanan terkait</h2>
                <p class="text-sm text-muted">Surat berbantuan: <strong class="text-ink tabular-nums">{{ number_format($assistedCorrespondenceCount, 0, ',', '.') }}</strong></p>
                <p class="text-sm text-muted">Sesi bantuan internet: <strong class="text-ink tabular-nums">{{ number_format($internetAssistanceCount, 0, ',', '.') }}</strong></p>
                <p class="text-sm text-muted">Panduan pasar desa: <strong class="text-ink tabular-nums">{{ number_format($marketplaceAssistanceCount, 0, ',', '.') }}</strong></p>
            </section>
        @endunless

        @if ($activityCounts->isNotEmpty())
            <section class="flex flex-wrap gap-2" aria-label="Jumlah sesi per jenis layanan">
                @foreach ($activityCounts as $activity)
                    <span class="rounded-full border border-line bg-paper px-3 py-2 text-sm">{{ $serviceTypes[$activity->service_type] ?? $activity->service_type }}: <strong class="tabular-nums">{{ number_format((int) $activity->total, 0, ',', '.') }}</strong></span>
                @endforeach
            </section>
        @endif

        <form method="GET" action="{{ route('bumdes.assistance.index') }}" class="flex flex-wrap items-end gap-3 rounded-card border border-line bg-paper p-4">
            <label for="status" class="text-sm font-medium">Filter status</label>
            <select id="status" name="status" class="min-h-11 rounded-control border border-line bg-paper px-3 text-base text-ink">
                <option value="">Semua status</option>
                @foreach ($statusLabels as $key => $label)
                    <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
                @endforeach
            </select>
            <x-ui.button type="submit" variant="secondary">Terapkan</x-ui.button>
        </form>

        <section aria-label="Daftar sesi bantuan" class="overflow-hidden rounded-card border border-line bg-paper">
            @forelse ($sessions as $session)
                <article class="grid gap-3 border-b border-line p-4 last:border-b-0 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-center sm:p-5">
                    <div class="min-w-0">
                        <h2 class="break-words font-semibold">{{ $serviceTypes[$session->service_type] ?? 'Bantuan layanan digital' }}</h2>
                        <p class="mt-1 break-words text-sm text-muted"><span class="font-mono" translate="no">{{ $session->session_reference }}</span> · Diajukan {{ $session->requested_at?->timezone(config('app.timezone'))->format('d M Y, H:i') }}</p>
                        @unless ($isResident)
                            <p class="mt-1 text-sm text-muted">Warga: <span class="font-medium text-ink">{{ $session->resident?->user?->username ?? 'Akun warga' }}</span> · Petugas: {{ $session->operator?->username ?? 'Belum ditugaskan' }}</p>
                        @endunless
                    </div>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded-full border border-line bg-canvas px-3 py-1 text-sm font-medium">{{ $statusLabels[$session->status] ?? $session->status }}</span>
                        <a href="{{ route('bumdes.assistance.show', $session) }}" class="inline-flex min-h-11 items-center rounded-control border border-line px-4 font-medium text-ink underline-offset-4 hover:bg-canvas hover:underline focus-visible:outline-2 focus-visible:outline-offset-2">{{ $isResident ? 'Lihat permintaan' : ($session->status === 'pending' ? 'Tinjau permintaan' : 'Kelola sesi') }}</a>
                    </div>
                </article>
            @empty
                <div class="p-8 text-center">
                    <h2 class="font-semibold">Belum ada sesi bantuan</h2>
                    <p class="mt-2 text-sm text-muted">@if ($isResident) Belum ada permintaan bantuan. Ajukan bantuan jika memerlukan pendampingan layanan digital. @else Permintaan bantuan warga yang menunggu dan sesi yang sudah diterima akan muncul di sini. @endif</p>
                    @if ($canCreate)
                        <div class="mt-4"><a href="{{ route('bumdes.assistance.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-control bg-brand-strong px-4 text-sm font-semibold text-white hover:bg-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2">Ajukan bantuan</a></div>
                    @endif
                </div>
            @endforelse
        </section>
        {{ $sessions->links() }}
    </main>
</x-layouts.app>
