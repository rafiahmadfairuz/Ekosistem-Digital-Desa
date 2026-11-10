<x-layouts.app title="Aktivasi akun">
<main id="main" class="mx-auto max-w-6xl px-4 py-10 sm:px-8 sm:py-14">
        <a href="{{ route('dashboard') }}" class="inline-flex min-h-11 items-center text-sm font-medium text-muted underline underline-offset-4 hover:text-ink">Kembali ke beranda</a>
        <div class="mt-5 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div>
                <p class="text-sm font-semibold text-emerald-800">Akses terbatas · Super Admin</p>
                <h1 class="mt-2 text-3xl font-semibold tracking-[-0.035em]">Aktivasi akun warga</h1>
                <p class="mt-2 max-w-2xl text-base leading-7 text-muted">Periksa permintaan yang menunggu, lalu aktifkan akun sesuai prosedur desa. Aktivasi tidak memberikan peran administratif.</p>
                @if (config('mail.default') === 'log')
                    <p class="mt-4 max-w-2xl border-l-2 border-slate-400 bg-slate-50 px-4 py-3 text-sm leading-6 text-muted">Mode email lokal aktif. Setelah memilih “Kirim ulang tautan”, buka <span class="font-medium text-ink">storage/logs/laravel.log</span> untuk mengambil tautan verifikasi.</p>
                @endif
            </div>
        </div>

        @if ($pendingUsers->isEmpty())
            <section class="mt-8 border-y border-slate-300 py-10" aria-labelledby="empty-title">
                <h2 id="empty-title" class="text-xl font-semibold">Tidak ada akun menunggu</h2>
                <p class="mt-2 text-base leading-7 text-muted">Akun warga baru yang menunggu aktivasi akan muncul di halaman ini.</p>
            </section>
        @else
            <div class="mt-8 overflow-x-auto border-y border-slate-300">
                <table class="w-full min-w-[36rem] text-left">
                    <caption class="sr-only">Daftar akun yang menunggu aktivasi</caption>
                    <thead>
                        <tr class="border-b border-slate-300 text-sm text-muted">
                            <th scope="col" class="px-3 py-4 font-semibold">Nama pengguna</th>
                            <th scope="col" class="px-3 py-4 font-semibold">Email</th>
                            <th scope="col" class="px-3 py-4 font-semibold">Status</th>
                            <th scope="col" class="px-3 py-4 text-right font-semibold">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach ($pendingUsers as $pendingUser)
                            <tr>
                                <th scope="row" class="break-all px-3 py-4 text-base font-semibold">{{ $pendingUser->username }}</th>
                                <td class="break-all px-3 py-4 text-sm text-muted">{{ $pendingUser->email ?: 'Tidak diberikan' }}</td>
                                <td class="px-3 py-4 text-sm">Menunggu aktivasi</td>
                                <td class="px-3 py-4 text-right">
                                    @if ($pendingUser->hasVerifiedEmail())
                                        <form method="POST" action="{{ route('admin.users.activate', $pendingUser) }}">
                                            @csrf
                                            <button class="inline-flex min-h-11 items-center justify-center bg-brand px-4 text-sm font-semibold text-ink hover:bg-emerald-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ink">Aktifkan akun</button>
                                        </form>
                                    @elseif ($pendingUser->email)
                                        <div class="flex flex-col items-end gap-2">
                                            <span class="text-sm text-muted">Menunggu verifikasi email</span>
                                            <form method="POST" action="{{ route('admin.users.resend-verification', $pendingUser) }}">
                                                @csrf
                                                <button class="inline-flex min-h-11 items-center justify-center border border-slate-500 px-3 text-sm font-medium hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">Kirim ulang tautan</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-sm text-muted">Email belum tersedia</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-6">{{ $pendingUsers->links() }}</div>
        @endif
    </main>
</x-layouts.app>
