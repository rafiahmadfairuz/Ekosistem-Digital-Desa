<x-layouts.app title="Akun Xendit penjual">
    <style>
        .xn-shell {
            max-width: 68rem;
            margin: 0 auto;
            padding: 1.75rem 1.25rem 3.5rem;
        }
        @media (min-width: 640px) {
            .xn-shell {
                padding: 2.25rem 2rem 4rem;
            }
        }
        .xn-breadcrumb {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.45rem;
            font-size: 0.8125rem;
            color: #64748b;
            margin: 0;
        }
        .xn-breadcrumb-link {
            font-weight: 500;
            color: #64748b;
            text-decoration: none;
            transition: color 0.16s ease;
        }
        .xn-breadcrumb-link:hover {
            color: #059669;
        }
        .xn-breadcrumb-sep {
            color: #cbd5e1;
            user-select: none;
        }
        .xn-breadcrumb-current {
            font-weight: 600;
            color: #334155;
        }
        .xn-header {
            margin-top: 1rem;
        }
        .xn-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #059669;
            margin: 0;
        }
        .xn-kicker-dot {
            width: 0.4rem;
            height: 0.4rem;
            border-radius: 9999px;
            background-color: #10b981;
        }
        .xn-title {
            margin: 0.375rem 0 0;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1.25;
            color: #1e293b;
        }
        @media (min-width: 640px) {
            .xn-title {
                font-size: 1.625rem;
            }
        }
        .xn-desc {
            margin: 0.5rem 0 0;
            max-width: 44rem;
            font-size: 0.875rem;
            line-height: 1.6;
            color: #64748b;
        }
        .xn-warning-box {
            margin-top: 1.375rem;
            display: flex;
            align-items: flex-start;
            gap: 0.875rem;
            border-radius: 0.875rem;
            border: 1px solid #fde68a;
            background-color: #fffbeb;
            padding: 1rem 1.125rem;
            box-shadow: 0 1px 4px -1px rgba(180, 83, 9, 0.05);
        }
        .xn-warning-badge {
            flex-shrink: 0;
            border-radius: 0.375rem;
            background-color: #ffffff;
            border: 1px solid #fcd34d;
            padding: 0.15rem 0.5rem;
            font-size: 0.6875rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            color: #92400e;
            margin-top: 0.1rem;
        }
        .xn-warning-text {
            margin: 0;
            font-size: 0.8125rem;
            line-height: 1.6;
            color: #78350f;
        }
        .xn-list {
            margin-top: 1.75rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        .xn-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 1.375rem 1.5rem;
            box-shadow: 0 2px 10px -3px rgba(15, 23, 42, 0.04);
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
            transition: border-color 0.16s ease, box-shadow 0.16s ease;
        }
        .xn-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 6px 18px -6px rgba(15, 23, 42, 0.06);
        }
        @media (min-width: 1024px) {
            .xn-card {
                grid-template-columns: minmax(14rem, 1fr) minmax(18rem, 1.25fr);
                align-items: center;
                gap: 2rem;
            }
        }
        .xn-seller-head {
            display: flex;
            align-items: flex-start;
            gap: 0.875rem;
        }
        .xn-avatar {
            width: 2.5rem;
            height: 2.5rem;
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
        .xn-business-name {
            margin: 0;
            font-size: 0.9375rem;
            font-weight: 700;
            line-height: 1.35;
            color: #1e293b;
            word-break: break-word;
        }
        .xn-seller-meta {
            margin: 0.2rem 0 0;
            font-size: 0.75rem;
            color: #64748b;
        }
        .xn-status-row {
            margin-top: 0.625rem;
        }
        .xn-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            border-radius: 9999px;
            padding: 0.2rem 0.65rem;
            font-size: 0.6875rem;
            font-weight: 600;
        }
        .xn-status-connected {
            background-color: #ecfdf5;
            border: 1px solid #d1fae5;
            color: #047857;
        }
        .xn-status-connected .xn-status-dot {
            background-color: #10b981;
        }
        .xn-status-unlinked {
            background-color: #fefce8;
            border: 1px solid #fde68a;
            color: #92400e;
        }
        .xn-status-unlinked .xn-status-dot {
            background-color: #f59e0b;
        }
        .xn-status-dot {
            width: 0.375rem;
            height: 0.375rem;
            border-radius: 9999px;
        }
        .xn-form-wrap {
            padding-top: 1.125rem;
            border-top: 1px solid #f1f5f9;
        }
        @media (min-width: 1024px) {
            .xn-form-wrap {
                padding-top: 0;
                padding-left: 1.75rem;
                border-top: none;
                border-left: 1px solid #f1f5f9;
            }
        }
        .xn-form {
            display: flex;
            flex-direction: column;
            gap: 0.625rem;
            margin: 0;
        }
        @media (min-width: 640px) {
            .xn-form {
                flex-direction: row;
                align-items: flex-end;
                gap: 0.75rem;
            }
        }
        .xn-label {
            display: block;
            min-width: 0;
            flex: 1;
            font-size: 0.75rem;
            font-weight: 600;
            color: #475569;
        }
        .xn-input {
            margin-top: 0.375rem;
            min-height: 2.5rem;
            width: 100%;
            border-radius: 0.625rem;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            padding: 0 0.75rem;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.8125rem;
            color: #1e293b;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }
        .xn-input::placeholder {
            font-family: inherit;
            color: #94a3b8;
        }
        .xn-input:focus {
            outline: none;
            border-color: #059669;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.12);
        }
        .xn-btn-save {
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
        .xn-btn-save:hover {
            background-color: #047857;
            box-shadow: 0 4px 12px -3px rgba(5, 150, 105, 0.3);
        }
        .xn-empty-wrap {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            padding: 2rem 1.5rem;
            box-shadow: 0 2px 10px -3px rgba(15, 23, 42, 0.04);
        }
        .xn-pagination {
            margin-top: 1.5rem;
        }
    </style>

    <main id="main" class="xn-shell">
        <nav aria-label="Jejak navigasi" class="xn-breadcrumb">
            <a href="{{ route('admin.users.manage') }}" class="xn-breadcrumb-link">Akun dan akses</a>
            <span class="xn-breadcrumb-sep" aria-hidden="true">/</span>
            <span class="xn-breadcrumb-current" aria-current="page">Akun Xendit penjual</span>
        </nav>

        <div class="xn-header">
            <p class="xn-kicker">
                <span class="xn-kicker-dot" aria-hidden="true"></span>
                <span>Pengaturan Pencairan · Super Admin</span>
            </p>
            <h1 class="xn-title">Akun Xendit penjual</h1>
            <p class="xn-desc">Hubungkan penjual yang disetujui ke Business ID sub-account XenPlatform. Hanya Super Admin dapat mengubah tujuan pencairan.</p>
        </div>

        <div class="xn-warning-box" role="region" aria-label="Peringatan keamanan konfigurasi Xendit">
            <span class="xn-warning-badge">Penting</span>
            <p class="xn-warning-text">
                Masukkan Business ID dari sub-account XenPlatform yang sudah diverifikasi dan terhubung ke platform ini. ID ini bukan API key. Jangan masukkan secret key. Pembayaran tetap gagal tertutup jika XenPlatform, izin API, atau sub-account belum aktif.
            </p>
        </div>


{{-- Status konfigurasi Xendit --}}
<section class="space-y-4 rounded-card border border-line bg-paper p-5 mt-6" aria-labelledby="xendit-status-heading">
    <h2 id="xendit-status-heading" class="font-semibold">Status konfigurasi</h2>

    <dl class="grid gap-4 sm:grid-cols-3">
        <div>
            <dt class="text-sm text-muted">Mode</dt>
            <dd class="mt-1 font-semibold">{{ $mode === 'test' ? 'Test' : ($mode === 'live' ? 'Live' : 'Simulasi') }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted">Kredensial API dan webhook</dt>
            <dd class="mt-1 font-semibold">{{ $credentialsConfigured ? 'Terpasang' : 'Belum lengkap' }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted">Checkout marketplace</dt>
            <dd class="mt-1 font-semibold">{{ $checkoutReady ? 'Siap' : 'Belum siap' }}</dd>
        </div>
        <div>
            <dt class="text-sm text-muted">Xendit Payouts</dt>
            <dd class="mt-1 font-semibold">{{ $payoutsReady ? 'Aktif' : 'Belum aktif' }}</dd>
        </div>
    </dl>
</section>

<x-ui.alert type="warning">
    Pembayaran masuk ke saldo akun merchant Xendit. Saldo penjual di aplikasi hanya bertambah berdasarkan pesanan yang selesai dan pembayaran terkonfirmasi. Payout Xendit baru dikirim ketika penjual mengajukan pencairan; sistem menahan saldo saat permintaan dibuat. Payout tetap dapat gagal, ditolak, atau menunggu pemeriksaan. Jangan aktifkan mode live sebelum akun memiliki izin API Money Out dan pemilik menyetujui model bisnis serta sumber dana payout.
</x-ui.alert>

{{-- Konfigurasi server --}}
<section class="rounded-card border border-line bg-paper p-5" aria-labelledby="xendit-setup-heading">
    <h2 id="xendit-setup-heading" class="font-semibold">Konfigurasi server</h2>
    <p class="mt-2 text-sm leading-6 text-muted">
        Checkout memerlukan <code>MARKETPLACE_PAYMENT_MODE</code>,
        <code>XENDIT_SECRET_KEY</code>, dan <code>XENDIT_WEBHOOK_TOKEN</code>.
        Pencairan memerlukan <code>XENDIT_PAYOUTS_ENABLED=true</code>,
        <code>XENDIT_PAYOUT_WEBHOOK_TOKEN</code>, izin Money Out,
        dan worker antrean aktif. Daftarkan kedua webhook pada dashboard Xendit.
        Tidak ada credential yang ditampilkan atau disimpan pada halaman ini.
    </p>
</section>


        <section class="xn-list" aria-label="Pemetaan sub-account penjual">
            @forelse($sellers as $seller)
                <article class="xn-card">
                    <div class="xn-seller-head min-w-0">
                        <span class="xn-avatar" aria-hidden="true">{{ mb_substr($seller->business_name, 0, 1) }}</span>
                        <div class="min-w-0">
                            <h2 class="xn-business-name">{{ $seller->business_name }}</h2>
                            <p class="xn-seller-meta">{{ $seller->user->username }} · Disetujui</p>
                            <div class="xn-status-row">
                                @if($seller->xendit_account_id)
                                    <span class="xn-status-pill xn-status-connected">
                                        <span class="xn-status-dot" aria-hidden="true"></span>
                                        <span>Sub-account terhubung</span>
                                    </span>
                                @else
                                    <span class="xn-status-pill xn-status-unlinked">
                                        <span class="xn-status-dot" aria-hidden="true"></span>
                                        <span>Belum terhubung</span>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="xn-form-wrap min-w-0">
                        <form method="POST" action="{{ route('admin.marketplace.xendit-seller-accounts.update', $seller) }}" class="xn-form">
                            @csrf
                            @method('PUT')
                            <label class="xn-label" for="xendit-account-{{ $seller->id }}">
                                <span>Business ID sub-account</span>
                                <input id="xendit-account-{{ $seller->id }}" name="xendit_account_id" value="{{ old('xendit_account_id', $seller->xendit_account_id) }}" maxlength="80" pattern="[A-Za-z0-9_-]+" autocomplete="off" spellcheck="false" class="xn-input" placeholder="Contoh: sub-account Business ID…">
                            </label>
                            <button type="submit" class="xn-btn-save focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">Simpan tujuan</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="xn-empty-wrap">
                    <x-ui.empty-state title="Belum ada penjual disetujui" description="Penjual akan muncul di sini setelah status pengajuannya disetujui." />
                </div>
            @endforelse
        </section>

        <div class="xn-pagination">{{ $sellers->links() }}</div>

    </main>
</x-layouts.app>
