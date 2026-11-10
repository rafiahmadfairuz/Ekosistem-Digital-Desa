<x-layouts.app title="Pengaturan desa">
    <main id="main" class="mx-auto max-w-6xl px-4 py-8 sm:px-8">
        <x-ui.breadcrumbs :items="[['label' => 'Beranda', 'url' => route('dashboard')], ['label' => 'Pengaturan desa']]" />
        <x-ui.page-heading class="mt-5" title="Pengaturan desa" description="Kelola informasi desa dan data RW/RT. Wilayah yang masih digunakan profil warga atau memiliki wilayah turunan tidak dapat dihapus. Riwayat penugasan tetap disimpan jika wilayah dihapus.">
            @if(auth()->user()->hasRole('Super Admin'))
                <x-slot:actions><a class="inline-flex min-h-11 items-center underline underline-offset-4" href="{{ route('admin.regions.index') }}">Tambah desa, RW, atau RT</a></x-slot:actions>
            @endif
        </x-ui.page-heading>

        @if(session('status'))<p class="mt-5 border-l-4 border-emerald-700 bg-emerald-50 p-4 text-sm text-emerald-950" role="status">{{ session('status') }}</p>@endif
        @error('region')<p class="mt-5 border-l-4 border-red-700 bg-red-50 p-4 text-sm text-red-900" role="alert">{{ $message }}</p>@enderror
        @error('village')<p class="mt-5 border-l-4 border-red-700 bg-red-50 p-4 text-sm text-red-900" role="alert">{{ $message }}</p>@enderror

        <div class="mt-7 space-y-6">
            @forelse($villages as $village)
                @php($rwRegions = $village->regions->where('region_type', 'rw'))
                @php($rtTotal = $rwRegions->sum(fn ($rw) => $rw->children->where('region_type', 'rt')->count()))
                <section class="rounded-card border border-line bg-paper p-5 sm:p-6" aria-labelledby="village-{{ $village->id }}-title">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <h2 id="village-{{ $village->id }}-title" class="break-words text-xl font-semibold">{{ $village->name }}</h2>
                            <p class="mt-1 text-sm text-muted">Kode {{ $village->code }} · {{ $rwRegions->count() }} RW · {{ $rtTotal }} RT · {{ $village->is_active ? 'Aktif' : 'Nonaktif' }}</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <a class="inline-flex min-h-11 items-center border border-slate-500 px-4 font-medium underline-offset-4 hover:underline focus-visible:outline-2" href="{{ route('admin.villages.edit', $village) }}">Edit data desa</a>
                            @if($village->regions->isEmpty())
                                <form method="POST" action="{{ route('admin.villages.destroy', $village) }}" data-confirm="Hapus desa {{ $village->name }}? Tindakan ini tidak dapat dibatalkan.">@csrf @method('DELETE')<button class="min-h-11 border border-red-700 px-4 font-medium text-red-800 focus-visible:outline-2">Hapus desa</button></form>
                            @else
                                <span class="text-sm text-muted">Hapus desa setelah seluruh RW/RT terhapus.</span>
                            @endif
                        </div>
                    </div>

                    @if($rwRegions->isEmpty())
                        <p class="mt-5 border-t border-line pt-4 text-sm text-muted">Belum ada RW. Desa ini dapat dihapus jika memang tidak digunakan.</p>
                    @else
                        <div class="mt-5 border-t border-line pt-4">
                            <h3 class="font-semibold">Hierarki RT/RW</h3>
                            <ul class="mt-3 space-y-4">
                                @foreach($rwRegions as $rw)
                                    @php($rtRegions = $rw->children->where('region_type', 'rt'))
                                    <li class="rounded-control border border-line p-4">
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <p class="font-medium">{{ $rw->name }} <span class="text-sm font-normal text-muted">· {{ $rtRegions->count() }} RT</span></p>
                                            @if(auth()->user()->hasRole('Super Admin'))
                                                @if($rtRegions->isEmpty() && $rw->residents->isEmpty())
                                                    <form method="POST" action="{{ route('admin.regions.destroy', $rw) }}" data-confirm="Hapus {{ $rw->name }} dari {{ $village->name }}? Tindakan ini tidak dapat dibatalkan.">@csrf @method('DELETE')<button class="min-h-11 border border-red-700 px-4 text-sm font-medium text-red-800 focus-visible:outline-2">Hapus RW</button></form>
                                                @elseif($rtRegions->isNotEmpty())
                                                    <span class="text-sm text-muted">Hapus RT yang tidak digunakan terlebih dahulu.</span>
                                                @elseif($rw->residents->isNotEmpty())
                                                    <span class="text-sm text-muted">RW masih digunakan oleh profil warga.</span>
                                                @endif
                                            @endif
                                        </div>

                                        @if($rtRegions->isNotEmpty())
                                            <ul class="mt-3 space-y-2 border-l border-line pl-4">
                                                @foreach($rtRegions as $rt)
                                                    <li class="flex flex-wrap items-center justify-between gap-3">
                                                        <span>{{ $rt->name }}</span>
                                                        @if(auth()->user()->hasRole('Super Admin'))
                                                            @if($rt->residents->isEmpty() && $rt->children->isEmpty())
                                                                <form method="POST" action="{{ route('admin.regions.destroy', $rt) }}" data-confirm="Hapus {{ $rt->name }} dari {{ $rw->name }}? Riwayat penugasan akan dipertahankan tanpa wilayah aktif.">@csrf @method('DELETE')<button class="min-h-11 border border-red-700 px-4 text-sm font-medium text-red-800 focus-visible:outline-2">Hapus RT</button></form>
                                                            @elseif($rt->residents->isNotEmpty())
                                                                <span class="text-sm text-muted">RT masih digunakan oleh profil warga.</span>
                                                            @else
                                                                <span class="text-sm text-muted">RT masih memiliki wilayah turunan.</span>
                                                            @endif
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="mt-2 text-sm text-muted">Belum ada RT pada RW ini.</p>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </section>
            @empty
                <x-ui.empty-state title="Belum ada data desa" description="Tambahkan data desa terlebih dahulu untuk mulai mengatur wilayah." />
            @endforelse
        </div>
    </main>
</x-layouts.app>
