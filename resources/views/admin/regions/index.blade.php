<x-layouts.app title="Data wilayah">
    <style>
        .rg-shell {
            max-width: 78rem;
            margin: 0 auto;
            padding: 1.75rem 1.25rem 3.5rem;
        }
        @media (min-width: 640px) {
            .rg-shell {
                padding: 2.25rem 2rem 4rem;
            }
        }
        .rg-header {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        @media (min-width: 1024px) {
            .rg-header {
                flex-direction: row;
                align-items: flex-start;
                justify-content: space-between;
                gap: 2rem;
            }
        }
        .rg-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #059669;
            margin: 0;
        }
        .rg-kicker-dot {
            width: 0.4rem;
            height: 0.4rem;
            border-radius: 9999px;
            background-color: #10b981;
        }
        .rg-title {
            margin: 0.375rem 0 0;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1.25;
            color: #1e293b;
        }
        @media (min-width: 640px) {
            .rg-title {
                font-size: 1.625rem;
            }
        }
        .rg-desc {
            margin: 0.5rem 0 0;
            max-width: 44rem;
            font-size: 0.875rem;
            line-height: 1.65;
            color: #64748b;
        }
        .rg-settings-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            min-height: 2.625rem;
            padding: 0 1.125rem;
            border-radius: 0.75rem;
            border: 1px solid #a7f3d0;
            background-color: #ecfdf5;
            color: #065f46;
            font-size: 0.8125rem;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            flex-shrink: 0;
            transition: background-color 0.16s ease, border-color 0.16s ease, color 0.16s ease;
        }
        .rg-settings-btn:hover {
            background-color: #059669;
            border-color: #059669;
            color: #ffffff;
        }
        .rg-layout {
            margin-top: 1.75rem;
            display: grid;
            grid-template-columns: 1fr;
            align-items: start;
            gap: 1.5rem;
        }
        @media (min-width: 1024px) {
            .rg-layout {
                grid-template-columns: minmax(0, 1.15fr) minmax(20rem, 0.85fr);
                gap: 1.75rem;
            }
        }
        .rg-steps {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            min-width: 0;
        }
        .rg-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.375rem 1.5rem;
            box-shadow: 0 2px 10px -3px rgba(15, 23, 42, 0.04);
            transition: border-color 0.16s ease, box-shadow 0.16s ease;
        }
        @media (min-width: 640px) {
            .rg-card {
                padding: 1.5rem 1.75rem;
            }
        }
        .rg-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 6px 18px -6px rgba(15, 23, 42, 0.06);
        }
        .rg-step-head {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .rg-step-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            height: 2rem;
            padding: 0 0.625rem;
            border-radius: 0.625rem;
            background-color: #ecfdf5;
            border: 1px solid #d1fae5;
            color: #059669;
            font-size: 0.75rem;
            font-weight: 700;
            flex-shrink: 0;
        }
        .rg-step-kicker {
            margin: 0;
            font-size: 0.6875rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #059669;
        }
        .rg-card-title {
            margin: 0.125rem 0 0;
            font-size: 1.0625rem;
            font-weight: 700;
            color: #1e293b;
        }
        .rg-form-village {
            margin-top: 1.25rem;
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.875rem;
        }
        @media (min-width: 640px) {
            .rg-form-village {
                grid-template-columns: minmax(8.5rem, 0.7fr) minmax(12rem, 1.3fr) auto;
                align-items: end;
            }
        }
        .rg-form-grid {
            margin-top: 1.25rem;
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.875rem;
        }
        @media (min-width: 640px) {
            .rg-form-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            .rg-span-2 {
                grid-column: span 2 / span 2;
            }
        }
        .rg-label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
        }
        .rg-input,
        .rg-select {
            margin-top: 0.375rem;
            min-height: 2.5rem;
            width: 100%;
            border-radius: 0.625rem;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            padding: 0 0.75rem;
            font-size: 0.8125rem;
            color: #1e293b;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .rg-input::placeholder {
            color: #94a3b8;
        }
        .rg-input:focus,
        .rg-select:focus {
            outline: none;
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.12);
        }
        .rg-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.5rem;
            padding: 0 1.125rem;
            border-radius: 0.625rem;
            border: none;
            background-color: #059669;
            color: #ffffff;
            font-size: 0.8125rem;
            font-weight: 600;
            white-space: nowrap;
            cursor: pointer;
            transition: background-color 0.16s ease, box-shadow 0.16s ease;
        }
        .rg-btn-primary:hover {
            background-color: #047857;
            box-shadow: 0 4px 12px -3px rgba(5, 150, 105, 0.3);
        }
        .rg-empty-step {
            margin-top: 1.125rem;
            border-radius: 0.75rem;
            border: 1px dashed #cbd5e1;
            background-color: #f8fafc;
            padding: 0.875rem 1rem;
            font-size: 0.8125rem;
            line-height: 1.6;
            color: #64748b;
        }
        .rg-aside {
            min-width: 0;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.375rem 1.5rem;
            box-shadow: 0 2px 10px -3px rgba(15, 23, 42, 0.04);
        }
        @media (min-width: 640px) {
            .rg-aside {
                padding: 1.5rem 1.75rem;
            }
        }
        @media (min-width: 1024px) {
            .rg-aside {
                position: sticky;
                top: 5.25rem;
            }
        }
        .rg-aside-title {
            margin: 0;
            font-size: 1.0625rem;
            font-weight: 700;
            color: #1e293b;
        }
        .rg-aside-desc {
            margin: 0.375rem 0 0;
            font-size: 0.8125rem;
            line-height: 1.6;
            color: #64748b;
        }
        .rg-tree-empty {
            margin-top: 1.25rem;
            border-radius: 0.75rem;
            border: 1px dashed #cbd5e1;
            background-color: #f8fafc;
            padding: 1.25rem 1rem;
            text-align: center;
            font-size: 0.8125rem;
            line-height: 1.6;
            color: #64748b;
        }
        .rg-village-list {
            margin: 1.25rem 0 0;
            padding: 0;
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .rg-village-node {
            border-radius: 0.875rem;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            padding: 1rem 1.125rem;
        }
        .rg-village-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }
        .rg-village-name {
            margin: 0;
            font-size: 0.9375rem;
            font-weight: 700;
            color: #1e293b;
            word-break: break-word;
        }
        .rg-village-code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.75rem;
            font-weight: 600;
            color: #059669;
        }
        .rg-count-pill {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            background-color: #ecfdf5;
            border: 1px solid #d1fae5;
            padding: 0.15rem 0.6rem;
            font-size: 0.6875rem;
            font-weight: 600;
            color: #047857;
            white-space: nowrap;
        }
        .rg-rw-list {
            margin: 0.875rem 0 0;
            padding: 0 0 0 0.875rem;
            list-style: none;
            border-left: 2px solid #a7f3d0;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .rg-rw-item {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 0.6875rem;
            padding: 0.6875rem 0.875rem;
        }
        .rg-rw-title {
            margin: 0;
            font-size: 0.8125rem;
            font-weight: 600;
            color: #334155;
        }
        .rg-rw-sub {
            font-weight: 500;
            color: #64748b;
        }
        .rg-rt-list {
            margin: 0.5rem 0 0;
            padding: 0;
            list-style: none;
            display: flex;
            flex-wrap: wrap;
            gap: 0.375rem;
        }
        .rg-rt-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 0.5rem;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            padding: 0.2rem 0.55rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: #475569;
            word-break: break-word;
        }
        .rg-muted-note {
            margin: 0.45rem 0 0;
            font-size: 0.75rem;
            color: #94a3b8;
        }
        .rg-aside-foot {
            margin: 1.25rem 0 0;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
            font-size: 0.75rem;
            line-height: 1.6;
            color: #64748b;
        }
    </style>

    <main id="main" class="rg-shell">
        <div class="rg-header">
            <div>
                <p class="rg-kicker">
                    <span class="rg-kicker-dot" aria-hidden="true"></span>
                    <span>Administrasi · Data dasar</span>
                </p>
                <h1 class="rg-title">Data desa, RW, dan RT</h1>
                <p class="rg-desc">Siapkan hierarki wilayah sebelum warga mengisi profil atau petugas menerima penugasan. Setiap RT harus berada di bawah RW pada desa yang sama. Membuat data wilayah tidak memberikan akses kepada akun mana pun.</p>
            </div>
            <a class="rg-settings-btn focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="{{ route('admin.village-settings.index') }}">
                <span>Buka pengaturan desa untuk mengedit atau menghapus wilayah</span>
                <span aria-hidden="true">›</span>
            </a>
        </div>

        <div class="rg-layout">
            <div class="rg-steps">
                @if(auth()->user()->hasRole('Super Admin'))
                <section class="rg-card" aria-labelledby="village-title">
                    <div class="rg-step-head">
                        <span class="rg-step-badge" aria-hidden="true">1</span>
                        <div>
                            <p class="rg-step-kicker">Langkah 1</p>
                            <h2 id="village-title" class="rg-card-title">Tambahkan desa</h2>
                        </div>
                    </div>
                    <form class="rg-form-village" method="POST" action="{{ route('admin.villages.store') }}">
                        @csrf
                        <div>
                            <label class="rg-label" for="village-code">Kode desa</label>
                            <input class="rg-input" id="village-code" name="code" value="{{ old('code') }}" autocomplete="off" maxlength="30" placeholder="contoh: DESA01…">
                        </div>
                        <div>
                            <label class="rg-label" for="village-name">Nama desa</label>
                            <input class="rg-input" id="village-name" name="name" value="{{ old('name') }}" maxlength="150" placeholder="Nama desa…">
                        </div>
                        <button class="rg-btn-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">Simpan desa</button>
                    </form>
                </section>

                <section class="rg-card" aria-labelledby="rw-title">
                    <div class="rg-step-head">
                        <span class="rg-step-badge" aria-hidden="true">2</span>
                        <div>
                            <p class="rg-step-kicker">Langkah 2</p>
                            <h2 id="rw-title" class="rg-card-title">Tambahkan RW</h2>
                        </div>
                    </div>
                    @if ($activeVillages->isNotEmpty())
                        <form class="rg-form-grid" method="POST" action="{{ route('admin.regions.rw.store') }}">
                            @csrf
                            <div class="rg-span-2">
                                <label class="rg-label" for="rw-village">Desa</label>
                                <select class="rg-select" id="rw-village" name="village_id">
                                    <option value="">Pilih desa…</option>
                                    @foreach($activeVillages as $village)<option value="{{ $village->id }}" @selected((string) old('village_id') === (string) $village->id)>{{ $village->name }} · {{ $village->code }}</option>@endforeach
                                </select>
                            </div>
                            <div>
                                <label class="rg-label" for="rw-code">Kode RW</label>
                                <input class="rg-input" id="rw-code" name="region_code" value="{{ old('region_code') }}" maxlength="30" placeholder="contoh: RW-01…">
                            </div>
                            <div>
                                <label class="rg-label" for="rw-name">Nama RW</label>
                                <input class="rg-input" id="rw-name" name="name" value="{{ old('name') }}" maxlength="120" placeholder="contoh: RW 01…">
                            </div>
                            <div class="rg-span-2">
                                <button class="rg-btn-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">Simpan RW</button>
                            </div>
                        </form>
                    @else
                        <p class="rg-empty-step">Tambahkan data desa pada Langkah 1 terlebih dahulu.</p>
                    @endif
                </section>

                <section class="rg-card" aria-labelledby="rt-title">
                    <div class="rg-step-head">
                        <span class="rg-step-badge" aria-hidden="true">3</span>
                        <div>
                            <p class="rg-step-kicker">Langkah 3</p>
                            <h2 id="rt-title" class="rg-card-title">Tambahkan RT</h2>
                        </div>
                    </div>
                    @if ($rwOptions->isNotEmpty())
                        <form class="rg-form-grid" method="POST" action="{{ route('admin.regions.rt.store') }}">
                            @csrf
                            <div class="rg-span-2">
                                <label class="rg-label" for="rt-parent">RW induk</label>
                                <select class="rg-select" id="rt-parent" name="parent_id">
                                    <option value="">Pilih RW…</option>
                                    @foreach($rwOptions as $rw)<option value="{{ $rw->id }}" @selected((string) old('parent_id') === (string) $rw->id)>{{ $rw->village->name }} · {{ $rw->name }} ({{ $rw->region_code }})</option>@endforeach
                                </select>
                            </div>
                            <div>
                                <label class="rg-label" for="rt-code">Kode RT</label>
                                <input class="rg-input" id="rt-code" name="region_code" value="{{ old('region_code') }}" maxlength="30" placeholder="contoh: RT-01…">
                            </div>
                            <div>
                                <label class="rg-label" for="rt-name">Nama RT</label>
                                <input class="rg-input" id="rt-name" name="name" value="{{ old('name') }}" maxlength="120" placeholder="contoh: RT 01…">
                            </div>
                            <div class="rg-span-2">
                                <button class="rg-btn-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">Simpan RT</button>
                            </div>
                        </form>
                    @else
                        <p class="rg-empty-step">Tambahkan setidaknya satu RW pada Langkah 2 terlebih dahulu.</p>
                    @endif
                </section>
                @endif
            </div>

            <aside class="rg-aside" aria-labelledby="hierarchy-title">
                <h2 id="hierarchy-title" class="rg-aside-title">Hierarki wilayah saat ini</h2>
                <p class="rg-aside-desc">Setiap desa dapat memiliki banyak RW. Setiap RW dapat menaungi beberapa RT, dan nomor RT boleh berulang di RW yang berbeda.</p>
                @if ($villages->isEmpty())
                    <p class="rg-tree-empty">Belum ada data desa. Mulai dari Langkah 1.</p>
                @else
                    <ul class="rg-village-list">
                        @foreach($villages as $village)
                            @php($rwRegions = $village->regions->where('region_type', 'rw'))
                            @php($rtTotal = $rwRegions->sum(fn ($rw) => $rw->children->where('region_type', 'rt')->count()))
                            <li class="rg-village-node min-w-0">
                                <div class="rg-village-head">
                                    <h3 class="rg-village-name">{{ $village->name }} <span class="rg-village-code">· {{ $village->code }}</span></h3>
                                    <span class="rg-count-pill">{{ $rwRegions->count() }} RW · {{ $rtTotal }} RT</span>
                                </div>
                                @if($rwRegions->isEmpty())
                                    <p class="rg-muted-note">Belum ada RW.</p>
                                @else
                                    <ul class="rg-rw-list">
                                        @foreach($rwRegions as $rw)
                                            <li class="rg-rw-item">
                                                <p class="rg-rw-title">{{ $rw->name }} <span class="rg-rw-sub">· {{ $rw->children->where('region_type', 'rt')->count() }} RT</span></p>
                                                @if($rw->children->where('region_type', 'rt')->isNotEmpty())
                                                    <ul class="rg-rt-list">
                                                        @foreach($rw->children->where('region_type', 'rt') as $rt)
                                                            <li class="rg-rt-chip">{{ $rt->name }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p class="rg-muted-note">Belum ada RT.</p>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
                <p class="rg-aside-foot">Setelah RT tersedia, warga dapat memilih tempat tinggalnya pada formulir profil. Penugasan petugas tetap harus dibuat terpisah.</p>
            </aside>
        </div>
    </main>
</x-layouts.app>
