@php
    $actionGroups = collect($navigationGroups)->except('Beranda');
    $rolesWithoutPhaseTwoDestinations = $roleNames->intersect(['BUMDes Operator', 'Seller', 'Village Head']);
    $accountStatusMap = [
        'active' => 'Aktif',
        'pending' => 'Menunggu aktivasi',
        'suspended' => 'Ditangguhkan',
        'disabled' => 'Dinonaktifkan',
    ];
    $currentAccountStatus = $accountStatusMap[auth()->user()->account_status] ?? auth()->user()->account_status;
@endphp

<x-layouts.app title="Ringkasan akses">
    <style>
        .db-shell {
            max-width: 76rem;
            margin: 0 auto;
            padding: 1.75rem 1.25rem 3.5rem;
        }
        @media (min-width: 640px) {
            .db-shell {
                padding: 2.25rem 2rem 4rem;
            }
        }

        .db-hero {
            position: relative;
            overflow: hidden;
            border-radius: 1.125rem;
            background: linear-gradient(130deg, #065f46 0%, #047857 52%, #059669 100%);
            padding: 1.5rem;
            box-shadow: 0 12px 28px -10px rgba(4, 120, 87, 0.35), 0 2px 6px -1px rgba(4, 120, 87, 0.12);
            color: #ffffff;
        }
        @media (min-width: 640px) {
            .db-hero {
                padding: 1.875rem 2.25rem;
            }
        }
        .db-hero-layout {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
        }
        @media (min-width: 1024px) {
            .db-hero-layout {
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                gap: 2.5rem;
            }
        }
        .db-hero-main {
            min-width: 0;
            flex: 1;
        }
        .db-hero-top-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.625rem;
        }
        .db-hero-title {
            font-size: 1.375rem;
            font-weight: 700;
            letter-spacing: -0.015em;
            line-height: 1.25;
            color: #ffffff;
            margin: 0;
        }
        @media (min-width: 640px) {
            .db-hero-title {
                font-size: 1.5rem;
            }
        }
        .db-hero-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.2rem 0.65rem;
            border-radius: 9999px;
            background-color: rgba(236, 253, 245, 0.95);
            color: #065f46;
            font-size: 0.6875rem;
            font-weight: 700;
        }
        .db-hero-desc {
            font-size: 0.8125rem;
            line-height: 1.6;
            color: rgba(236, 253, 245, 0.88);
            margin: 0.5rem 0 0;
            max-width: 38rem;
        }
        .db-hero-roles {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.45rem;
            margin-top: 0.875rem;
        }
        .db-hero-role-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.275rem 0.7rem;
            border-radius: 9999px;
            background-color: rgba(255, 255, 255, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.22);
            font-size: 0.75rem;
            font-weight: 500;
            color: #ffffff;
        }
        .db-hero-dot {
            width: 0.4rem;
            height: 0.4rem;
            border-radius: 9999px;
            background-color: #6ee7b7;
        }

        .db-hero-side {
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.16);
        }
        @media (min-width: 1024px) {
            .db-hero-side {
                flex-shrink: 0;
                max-width: 21rem;
                padding-top: 0;
                padding-left: 1.75rem;
                border-top: none;
                border-left: 1px solid rgba(255, 255, 255, 0.18);
            }
        }
        .db-hero-meta-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            font-size: 0.75rem;
        }
        .db-hero-meta-label {
            color: rgba(209, 250, 229, 0.85);
        }
        .db-hero-meta-value {
            font-weight: 600;
            color: #ffffff;
            text-align: right;
        }
        .db-hero-verify-link {
            color: #fde68a;
            text-decoration: underline;
            text-underline-offset: 3px;
            font-weight: 600;
        }
        .db-hero-verify-link:hover {
            color: #ffffff;
        }
        .db-hero-note {
            margin: 0.25rem 0 0;
            font-size: 0.6875rem;
            line-height: 1.5;
            color: rgba(209, 250, 229, 0.82);
        }

        .db-section {
            margin-top: 2.25rem;
        }
        .db-section-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }
        .db-section-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: #334155;
            margin: 0;
        }
        .db-section-sub {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .db-bento {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.125rem;
        }
        @media (min-width: 1024px) {
            .db-bento {
                grid-template-columns: repeat(12, minmax(0, 1fr));
            }
            .db-bento-main {
                grid-column: span 8 / span 8;
            }
            .db-bento-side {
                grid-column: span 4 / span 4;
            }
        }
        .db-kpi-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 2px 10px -3px rgba(15, 23, 42, 0.04);
            display: grid;
            grid-template-columns: 1fr;
            margin: 0;
        }
        @media (min-width: 640px) {
            .db-kpi-card {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        .db-kpi-item {
            padding: 1.375rem 1.5rem;
        }
        .db-kpi-item + .db-kpi-item {
            border-top: 1px solid #f1f5f9;
        }
        @media (min-width: 640px) {
            .db-kpi-item + .db-kpi-item {
                border-top: none;
                border-left: 1px solid #f1f5f9;
            }
        }
        .db-kpi-label-row {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .db-dot-amber {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 9999px;
            background-color: #f59e0b;
            flex-shrink: 0;
        }
        .db-dot-emerald {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 9999px;
            background-color: #10b981;
            flex-shrink: 0;
        }
        .db-dot-teal {
            width: 0.5rem;
            height: 0.5rem;
            border-radius: 9999px;
            background-color: #14b8a6;
            flex-shrink: 0;
        }
        .db-kpi-label {
            font-size: 0.75rem;
            font-weight: 500;
            color: #64748b;
        }
        .db-kpi-value {
            font-size: 1.5rem;
            font-weight: 700;
            line-height: 1.15;
            color: #334155;
            font-variant-numeric: tabular-nums;
            margin: 0.625rem 0 0;
        }
        .db-kpi-note {
            font-size: 0.6875rem;
            color: #94a3b8;
            margin: 0.35rem 0 0;
        }

        .db-authority-card {
            background: linear-gradient(145deg, #ecfdf5 0%, #f0fdf4 100%);
            border: 1px solid #d1fae5;
            border-radius: 1rem;
            padding: 1.375rem 1.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 2px 10px -3px rgba(5, 150, 105, 0.06);
        }
        .db-authority-kicker {
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #059669;
            margin: 0;
        }
        .db-authority-role {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0.45rem 0 0;
        }
        .db-authority-foot {
            font-size: 0.75rem;
            line-height: 1.5;
            color: #475569;
            margin: 1.125rem 0 0;
        }

        .db-cards-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.125rem;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        @media (min-width: 640px) {
            .db-cards-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        @media (min-width: 1024px) {
            .db-cards-grid {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
        }
        .db-service-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.25rem 1.375rem;
            text-decoration: none;
            box-shadow: 0 2px 8px -3px rgba(15, 23, 42, 0.04);
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }
        .db-service-card:hover {
            transform: translateY(-2px);
            border-color: #a7f3d0;
            box-shadow: 0 10px 24px -10px rgba(5, 150, 105, 0.15);
        }
        .db-service-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.875rem;
        }
        .db-service-title {
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1.4;
            color: #334155;
            margin: 0;
            transition: color 0.16s ease;
        }
        .db-service-card:hover .db-service-title {
            color: #047857;
        }
        .db-service-group {
            font-size: 0.75rem;
            color: #94a3b8;
            margin: 0.25rem 0 0;
        }
        .db-monogram {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.625rem;
            background-color: #ecfdf5;
            border: 1px solid #d1fae5;
            color: #059669;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background-color 0.18s ease, color 0.18s ease, border-color 0.18s ease;
        }
        .db-service-card:hover .db-monogram {
            background-color: #059669;
            border-color: #059669;
            color: #ffffff;
        }
        .db-service-desc {
            font-size: 0.75rem;
            line-height: 1.5;
            color: #64748b;
            margin: 1rem 0 0;
        }
        .db-service-foot {
            margin-top: 1.125rem;
            padding-top: 0.75rem;
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }
        .db-service-cta {
            font-size: 0.75rem;
            font-weight: 600;
            color: #059669;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            transition: transform 0.16s ease, color 0.16s ease;
        }
        .db-service-card:hover .db-service-cta {
            color: #047857;
            transform: translateX(2px);
        }

        .db-panel {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.375rem 1.5rem;
            box-shadow: 0 2px 8px -3px rgba(15, 23, 42, 0.04);
        }
        .db-panel + .db-panel {
            margin-top: 1.25rem;
        }
        .db-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            padding-bottom: 0.875rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .db-panel-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155;
            margin: 0;
        }
        .db-panel-sub {
            font-size: 0.75rem;
            color: #94a3b8;
            margin: 0.25rem 0 0;
        }
        .db-btn-emerald {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.5rem 0.9rem;
            border-radius: 0.625rem;
            background-color: #059669;
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.16s ease;
        }
        .db-btn-emerald:hover {
            background-color: #047857;
        }
    </style>

    <main id="main" class="db-shell">
        <section class="db-hero" aria-labelledby="dashboard-hero-title">
            <div class="db-hero-layout">
                <div class="db-hero-main">
                    <div class="db-hero-top-row">
                        <h1 id="dashboard-hero-title" class="db-hero-title break-words">
                            {{ auth()->user()->username }}
                        </h1>
                        <span class="db-hero-status-pill">
                            {{ $currentAccountStatus }}
                        </span>
                    </div>
                    <p class="db-hero-desc">
                        {{ config('app.name') }} menggabungkan akses warga dan layanan desa dalam satu akun. Daftar di bawah mengikuti peran dan penugasan yang aktif.
                    </p>
                    <div class="db-hero-roles">
                        @if ($roles->isNotEmpty())
                            @foreach ($roles as $role)
                                <span class="db-hero-role-pill">
                                    <span class="db-hero-dot" aria-hidden="true"></span>
                                    <span>{{ $role->name }}</span>
                                </span>
                            @endforeach
                        @else
                            <span class="db-hero-role-pill">
                                <span class="db-hero-dot" aria-hidden="true"></span>
                                <span>Pengguna Terdaftar</span>
                            </span>
                        @endif
                    </div>
                </div>

                <aside class="db-hero-side" aria-label="Status akun">
                    <div class="db-hero-meta-item">
                        <span class="db-hero-meta-label">Verifikasi email</span>
                        <span class="db-hero-meta-value">
                            @if (auth()->user()->hasVerifiedEmail())
                                <span style="color: #a7f3d0;">Terverifikasi</span>
                            @else
                                <a class="db-hero-verify-link" href="{{ route('verification.notice') }}">Belum diverifikasi · Lihat petunjuk</a>
                            @endif
                        </span>
                    </div>

                    @if ($adminSummary)
                        <p class="db-hero-note">
                            Super Admin mengelola akses teknis dan tidak otomatis memiliki kewenangan hukum untuk menyetujui atau menandatangani dokumen desa.
                        </p>
                    @endif
                </aside>
            </div>
        </section>

        @if ($adminSummary)
            <section class="db-section" aria-labelledby="village-account-summary">
                <div class="db-section-head">
                    <h2 id="village-account-summary" class="db-section-title">Ikhtisar akun desa</h2>
                    <span class="db-section-sub">Jumlah berasal dari data akun dan penugasan yang tersimpan</span>
                </div>

                <div class="db-bento">
                    <dl class="db-kpi-card db-bento-main">
                        <div class="db-kpi-item">
                            <div class="db-kpi-label-row">
                                <span class="db-dot-amber" aria-hidden="true"></span>
                                <dt class="db-kpi-label">Menunggu aktivasi</dt>
                            </div>
                            <dd class="db-kpi-value">
                                {{ number_format($adminSummary['pending_accounts'], 0, ',', '.') }}
                            </dd>
                            <p class="db-kpi-note">Antrean verifikasi</p>
                        </div>

                        <div class="db-kpi-item">
                            <div class="db-kpi-label-row">
                                <span class="db-dot-emerald" aria-hidden="true"></span>
                                <dt class="db-kpi-label">Akun aktif</dt>
                            </div>
                            <dd class="db-kpi-value">
                                {{ number_format($adminSummary['active_accounts'], 0, ',', '.') }}
                            </dd>
                            <p class="db-kpi-note">Terdaftar di sistem</p>
                        </div>

                        <div class="db-kpi-item">
                            <div class="db-kpi-label-row">
                                <span class="db-dot-teal" aria-hidden="true"></span>
                                <dt class="db-kpi-label">Penugasan RT/RW aktif</dt>
                            </div>
                            <dd class="db-kpi-value">
                                {{ number_format($adminSummary['active_assignments'], 0, ',', '.') }}
                            </dd>
                            <p class="db-kpi-note">Wilayah aktif</p>
                        </div>
                    </dl>

                    <div class="db-authority-card db-bento-side">
                        <div>
                            <p class="db-authority-kicker">Status Kewenangan</p>
                            <p class="db-authority-role">
                                {{ $roles->first()?->name ?? 'Pengguna Aktif' }}
                            </p>
                        </div>
                        <p class="db-authority-foot">
                            <strong style="color: #047857;">{{ number_format($adminSummary['active_accounts'] + $adminSummary['pending_accounts'], 0, ',', '.') }}</strong> total akun tercatat pada basis data desa.
                        </p>
                    </div>
                </div>
            </section>
        @endif

        @forelse ($actionGroups as $group => $items)
            <section class="db-section" aria-labelledby="task-group-{{ \Illuminate\Support\Str::slug($group) }}">
                <div class="db-section-head">
                    <h2 id="task-group-{{ \Illuminate\Support\Str::slug($group) }}" class="db-section-title">{{ $group }}</h2>
                    <span class="db-section-sub">{{ count($items) }} layanan</span>
                </div>

                <ul class="db-cards-grid">
                    @foreach ($items as $item)
                        @php
                            $words = preg_split('/\s+/', trim($item['label']));
                            $initials = count($words) >= 2
                                ? mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1))
                                : mb_strtoupper(mb_substr($item['label'], 0, 2));
                        @endphp
                        <li>
                            <a href="{{ route($item['route']) }}" class="db-service-card focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                                <div>
                                    <div class="db-service-top">
                                        <div style="min-width: 0;">
                                            <h3 class="db-service-title break-words">
                                                {{ $item['label'] }}
                                            </h3>
                                            <p class="db-service-group">{{ $group }}</p>
                                        </div>
                                        <span class="db-monogram" aria-hidden="true">{{ $initials }}</span>
                                    </div>

                                    <p class="db-service-desc">
                                        Akses layanan terintegrasi sesuai peran aktif pada akun Anda.
                                    </p>
                                </div>

                                <div class="db-service-foot">
                                    <span class="db-service-cta">
                                        <span>Buka layanan</span>
                                        <span aria-hidden="true">›</span>
                                    </span>
                                </div>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <div class="db-section db-panel">
                <x-ui.empty-state
                    title="Belum ada halaman layanan untuk peran ini"
                    description="Peran yang tercatat pada akun Anda belum memiliki tujuan aplikasi yang tersedia pada tahap ini. Jika akun Anda memiliki peran lain, tugasnya akan muncul di sini."
                />
            </div>
        @endforelse

        @if ($rolesWithoutPhaseTwoDestinations->isNotEmpty() && $actionGroups->isNotEmpty())
            <div class="db-section db-panel">
                <x-ui.empty-state
                    title="Sebagian peran belum memiliki halaman layanan"
                    description="{{ $rolesWithoutPhaseTwoDestinations->join(', ') }} belum memiliki tujuan aplikasi yang tersedia pada tahap ini."
                />
            </div>
        @endif

        @if ($roleNames->intersect(['RT', 'RW'])->isNotEmpty() || $roleNames->contains('Resident') || $roleNames->contains('Village Admin'))
            <div class="db-section">
                @if ($roleNames->intersect(['RT', 'RW'])->isNotEmpty())
                    <section class="db-panel" aria-labelledby="region-assignments-heading">
                        <div class="db-panel-head">
                            <div>
                                <h2 id="region-assignments-heading" class="db-panel-title">Wilayah tugas</h2>
                                <p class="db-panel-sub">Cakupan berikut berasal dari penugasan aktif pada akun Anda.</p>
                            </div>
                        </div>
                        <div style="margin-top: 1rem;">
                            @if ($assignments->isNotEmpty())
                                <ul class="grid gap-3 sm:grid-cols-2" style="list-style: none; padding: 0; margin: 0;">
                                    @foreach ($assignments as $assignment)
                                        <li style="display: flex; align-items: center; gap: 0.625rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; background-color: #f8fafc; padding: 0.75rem 0.875rem; font-size: 0.75rem;">
                                            <span class="db-dot-emerald" aria-hidden="true"></span>
                                            <span style="font-weight: 500; color: #334155;">{{ $assignment->region->region_type === 'rt' ? $assignment->region->name.' / '.$assignment->region->parent?->name : $assignment->region->name.' · seluruh RT di bawahnya' }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p style="border-radius: 0.75rem; border: 1px solid #e2e8f0; background-color: #f8fafc; padding: 0.875rem 1rem; font-size: 0.75rem; line-height: 1.6; color: #64748b; margin: 0;">Belum ada wilayah yang ditugaskan. Peran RT/RW saja tidak membuka akses ke data warga.</p>
                            @endif
                        </div>
                    </section>
                @endif

                @if ($roleNames->contains('Resident'))
                    <section class="db-panel" aria-labelledby="resident-profile-heading">
                        <h2 id="resident-profile-heading" class="db-panel-title">Profil warga</h2>
                        <div style="margin-top: 0.875rem;">
                            @if ($residentProfile)
                                <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; background-color: #f8fafc; padding: 0.875rem 1rem;">
                                    <div style="min-width: 0;">
                                        <p style="font-size: 0.75rem; font-weight: 500; color: #64748b; margin: 0;">Status wilayah:
                                            <span style="margin-left: 0.375rem; display: inline-block;">
                                                @if ($residentProfile->region_verified_at)
                                                    <x-ui.status-badge status="Terverifikasi" tone="success" />
                                                @else
                                                    <x-ui.status-badge status="Menunggu pemeriksaan" tone="warning" />
                                                @endif
                                            </span>
                                        </p>
                                    </div>
                                    <a href="{{ route('profile.show') }}" class="db-btn-emerald">
                                        <span>Lihat profil</span>
                                        <span aria-hidden="true">›</span>
                                    </a>
                                </div>
                            @else
                                <x-ui.empty-state
                                    id="resident-profile-empty"
                                    title="Profil belum dilengkapi"
                                    description="Isi profil agar wilayah tempat tinggal dapat diperiksa oleh Village Admin."
                                    action="Lengkapi profil"
                                    :href="route('profile.create')"
                                    class="border-0 bg-transparent p-0"
                                />
                            @endif
                        </div>
                    </section>
                @endif

                @if ($roleNames->contains('Village Admin'))
                    <section class="db-panel" aria-labelledby="region-review-heading">
                        <h2 id="region-review-heading" class="db-panel-title">Pemeriksaan wilayah warga</h2>
                        <p class="db-panel-sub">Permintaan yang belum diperiksa tersimpan pada daftar verifikasi.</p>
                        <div style="margin-top: 0.875rem; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; border-radius: 0.75rem; border: 1px solid #e2e8f0; background-color: #f8fafc; padding: 0.875rem 1rem;">
                            <p style="font-size: 0.75rem; color: #64748b; margin: 0;">Menunggu pemeriksaan: <strong style="color: #334155; font-variant-numeric: tabular-nums;">{{ number_format($regionReviewCount, 0, ',', '.') }}</strong> permintaan</p>
                            <a href="{{ route('admin.resident-region-verification.index') }}" class="db-btn-emerald">
                                <span>Buka daftar verifikasi</span>
                                <span aria-hidden="true">›</span>
                            </a>
                        </div>
                    </section>
                @endif
            </div>
        @endif
    </main>
</x-layouts.app>
