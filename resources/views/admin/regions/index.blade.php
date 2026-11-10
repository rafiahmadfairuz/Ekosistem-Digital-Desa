<x-layouts.app title="Data wilayah">
<main id="main" class="mx-auto max-w-7xl px-4 py-7 sm:px-8 sm:py-9">
        <p class="text-sm font-medium text-muted">Administrasi <span aria-hidden="true">/</span> Data dasar</p>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight">Data desa, RW, dan RT</h1>
        <p class="mt-2 max-w-3xl text-base leading-6 text-muted">Siapkan hierarki wilayah sebelum warga mengisi profil atau petugas menerima penugasan. Setiap RT harus berada di bawah RW pada desa yang sama. Membuat data wilayah tidak memberikan akses kepada akun mana pun.</p>
        <a class="mt-3 inline-flex min-h-11 items-center font-medium text-brand underline underline-offset-4 focus-visible:outline-2" href="{{ route('admin.village-settings.index') }}">Buka pengaturan desa untuk mengedit atau menghapus wilayah</a>

        <div class="mt-6 grid items-start gap-6 lg:grid-cols-[minmax(0,1.15fr)_minmax(18rem,0.85fr)]">
            <div class="min-w-0 space-y-5">
                @if(auth()->user()->hasRole('Super Admin'))
                <section class="border border-line bg-paper p-5 sm:p-6" aria-labelledby="village-title">
                    <p class="text-sm font-medium text-muted">Langkah 1</p>
                    <h2 id="village-title" class="mt-1 text-lg font-semibold">Tambahkan desa</h2>
                    <form class="mt-4 grid gap-4 sm:grid-cols-[minmax(8rem,0.7fr)_minmax(12rem,1.3fr)_auto] sm:items-end" method="POST" action="{{ route('admin.villages.store') }}">
                        @csrf
                        <div>
                            <label class="block text-sm font-medium" for="village-code">Kode desa</label>
                            <input class="mt-2 min-h-11 w-full border border-slate-400 bg-white px-3 text-base" id="village-code" name="code" value="{{ old('code') }}" autocomplete="off" maxlength="30" placeholder="contoh: DESA01…">
                        </div>
                        <div>
                            <label class="block text-sm font-medium" for="village-name">Nama desa</label>
                            <input class="mt-2 min-h-11 w-full border border-slate-400 bg-white px-3 text-base" id="village-name" name="name" value="{{ old('name') }}" maxlength="150" placeholder="Nama desa…">
                        </div>
                        <button class="min-h-11 border border-slate-500 px-4 text-sm font-medium hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">Simpan desa</button>
                    </form>
                </section>

                <section class="border border-line bg-paper p-5 sm:p-6" aria-labelledby="rw-title">
                    <p class="text-sm font-medium text-muted">Langkah 2</p>
                    <h2 id="rw-title" class="mt-1 text-lg font-semibold">Tambahkan RW</h2>
                    @if ($activeVillages->isNotEmpty())
                        <form class="mt-4 grid gap-4 sm:grid-cols-2" method="POST" action="{{ route('admin.regions.rw.store') }}">
                            @csrf
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium" for="rw-village">Desa</label>
                                <select class="mt-2 min-h-11 w-full border border-slate-400 bg-white px-3 text-base" id="rw-village" name="village_id">
                                    <option value="">Pilih desa…</option>
                                    @foreach($activeVillages as $village)<option value="{{ $village->id }}" @selected((string) old('village_id') === (string) $village->id)>{{ $village->name }} · {{ $village->code }}</option>@endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium" for="rw-code">Kode RW</label>
                                <input class="mt-2 min-h-11 w-full border border-slate-400 bg-white px-3 text-base" id="rw-code" name="region_code" value="{{ old('region_code') }}" maxlength="30" placeholder="contoh: RW-01…">
                            </div>
                            <div>
                                <label class="block text-sm font-medium" for="rw-name">Nama RW</label>
                                <input class="mt-2 min-h-11 w-full border border-slate-400 bg-white px-3 text-base" id="rw-name" name="name" value="{{ old('name') }}" maxlength="120" placeholder="contoh: RW 01…">
                            </div>
                            <button class="min-h-11 justify-self-start border border-slate-500 px-4 text-sm font-medium hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand sm:col-span-2">Simpan RW</button>
                        </form>
                    @else
                        <p class="mt-3 border-l-2 border-slate-400 bg-slate-50 px-4 py-3 text-sm leading-6 text-muted">Tambahkan data desa pada Langkah 1 terlebih dahulu.</p>
                    @endif
                </section>

                <section class="border border-line bg-paper p-5 sm:p-6" aria-labelledby="rt-title">
                    <p class="text-sm font-medium text-muted">Langkah 3</p>
                    <h2 id="rt-title" class="mt-1 text-lg font-semibold">Tambahkan RT</h2>
                    @if ($rwOptions->isNotEmpty())
                        <form class="mt-4 grid gap-4 sm:grid-cols-2" method="POST" action="{{ route('admin.regions.rt.store') }}">
                            @csrf
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium" for="rt-parent">RW induk</label>
                                <select class="mt-2 min-h-11 w-full border border-slate-400 bg-white px-3 text-base" id="rt-parent" name="parent_id">
                                    <option value="">Pilih RW…</option>
                                    @foreach($rwOptions as $rw)<option value="{{ $rw->id }}" @selected((string) old('parent_id') === (string) $rw->id)>{{ $rw->village->name }} · {{ $rw->name }} ({{ $rw->region_code }})</option>@endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium" for="rt-code">Kode RT</label>
                                <input class="mt-2 min-h-11 w-full border border-slate-400 bg-white px-3 text-base" id="rt-code" name="region_code" value="{{ old('region_code') }}" maxlength="30" placeholder="contoh: RT-01…">
                            </div>
                            <div>
                                <label class="block text-sm font-medium" for="rt-name">Nama RT</label>
                                <input class="mt-2 min-h-11 w-full border border-slate-400 bg-white px-3 text-base" id="rt-name" name="name" value="{{ old('name') }}" maxlength="120" placeholder="contoh: RT 01…">
                            </div>
                            <button class="min-h-11 justify-self-start border border-slate-500 px-4 text-sm font-medium hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand sm:col-span-2">Simpan RT</button>
                        </form>
                    @else
                        <p class="mt-3 border-l-2 border-slate-400 bg-slate-50 px-4 py-3 text-sm leading-6 text-muted">Tambahkan setidaknya satu RW pada Langkah 2 terlebih dahulu.</p>
                    @endif
                </section>
                @endif
            </div>

            <aside class="min-w-0 border border-line bg-paper p-5 sm:p-6" aria-labelledby="hierarchy-title">
                <h2 id="hierarchy-title" class="text-lg font-semibold">Hierarki wilayah saat ini</h2>
                <p class="mt-1 text-sm leading-6 text-muted">Setiap desa dapat memiliki banyak RW. Setiap RW dapat menaungi beberapa RT, dan nomor RT boleh berulang di RW yang berbeda.</p>
                @if ($villages->isEmpty())
                    <p class="mt-5 border-y border-slate-200 py-5 text-sm leading-6 text-muted">Belum ada data desa. Mulai dari Langkah 1.</p>
                @else
                    <ul class="mt-5 space-y-5">
                        @foreach($villages as $village)
                            <li class="min-w-0">
                                <h3 class="break-words font-semibold">{{ $village->name }} <span class="font-normal text-muted">· {{ $village->code }}</span></h3>
                                @php($rwRegions = $village->regions->where('region_type', 'rw'))
                                @php($rtTotal = $rwRegions->sum(fn ($rw) => $rw->children->where('region_type', 'rt')->count()))
                                <p class="mt-2 text-sm text-muted">{{ $rwRegions->count() }} RW · {{ $rtTotal }} RT</p>
                                @if($rwRegions->isEmpty())
                                    <p class="mt-2 text-sm text-muted">Belum ada RW.</p>
                                @else
                                    <ul class="mt-3 space-y-3 border-l border-slate-300 pl-4">
                                        @foreach($rwRegions as $rw)
                                            <li>
                                                <p class="text-sm font-medium">{{ $rw->name }} <span class="font-normal text-muted">· {{ $rw->children->where('region_type', 'rt')->count() }} RT</span></p>
                                                @if($rw->children->where('region_type', 'rt')->isNotEmpty())
                                                    <ul class="mt-2 space-y-1 pl-3 text-sm text-muted">
                                                        @foreach($rw->children->where('region_type', 'rt') as $rt)
                                                            <li class="break-words">{{ $rt->name }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p class="mt-1 pl-3 text-sm text-muted">Belum ada RT.</p>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
                <p class="mt-6 border-t border-slate-200 pt-4 text-sm leading-6 text-muted">Setelah RT tersedia, warga dapat memilih tempat tinggalnya pada formulir profil. Penugasan petugas tetap harus dibuat terpisah.</p>
            </aside>
        </div>
    </main>
</x-layouts.app>
