<x-layouts.app title="Persuratan">
    <main id="main" class="mx-auto max-w-6xl px-4 py-8 sm:px-8">
        <x-ui.breadcrumbs :items="[['label' => 'Beranda', 'url' => route('dashboard')], ['label' => 'Persuratan']]" />
        <x-ui.page-heading class="mt-5" title="Persuratan" description="Ajukan dan lacak permohonan surat desa. Petugas melihat antrean sesuai peran dan wilayah penugasannya.">
            <x-slot:actions>
                @if ($canCreate)
                    <a href="{{ route('correspondence.create') }}" class="inline-flex min-h-11 items-center justify-center rounded-control bg-brand-strong px-4 text-sm font-semibold text-white hover:bg-emerald-900 focus-visible:outline-offset-2">Buat permohonan surat</a>
                @endif
                @if ($canManageTypes)
                    <a href="{{ route('correspondence.types') }}" class="inline-flex min-h-11 items-center rounded-control border border-line bg-paper px-4 text-sm font-medium hover:bg-slate-100 focus-visible:outline-offset-2">Kelola jenis surat</a>
                @endif
            </x-slot:actions>
        </x-ui.page-heading>

        @if ($residentNeedsVerifiedRegion)
            <div class="mt-5 rounded-card border border-amber-300 bg-amber-50 p-4 text-sm leading-6 text-amber-950" role="status">
                <p class="font-semibold">Lengkapi wilayah tempat tinggal</p>
                <p class="mt-1">Permohonan surat baru dapat dibuat setelah profil warga memiliki wilayah yang sudah diverifikasi. Daftar di bawah hanya menampilkan permohonan milik akun Anda.</p>
                <a href="{{ route('profile.show') }}" class="mt-2 inline-flex min-h-11 items-center font-medium underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2">Periksa profil warga</a>
            </div>
        @endif

        @if ($canAssist)
            <p class="mt-5 rounded-card border border-line bg-paper px-4 py-3 text-sm leading-6 text-muted">Untuk layanan berbantuan, warga tetap menjadi pemilik permohonan dan harus masuk ke akunnya untuk memeriksa serta mengonfirmasi isian sebelum dikirim ke RT.</p>
        @endif

        @if ($notifications->isNotEmpty())
            <section class="mt-6" aria-labelledby="correspondence-notifications-heading">
                <h2 id="correspondence-notifications-heading" class="text-base font-semibold">Pemberitahuan persuratan</h2>
                <ul class="mt-3 divide-y divide-line rounded-card border border-line bg-paper">
                    @foreach ($notifications as $notification)
                        <li class="flex flex-col gap-2 px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <p class="break-words text-sm {{ $notification['read_at'] ? 'text-muted' : 'font-medium text-ink' }}">{{ $notification['message'] }}</p>
                                <p class="mt-1 font-mono text-xs text-muted">{{ $notification['request_number'] }} · {{ \Illuminate\Support\Carbon::parse($notification['created_at'])->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</p>
                            </div>
                            @if ($notification['read_at'])
                                <a href="{{ $notification['url'] }}" class="inline-flex min-h-11 shrink-0 items-center text-sm text-brand-strong underline underline-offset-4">Buka permohonan</a>
                            @else
                                <form method="POST" action="{{ route('correspondence.notifications.read', $notification['id']) }}">
                                    @csrf
                                    <x-ui.button type="submit" variant="secondary">Buka dan tandai dibaca</x-ui.button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        <section class="mt-7" aria-labelledby="letters-heading">
            <h2 id="letters-heading" class="text-base font-semibold">Daftar permohonan</h2>
            <div class="mt-3 overflow-hidden rounded-card border border-line bg-paper">
                @forelse ($letters as $letter)
                    <a href="{{ route('correspondence.show', $letter) }}" class="flex min-h-16 flex-col justify-center gap-2 border-b border-line px-4 py-3 last:border-b-0 hover:bg-slate-50 focus-visible:outline-offset-[-3px] sm:flex-row sm:items-center sm:justify-between">
                        <span class="min-w-0">
                            <span class="block break-words font-medium">{{ $letter->letterType->name }}</span>
                            <span class="mt-1 block font-mono text-xs text-muted">{{ $letter->request_number }}</span>
                        </span>
                        <span class="flex flex-wrap items-center gap-3 text-sm">
                            <x-ui.status-badge :status="str_replace('_', ' ', ucfirst($letter->status))" :tone="match ($letter->status) { 'completed' => 'success', 'rejected' => 'danger', 'revision_required', 'signature_pending' => 'warning', default => 'neutral' }" />
                            <time class="text-muted" datetime="{{ $letter->updated_at->toIso8601String() }}">{{ $letter->updated_at->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</time>
                        </span>
                    </a>
                @empty
                    <div class="p-4">
                        <x-ui.empty-state title="Belum ada permohonan" description="Permohonan yang Anda ajukan atau perlu Anda periksa akan muncul di sini.">
                            @if ($canCreate)
                                <a href="{{ route('correspondence.create') }}" class="mt-3 inline-flex min-h-11 items-center font-medium text-brand-strong underline underline-offset-4">Buat permohonan surat</a>
                            @endif
                        </x-ui.empty-state>
                    </div>
                @endforelse
            </div>
            <div class="mt-4">{{ $letters->links() }}</div>
        </section>
    </main>
</x-layouts.app>
