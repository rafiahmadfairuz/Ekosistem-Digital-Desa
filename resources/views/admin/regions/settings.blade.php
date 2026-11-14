<x-layouts.app title="Pengaturan desa">
    <style>
        .vs-shell {
            max-width: 74rem;
            margin: 0 auto;
            padding: 1.75rem 1.25rem 3.5rem;
        }
        @media (min-width: 640px) {
            .vs-shell {
                padding: 2.25rem 2rem 4rem;
            }
        }
        .vs-breadcrumb {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.8125rem;
            color: #64748b;
            margin: 0;
        }
        .vs-breadcrumb-link {
            font-weight: 500;
            color: #64748b;
            text-decoration: none;
            transition: color 0.16s ease;
        }
        .vs-breadcrumb-link:hover {
            color: #059669;
        }
        .vs-breadcrumb-sep {
            color: #cbd5e1;
            user-select: none;
        }
        .vs-breadcrumb-current {
            font-weight: 600;
            color: #334155;
        }
        .vs-header {
            margin-top: 1rem;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        @media (min-width: 1024px) {
            .vs-header {
                flex-direction: row;
                align-items: flex-start;
                justify-content: space-between;
                gap: 2rem;
            }
        }
        .vs-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #059669;
            margin: 0;
        }
        .vs-kicker-dot {
            width: 0.4rem;
            height: 0.4rem;
            border-radius: 9999px;
            background-color: #10b981;
        }
        .vs-title {
            margin: 0.375rem 0 0;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1.25;
            color: #1e293b;
        }
        @media (min-width: 640px) {
            .vs-title {
                font-size: 1.625rem;
            }
        }
        .vs-desc {
            margin: 0.5rem 0 0;
            max-width: 44rem;
            font-size: 0.875rem;
            line-height: 1.65;
            color: #64748b;
        }
        .vs-add-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            min-height: 2.625rem;
            padding: 0 1.125rem;
            border-radius: 0.75rem;
            background-color: #059669;
            color: #ffffff;
            font-size: 0.8125rem;
            font-weight: 600;
            text-decoration: none;
            white-space: nowrap;
            flex-shrink: 0;
            box-shadow: 0 2px 8px -2px rgba(5, 150, 105, 0.28);
            transition: background-color 0.16s ease, box-shadow 0.16s ease;
        }
        .vs-add-btn:hover {
            background-color: #047857;
            box-shadow: 0 6px 14px -4px rgba(5, 150, 105, 0.35);
        }
        .vs-alert-success {
            margin-top: 1.25rem;
            border-radius: 0.875rem;
            border: 1px solid #a7f3d0;
            background-color: #ecfdf5;
            padding: 0.875rem 1.125rem;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #065f46;
        }
        .vs-alert-error {
            margin-top: 1.25rem;
            border-radius: 0.875rem;
            border: 1px solid #fecaca;
            background-color: #fef2f2;
            padding: 0.875rem 1.125rem;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #991b1b;
        }
        .vs-list {
            margin-top: 1.75rem;
            display: flex;
            flex-direction: column;
            gap: 1.375rem;
        }
        .vs-village-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.375rem 1.5rem;
            box-shadow: 0 2px 10px -3px rgba(15, 23, 42, 0.04);
            transition: border-color 0.16s ease, box-shadow 0.16s ease;
        }
        @media (min-width: 640px) {
            .vs-village-card {
                padding: 1.625rem 1.75rem;
            }
        }
        .vs-village-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 6px 18px -6px rgba(15, 23, 42, 0.06);
        }
        .vs-village-top {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .vs-village-top {
                flex-direction: row;
                align-items: flex-start;
                justify-content: space-between;
                gap: 1.5rem;
            }
        }
        .vs-village-head {
            display: flex;
            align-items: flex-start;
            gap: 0.875rem;
            min-width: 0;
        }
        .vs-monogram {
            width: 2.625rem;
            height: 2.625rem;
            border-radius: 0.75rem;
            background-color: #ecfdf5;
            border: 1px solid #d1fae5;
            color: #059669;
            font-size: 0.875rem;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .vs-village-title {
            margin: 0;
            font-size: 1.125rem;
            font-weight: 700;
            color: #1e293b;
            word-break: break-word;
            line-height: 1.3;
        }
        .vs-meta-pills {
            margin-top: 0.5rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.45rem;
        }
        .vs-pill-code {
            display: inline-flex;
            align-items: center;
            border-radius: 0.5rem;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 0.175rem 0.55rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
        }
        .vs-pill-count {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 0.175rem 0.625rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: #64748b;
        }
        .vs-pill-active {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 9999px;
            background-color: #ecfdf5;
            border: 1px solid #d1fae5;
            padding: 0.175rem 0.625rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #047857;
        }
        .vs-pill-inactive {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            border-radius: 9999px;
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 0.175rem 0.625rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
        }
        .vs-status-dot {
            width: 0.375rem;
            height: 0.375rem;
            border-radius: 9999px;
        }
        .vs-village-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.625rem;
            flex-shrink: 0;
        }
        .vs-btn-edit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.375rem;
            padding: 0 1rem;
            border-radius: 0.625rem;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            color: #334155;
            font-size: 0.8125rem;
            font-weight: 600;
            text-decoration: none;
            transition: border-color 0.15s ease, background-color 0.15s ease, color 0.15s ease;
        }
        .vs-btn-edit:hover {
            border-color: #6ee7b7;
            background-color: #ecfdf5;
            color: #047857;
        }
        .vs-btn-danger {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.375rem;
            padding: 0 0.875rem;
            border-radius: 0.625rem;
            border: 1px solid #fecaca;
            background-color: #ffffff;
            color: #b91c1c;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }
        .vs-btn-danger:hover {
            background-color: #fef2f2;
            border-color: #fca5a5;
        }
        .vs-btn-danger-sm {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.125rem;
            padding: 0 0.75rem;
            border-radius: 0.5625rem;
            border: 1px solid #fecaca;
            background-color: #ffffff;
            color: #b91c1c;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.15s ease, border-color 0.15s ease;
        }
        .vs-btn-danger-sm:hover {
            background-color: #fef2f2;
            border-color: #fca5a5;
        }
        .vs-hint {
            font-size: 0.75rem;
            color: #94a3b8;
        }
        .vs-empty-rw {
            margin-top: 1.25rem;
            border-radius: 0.75rem;
            border: 1px dashed #cbd5e1;
            background-color: #f8fafc;
            padding: 0.875rem 1rem;
            font-size: 0.8125rem;
            color: #64748b;
        }
        .vs-hierarchy {
            margin-top: 1.375rem;
            padding-top: 1.25rem;
            border-top: 1px solid #f1f5f9;
        }
        .vs-hierarchy-title {
            margin: 0;
            font-size: 0.8125rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #475569;
        }
        .vs-rw-list {
            margin: 0.875rem 0 0;
            padding: 0;
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
        }
        .vs-rw-card {
            border-radius: 0.875rem;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            padding: 1rem 1.125rem;
        }
        .vs-rw-head {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }
        .vs-rw-name {
            margin: 0;
            font-size: 0.9375rem;
            font-weight: 700;
            color: #1e293b;
        }
        .vs-rw-count {
            font-size: 0.75rem;
            font-weight: 500;
            color: #64748b;
        }
        .vs-rt-list {
            margin: 0.875rem 0 0;
            padding: 0 0 0 0.875rem;
            list-style: none;
            border-left: 2px solid #a7f3d0;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .vs-rt-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.625rem;
            border-radius: 0.625rem;
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
            padding: 0.5rem 0.875rem;
        }
        .vs-rt-name {
            font-size: 0.8125rem;
            font-weight: 600;
            color: #334155;
        }
        .vs-empty-wrap {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 2rem 1.5rem;
            box-shadow: 0 2px 10px -3px rgba(15, 23, 42, 0.04);
        }
    </style>

    <main id="main" class="vs-shell">
        <nav aria-label="Jejak navigasi" class="vs-breadcrumb">
            <a href="{{ route('dashboard') }}" class="vs-breadcrumb-link">Beranda</a>
            <span class="vs-breadcrumb-sep" aria-hidden="true">/</span>
            <span class="vs-breadcrumb-current" aria-current="page">Pengaturan desa</span>
        </nav>

        <div class="vs-header">
            <div>
                <p class="vs-kicker">
                    <span class="vs-kicker-dot" aria-hidden="true"></span>
                    <span>Administrasi Wilayah</span>
                </p>
                <h1 class="vs-title">Pengaturan desa</h1>
                <p class="vs-desc">Kelola informasi desa dan data RW/RT. Wilayah yang masih digunakan profil warga atau memiliki wilayah turunan tidak dapat dihapus. Riwayat penugasan tetap disimpan jika wilayah dihapus.</p>
            </div>
            @if(auth()->user()->hasRole('Super Admin'))
                <a class="vs-add-btn focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="{{ route('admin.regions.index') }}">
                    <span>Tambah desa, RW, atau RT</span>
                    <span aria-hidden="true">›</span>
                </a>
            @endif
        </div>

        @if(session('status'))<p class="vs-alert-success" role="status">{{ session('status') }}</p>@endif
        @error('region')<p class="vs-alert-error" role="alert">{{ $message }}</p>@enderror
        @error('village')<p class="vs-alert-error" role="alert">{{ $message }}</p>@enderror

        <div class="vs-list">
            @forelse($villages as $village)
                @php($rwRegions = $village->regions->where('region_type', 'rw'))
                @php($rtTotal = $rwRegions->sum(fn ($rw) => $rw->children->where('region_type', 'rt')->count()))
                <section class="vs-village-card" aria-labelledby="village-{{ $village->id }}-title">
                    <div class="vs-village-top">
                        <div class="vs-village-head">
                            <span class="vs-monogram" aria-hidden="true">{{ mb_substr($village->name, 0, 1) }}</span>
                            <div class="min-w-0">
                                <h2 id="village-{{ $village->id }}-title" class="vs-village-title">{{ $village->name }}</h2>
                                <div class="vs-meta-pills">
                                    <span class="vs-pill-code">Kode {{ $village->code }}</span>
                                    <span class="vs-pill-count">{{ $rwRegions->count() }} RW · {{ $rtTotal }} RT</span>
                                    @if($village->is_active)
                                        <span class="vs-pill-active">
                                            <span class="vs-status-dot" style="background-color: #10b981;" aria-hidden="true"></span>
                                            <span>Aktif</span>
                                        </span>
                                    @else
                                        <span class="vs-pill-inactive">
                                            <span class="vs-status-dot" style="background-color: #94a3b8;" aria-hidden="true"></span>
                                            <span>Nonaktif</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="vs-village-actions">
                            <a class="vs-btn-edit focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" href="{{ route('admin.villages.edit', $village) }}">Edit data desa</a>
                            @if($village->regions->isEmpty())
                                <form method="POST" action="{{ route('admin.villages.destroy', $village) }}" data-confirm="Hapus desa {{ $village->name }}? Tindakan ini tidak dapat dibatalkan." style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="vs-btn-danger focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Hapus desa</button>
                                </form>
                            @else
                                <span class="vs-hint">Hapus desa setelah seluruh RW/RT terhapus.</span>
                            @endif
                        </div>
                    </div>

                    @if($rwRegions->isEmpty())
                        <p class="vs-empty-rw">Belum ada RW. Desa ini dapat dihapus jika memang tidak digunakan.</p>
                    @else
                        <div class="vs-hierarchy">
                            <h3 class="vs-hierarchy-title">Hierarki RT/RW</h3>
                            <ul class="vs-rw-list">
                                @foreach($rwRegions as $rw)
                                    @php($rtRegions = $rw->children->where('region_type', 'rt'))
                                    <li class="vs-rw-card">
                                        <div class="vs-rw-head">
                                            <p class="vs-rw-name">{{ $rw->name }} <span class="vs-rw-count">· {{ $rtRegions->count() }} RT</span></p>
                                            @if(auth()->user()->hasRole('Super Admin'))
                                                @if($rtRegions->isEmpty() && $rw->residents->isEmpty())
                                                    <form method="POST" action="{{ route('admin.regions.destroy', $rw) }}" data-confirm="Hapus {{ $rw->name }} dari {{ $village->name }}? Tindakan ini tidak dapat dibatalkan." style="margin: 0;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="vs-btn-danger-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Hapus RW</button>
                                                    </form>
                                                @elseif($rtRegions->isNotEmpty())
                                                    <span class="vs-hint">Hapus RT yang tidak digunakan terlebih dahulu.</span>
                                                @elseif($rw->residents->isNotEmpty())
                                                    <span class="vs-hint">RW masih digunakan oleh profil warga.</span>
                                                @endif
                                            @endif
                                        </div>

                                        @if($rtRegions->isNotEmpty())
                                            <ul class="vs-rt-list">
                                                @foreach($rtRegions as $rt)
                                                    <li class="vs-rt-row">
                                                        <span class="vs-rt-name">{{ $rt->name }}</span>
                                                        @if(auth()->user()->hasRole('Super Admin'))
                                                            @if($rt->residents->isEmpty() && $rt->children->isEmpty())
                                                                <form method="POST" action="{{ route('admin.regions.destroy', $rt) }}" data-confirm="Hapus {{ $rt->name }} dari {{ $rw->name }}? Riwayat penugasan akan dipertahankan tanpa wilayah aktif." style="margin: 0;">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button class="vs-btn-danger-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">Hapus RT</button>
                                                                </form>
                                                            @elseif($rt->residents->isNotEmpty())
                                                                <span class="vs-hint">RT masih digunakan oleh profil warga.</span>
                                                            @else
                                                                <span class="vs-hint">RT masih memiliki wilayah turunan.</span>
                                                            @endif
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="vs-hint" style="margin-top: 0.5rem;">Belum ada RT pada RW ini.</p>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </section>
            @empty
                <div class="vs-empty-wrap">
                    <x-ui.empty-state title="Belum ada data desa" description="Tambahkan data desa terlebih dahulu untuk mulai mengatur wilayah." />
                </div>
            @endforelse
        </div>
    </main>
</x-layouts.app>
