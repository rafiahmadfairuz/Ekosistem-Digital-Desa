<x-layouts.app title="Aktivasi akun">
    <style>
        .act-shell {
            max-width: 76rem;
            margin: 0 auto;
            padding: 1.75rem 1.25rem 3.5rem;
        }
        @media (min-width: 640px) {
            .act-shell {
                padding: 2.25rem 2rem 4rem;
            }
        }
        .act-back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8125rem;
            font-weight: 500;
            color: #64748b;
            text-decoration: none;
            padding: 0.375rem 0;
            transition: color 0.16s ease;
        }
        .act-back-link:hover {
            color: #059669;
        }
        .act-header {
            margin-top: 0.875rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        @media (min-width: 768px) {
            .act-header {
                flex-direction: row;
                align-items: flex-end;
                justify-content: space-between;
            }
        }
        .act-kicker {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.75rem;
            font-weight: 600;
            color: #059669;
            margin: 0;
        }
        .act-kicker-dot {
            width: 0.4rem;
            height: 0.4rem;
            border-radius: 9999px;
            background-color: #10b981;
        }
        .act-title {
            margin: 0.375rem 0 0;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            line-height: 1.25;
            color: #1e293b;
        }
        @media (min-width: 640px) {
            .act-title {
                font-size: 1.625rem;
            }
        }
        .act-desc {
            margin: 0.5rem 0 0;
            max-width: 40rem;
            font-size: 0.875rem;
            line-height: 1.6;
            color: #64748b;
        }
        .act-notice {
            margin-top: 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            max-width: 44rem;
            border-radius: 0.875rem;
            border: 1px solid #d1fae5;
            background-color: #ecfdf5;
            padding: 0.875rem 1rem;
            font-size: 0.8125rem;
            line-height: 1.55;
            color: #475569;
        }
        .act-notice-badge {
            flex-shrink: 0;
            border-radius: 0.375rem;
            background-color: #ffffff;
            border: 1px solid #a7f3d0;
            padding: 0.125rem 0.5rem;
            font-size: 0.6875rem;
            font-weight: 600;
            color: #047857;
            margin-top: 0.1rem;
        }
        .act-notice code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 0.75rem;
            font-weight: 600;
            color: #065f46;
            background-color: rgba(255, 255, 255, 0.85);
            padding: 0.1rem 0.4rem;
            border-radius: 0.375rem;
            border: 1px solid #d1fae5;
        }
        .act-card {
            margin-top: 1.75rem;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 1rem;
            box-shadow: 0 2px 10px -3px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }
        .act-empty {
            padding: 3.25rem 1.5rem;
            text-align: center;
        }
        .act-empty-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 9999px;
            background-color: #ecfdf5;
            border: 1px solid #d1fae5;
            color: #059669;
            font-size: 1rem;
            font-weight: 700;
            margin-bottom: 0.875rem;
        }
        .act-empty-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
            color: #334155;
        }
        .act-empty-desc {
            margin: 0.375rem auto 0;
            max-width: 26rem;
            font-size: 0.8125rem;
            line-height: 1.6;
            color: #94a3b8;
        }
        .act-table-wrap {
            overflow-x: auto;
        }
        .act-table {
            width: 100%;
            min-width: 38rem;
            border-collapse: collapse;
            text-align: left;
        }
        .act-table thead tr {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .act-table th {
            padding: 0.875rem 1.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            color: #64748b;
        }
        .act-table th.act-col-right {
            text-align: right;
        }
        .act-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background-color 0.14s ease;
        }
        .act-table tbody tr:last-child {
            border-bottom: none;
        }
        .act-table tbody tr:hover {
            background-color: #f8fafc;
        }
        .act-table td,
        .act-table tbody th {
            padding: 1rem 1.25rem;
            vertical-align: middle;
        }
        .act-user-cell {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-weight: 400;
        }
        .act-avatar {
            width: 2.125rem;
            height: 2.125rem;
            border-radius: 0.625rem;
            background-color: #ecfdf5;
            border: 1px solid #d1fae5;
            color: #059669;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .act-username {
            font-size: 0.875rem;
            font-weight: 600;
            color: #334155;
            word-break: break-all;
        }
        .act-email {
            font-size: 0.8125rem;
            color: #64748b;
            word-break: break-all;
        }
        .act-email-empty {
            font-size: 0.8125rem;
            font-style: italic;
            color: #94a3b8;
        }
        .act-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            border-radius: 9999px;
            background-color: #fefce8;
            border: 1px solid #fde68a;
            padding: 0.25rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 500;
            color: #92400e;
            white-space: nowrap;
        }
        .act-status-dot {
            width: 0.375rem;
            height: 0.375rem;
            border-radius: 9999px;
            background-color: #f59e0b;
        }
        .act-action-cell {
            text-align: right;
        }
        .act-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.375rem;
            padding: 0 1rem;
            border-radius: 0.625rem;
            border: none;
            background-color: #059669;
            color: #ffffff;
            font-size: 0.8125rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.16s ease, box-shadow 0.16s ease;
        }
        .act-btn-primary:hover {
            background-color: #047857;
            box-shadow: 0 4px 12px -3px rgba(5, 150, 105, 0.35);
        }
        .act-action-stack {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.45rem;
        }
        @media (min-width: 640px) {
            .act-action-stack {
                flex-direction: row;
                align-items: center;
                justify-content: flex-end;
                gap: 0.75rem;
            }
        }
        .act-substatus {
            font-size: 0.75rem;
            color: #64748b;
        }
        .act-substatus-muted {
            font-size: 0.75rem;
            color: #94a3b8;
        }
        .act-btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.25rem;
            padding: 0 0.875rem;
            border-radius: 0.625rem;
            border: 1px solid #cbd5e1;
            background-color: #ffffff;
            color: #475569;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: border-color 0.16s ease, background-color 0.16s ease, color 0.16s ease;
        }
        .act-btn-secondary:hover {
            border-color: #6ee7b7;
            background-color: #ecfdf5;
            color: #047857;
        }
        .act-pagination {
            margin-top: 1.5rem;
        }
    </style>

    <main id="main" class="act-shell">
        <a href="{{ route('dashboard') }}" class="act-back-link">
            <span aria-hidden="true">←</span>
            <span>Kembali ke beranda</span>
        </a>

        <div class="act-header">
            <div>
                <p class="act-kicker">
                    <span class="act-kicker-dot" aria-hidden="true"></span>
                    <span>Akses terbatas · Super Admin</span>
                </p>
                <h1 class="act-title">Aktivasi akun warga</h1>
                <p class="act-desc">Periksa permintaan yang menunggu, lalu aktifkan akun sesuai prosedur desa. Aktivasi tidak memberikan peran administratif.</p>

                @if (config('mail.default') === 'log')
                    <div class="act-notice">
                        <span class="act-notice-badge">Info Log</span>
                        <p style="margin: 0;">Mode email lokal aktif. Setelah memilih “Kirim ulang tautan”, buka <code>storage/logs/laravel.log</code> untuk mengambil tautan verifikasi.</p>
                    </div>
                @endif
            </div>
        </div>

        @if ($pendingUsers->isEmpty())
            <section class="act-card act-empty" aria-labelledby="empty-title">
                <div class="act-empty-pill" aria-hidden="true">✓</div>
                <h2 id="empty-title" class="act-empty-title">Tidak ada akun menunggu</h2>
                <p class="act-empty-desc">Akun warga baru yang menunggu aktivasi akan muncul di halaman ini.</p>
            </section>
        @else
            <div class="act-card">
                <div class="act-table-wrap">
                    <table class="act-table">
                        <caption class="sr-only">Daftar akun yang menunggu aktivasi</caption>
                        <thead>
                            <tr>
                                <th scope="col">Nama pengguna</th>
                                <th scope="col">Email</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="act-col-right">Tindakan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($pendingUsers as $pendingUser)
                                <tr>
                                    <th scope="row">
                                        <div class="act-user-cell">
                                            <span class="act-avatar" aria-hidden="true">{{ mb_substr($pendingUser->username, 0, 1) }}</span>
                                            <span class="act-username">{{ $pendingUser->username }}</span>
                                        </div>
                                    </th>
                                    <td>
                                        @if ($pendingUser->email)
                                            <span class="act-email">{{ $pendingUser->email }}</span>
                                        @else
                                            <span class="act-email-empty">Tidak diberikan</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="act-status-badge">
                                            <span class="act-status-dot" aria-hidden="true"></span>
                                            <span>Menunggu aktivasi</span>
                                        </span>
                                    </td>
                                    <td class="act-action-cell">
                                        @if ($pendingUser->hasVerifiedEmail())
                                            <form method="POST" action="{{ route('admin.users.activate', $pendingUser) }}" style="margin: 0;">
                                                @csrf
                                                <button class="act-btn-primary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">Aktifkan akun</button>
                                            </form>
                                        @elseif ($pendingUser->email)
                                            <div class="act-action-stack">
                                                <span class="act-substatus">Menunggu verifikasi email</span>
                                                <form method="POST" action="{{ route('admin.users.resend-verification', $pendingUser) }}" style="margin: 0;">
                                                    @csrf
                                                    <button class="act-btn-secondary focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">Kirim ulang tautan</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="act-substatus-muted">Email belum tersedia</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="act-pagination">{{ $pendingUsers->links() }}</div>
        @endif
    </main>
</x-layouts.app>
