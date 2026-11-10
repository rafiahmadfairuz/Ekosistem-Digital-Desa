<x-layouts.app title="Pemberitahuan">
    <main id="main" class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-8">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <x-ui.page-heading title="Pemberitahuan" description="Pembaruan layanan, permohonan, dan transaksi yang terkait dengan akun Anda." />
            @if ($notifications->contains(fn ($notification) => $notification->read_at === null))
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <x-ui.button type="submit" variant="secondary">Tandai semua dibaca</x-ui.button>
                </form>
            @endif
        </div>

        @if ($notifications->isEmpty())
            <x-ui.empty-state title="Belum ada pemberitahuan" description="Pembaruan yang terkait dengan akun Anda akan muncul di sini." />
        @else
            <section class="overflow-hidden rounded-card border border-line bg-paper" aria-label="Daftar pemberitahuan">
                <ul class="divide-y divide-line">
                    @foreach ($notifications as $notification)
                        <li class="flex flex-wrap items-start justify-between gap-4 p-5 {{ $notification->read_at === null ? 'bg-emerald-50/50' : '' }}">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="font-semibold break-words">{{ $notification->title }}</h2>
                                    @if ($notification->read_at === null)
                                        <span class="rounded-full border border-emerald-700/30 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-900">Baru</span>
                                    @else
                                        <span class="rounded-full border border-line px-2.5 py-1 text-xs text-muted">Sudah dibaca</span>
                                    @endif
                                </div>
                                <p class="mt-1 break-words text-sm leading-6 text-muted">{{ $notification->message }}</p>
                                <time class="mt-2 block text-xs text-muted" datetime="{{ $notification->created_at }}">{{ \Illuminate\Support\Carbon::parse($notification->created_at)->translatedFormat('d M Y, H:i') }}</time>
                            </div>
                            <form method="POST" action="{{ route('notifications.open', $notification->id) }}">
                                @csrf
                                <x-ui.button type="submit" variant="secondary">{{ $notification->reference_type ? 'Buka pembaruan' : 'Tandai dibaca' }}</x-ui.button>
                            </form>
                        </li>
                    @endforeach
                </ul>
                <div class="border-t border-line px-5 py-4">{{ $notifications->links() }}</div>
            </section>
        @endif
    </main>
</x-layouts.app>
