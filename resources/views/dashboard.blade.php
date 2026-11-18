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
            width: 2.375rem;
            height: 2.375rem;
            border-radius: 0.625rem;
            background-color: #ecfdf5;
            border: 1px solid #d1fae5;
            color: #059669;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background-color 0.18s ease, color 0.18s ease, border-color 0.18s ease;
        }
        .db-monogram svg {
            width: 1.15rem;
            height: 1.15rem;
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
                                    <span>{{ \App\Models\Role::displayName($role->name) }}</span>
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
                            Administrator Utama mengelola akses teknis dan tidak otomatis memiliki kewenangan hukum untuk menyetujui atau menandatangani dokumen desa.
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
                                {{ $roles->first() ? \App\Models\Role::displayName($roles->first()->name) : 'Pengguna Aktif' }}
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
                                        <span class="db-monogram" aria-hidden="true">
                                            {{-- 1. Dashboard & Beranda --}}
                                            @if (\Illuminate\Support\Str::contains($item['route'], ['dashboard', 'home', 'overview']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="3" width="7" height="7" rx="1.5" />
                                                    <rect x="14" y="3" width="7" height="7" rx="1.5" />
                                                    <rect x="14" y="14" width="7" height="7" rx="1.5" />
                                                    <rect x="3" y="14" width="7" height="7" rx="1.5" />
                                                </svg>

                                            {{-- 2. Notifikasi --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['notifications']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                                                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                                                </svg>

                                            {{-- 3. Keranjang Belanja (Marketplace) --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace.cart', 'cart', 'checkout']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="8" cy="21" r="1" />
                                                    <circle cx="19" cy="21" r="1" />
                                                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                                                </svg>

                                            {{-- 4. Pesanan Pembeli (Marketplace Orders) --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace.orders']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                                                    <path d="M3 6h18" />
                                                    <path d="M16 10a4 4 0 0 1-8 0" />
                                                </svg>

                                            {{-- 5. Pendaftaran Penjual (Seller Application) --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace.seller.application']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M3 9l2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.8 1.1L21 9" />
                                                    <path d="M3 9h18v2a3 3 0 0 1-6 0 3 3 0 0 1-6 0 3 3 0 0 1-6 0V9Z" />
                                                    <path d="M5 12v7a2 2 0 0 0 2 2h5" />
                                                    <path d="M19 16v6" />
                                                    <path d="M16 19h6" />
                                                </svg>

                                            {{-- 6. Produk Penjual (Seller Products) --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace.seller.products']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="m7.5 4.27 9 5.15" />
                                                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                                                    <path d="m3.3 7 8.7 5 8.7-5" />
                                                    <path d="M12 22V12" />
                                                </svg>

                                            {{-- 7. Pesanan Masuk Penjual & Pengiriman (Seller Orders) --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace.seller.orders']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2" />
                                                    <path d="M15 18H9" />
                                                    <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14" />
                                                    <circle cx="17" cy="18" r="2" />
                                                    <circle cx="7" cy="18" r="2" />
                                                </svg>

                                            {{-- 8. Rekening Pencairan Penjual (Payout Account) --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace.seller.payout-account']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1" />
                                                    <path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4" />
                                                </svg>

                                            {{-- 9. Manajemen Penjual (Verifikasi Seller) --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace.management.sellers']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                                    <circle cx="9" cy="7" r="4" />
                                                    <polyline points="16 11 18 13 22 9" />
                                                </svg>

                                            {{-- 10. Manajemen Rekening Payout & Akun Xendit Seller --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace.management.payout-accounts', 'xendit-seller-accounts']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <line x1="3" y1="22" x2="21" y2="22" />
                                                    <line x1="6" y1="18" x2="6" y2="11" />
                                                    <line x1="10" y1="18" x2="10" y2="11" />
                                                    <line x1="14" y1="18" x2="14" y2="11" />
                                                    <line x1="18" y1="18" x2="18" y2="11" />
                                                    <polygon points="12 2 20 7 4 7" />
                                                </svg>

                                            {{-- 11. Manajemen Kategori Produk --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace.management.categories']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z" />
                                                    <circle cx="7.5" cy="7.5" r=".5" fill="currentColor" />
                                                </svg>

                                            {{-- 12. Moderasi Produk Marketplace --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace.management.products']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
                                                    <path d="m9 12 2 2 4-4" />
                                                </svg>

                                            {{-- 13. Etalase Utama Marketplace Desa --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace', 'market', 'shop', 'store']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7" />
                                                    <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
                                                    <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4" />
                                                    <path d="M2 7h20" />
                                                    <path d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7" />
                                                </svg>

                                            {{-- 14. Laporan Pendampingan BUMDes --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['bumdes.assistance.reports']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                                                    <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                                                    <path d="M8 18v-2" />
                                                    <path d="M12 18v-4" />
                                                    <path d="M16 18v-6" />
                                                </svg>

                                            {{-- 15. Layanan Pendampingan BUMDes (Assisted Service) --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['bumdes.assistance']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3" />
                                                </svg>

                                            {{-- 16. Staf & Pengurus BUMDes --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['bumdes.staff']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect width="20" height="14" x="2" y="7" rx="2" ry="2" />
                                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                                                </svg>

                                            {{-- 17. Laporan Keuangan & Usaha BUMDes --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['bumdes.reports', 'report', 'analytic', 'finance']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="22 7 13.5 15.5 8.5 10.5 2 17" />
                                                    <polyline points="16 7 22 7 22 13" />
                                                </svg>

                                            {{-- 18. Jenis & Template Persuratan --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['correspondence.types']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z" />
                                                    <path d="M8 10h8" />
                                                    <path d="M8 14h5" />
                                                </svg>

                                            {{-- 19. Layanan Persuratan Desa --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['correspondence']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect width="20" height="16" x="2" y="4" rx="2" />
                                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                                                </svg>

                                            {{-- 20. Manajemen Layanan Internet & WiFi --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['services.management']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect width="20" height="8" x="2" y="2" rx="2" ry="2" />
                                                    <rect width="20" height="8" x="2" y="14" rx="2" ry="2" />
                                                    <line x1="6" x2="6.01" y1="6" y2="6" />
                                                    <line x1="6" x2="6.01" y1="18" y2="18" />
                                                </svg>

                                            {{-- 21. Portal Layanan Internet & WiFi Warga --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['services']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M12 20h.01" />
                                                    <path d="M2 8.82a15 15 0 0 1 20 0" />
                                                    <path d="M5 12.859a10 10 0 0 1 14 0" />
                                                    <path d="M8.5 16.429a5 5 0 0 1 7 0" />
                                                </svg>

                                            {{-- 22. Data Warga Wilayah (Petugas RT/RW) --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['officer.residents']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                                    <circle cx="9" cy="7" r="4" />
                                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                                </svg>

                                            {{-- 23. Profil Warga / Akun Pribadi --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['profile']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <circle cx="12" cy="10" r="3" />
                                                    <path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662" />
                                                </svg>

                                            {{-- 24. Aktivasi Pengguna Baru (Admin Pending Users) --}}
                                            @elseif ($item['route'] === 'admin.users.index')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                                    <circle cx="9" cy="7" r="4" />
                                                    <line x1="19" x2="19" y1="8" y2="14" />
                                                    <line x1="22" x2="16" y1="11" y2="11" />
                                                </svg>

                                            {{-- 25. Pergantian Kepala Desa --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['village-head']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z" />
                                                    <path d="M5 21h14" />
                                                </svg>

                                            {{-- 26. Manajemen Pengguna & Hak Akses --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['admin.users']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" />
                                                    <circle cx="12" cy="10" r="2.5" />
                                                    <path d="M8.5 16a4 4 0 0 1 7 0" />
                                                </svg>

                                            {{-- 27. Penugasan Wilayah RT/RW --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['region-assignments']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0" />
                                                    <circle cx="12" cy="10" r="3" />
                                                </svg>

                                            {{-- 28. Verifikasi Wilayah Warga --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['resident-region-verification']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z" />
                                                    <path d="m9 12 2 2 4-4" />
                                                </svg>

                                            {{-- 29. Katalog Wilayah Desa / RW / RT --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['admin.regions']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21" />
                                                    <line x1="9" x2="9" y1="3" y2="18" />
                                                    <line x1="15" x2="15" y1="6" y2="21" />
                                                </svg>

                                            {{-- 30. Pengaturan Profil Desa --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['village-settings']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z" />
                                                    <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2" />
                                                    <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2" />
                                                    <path d="M10 6h4" />
                                                    <path d="M10 10h4" />
                                                    <path d="M10 14h4" />
                                                    <path d="M10 18h4" />
                                                </svg>

                                            {{-- 31. Log Audit Sistem --}}
                                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['audit-logs']))
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                                                    <path d="M3 3v5h5" />
                                                    <path d="M12 7v5l4 2" />
                                                </svg>

                                            {{-- 32. Default Fallback --}}
                                            @else
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="3" />
                                                    <path d="M12 3v3" />
                                                    <path d="M12 18v3" />
                                                    <path d="m4.93 4.93 2.12 2.12" />
                                                    <path d="m16.95 16.95 2.12 2.12" />
                                                    <path d="M3 12h3" />
                                                    <path d="M18 12h3" />
                                                    <path d="m4.93 19.07 2.12-2.12" />
                                                    <path d="m16.95 7.05 2.12-2.12" />
                                                </svg>
                                            @endif
                                        </span>
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
                                    description="Isi profil agar wilayah tempat tinggal dapat diperiksa oleh Admin Desa."
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
