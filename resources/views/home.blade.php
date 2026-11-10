<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0A192F">
    <meta name="description" content="Portal akun untuk memulai akses layanan digital desa.">
    <title>Desa Digital · Portal layanan warga</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-canvas text-ink antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-20 focus:bg-paper focus:px-4 focus:py-3">Lewati ke konten</a>
    <main id="main">
        <section class="relative flex min-h-[100svh] flex-col overflow-hidden bg-ink px-4 pb-10 pt-5 text-paper sm:px-8 sm:pt-7" aria-labelledby="home-title">
            <header class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center gap-3 font-semibold tracking-tight text-paper" aria-label="Desa Digital, beranda">
                    <span class="grid size-9 place-items-center bg-brand text-sm font-bold text-ink" aria-hidden="true">D</span>
                    <span>Desa Digital</span>
                </a>
                <nav aria-label="Navigasi utama" class="flex items-center gap-2 sm:gap-6">
                    <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center px-3 text-sm font-medium text-slate-200 underline-offset-4 hover:text-white hover:underline">Masuk</a>
                </nav>
            </header>

            <div class="mx-auto grid w-full max-w-7xl flex-1 items-center gap-8 py-10 lg:grid-cols-[0.8fr_1.2fr] lg:gap-16 lg:py-14">
                <div class="order-1 max-w-xl pb-4 lg:order-1 lg:pb-0">
                    <p class="text-sm font-medium text-emerald-300">Portal layanan warga</p>
                    <h1 id="home-title" class="mt-4 max-w-[12ch] text-balance text-4xl font-semibold leading-[1.05] tracking-[-0.04em] text-white sm:text-5xl lg:text-6xl">Mulai dari satu akun.</h1>
                    <p class="mt-5 max-w-lg text-base leading-7 text-slate-200 sm:text-lg">Buat akun untuk memulai akses layanan digital desa. Peran dan wilayah tugas menentukan akses yang diberikan.</p>
                    <div class="mt-8 flex flex-wrap items-center gap-x-5 gap-y-3">
                    <a href="{{ route('register') }}" class="inline-flex min-h-12 items-center justify-center bg-brand px-6 text-base font-semibold text-ink hover:bg-emerald-400">Daftar akun warga</a>
                        <a href="#cara-akses" class="inline-flex min-h-11 items-center text-sm font-medium text-white underline decoration-slate-500 underline-offset-4 hover:decoration-white">Lihat cara akses</a>
                    </div>
                    <p class="mt-5 max-w-md text-sm leading-6 text-slate-300">Akun baru berstatus menunggu aktivasi. Pendaftaran tidak memberikan kewenangan administratif.</p>
                </div>

                <div class="order-2 flex justify-center lg:order-2 lg:justify-end">
                    <div class="relative w-full max-w-[30rem] lg:mr-[5vw]">
                        <div class="absolute -right-2 -top-2 z-0 h-full w-full bg-brand" aria-hidden="true"></div>
                        <article class="relative z-10 bg-paper px-6 py-8 text-ink sm:px-10 sm:py-11" aria-label="Kartu informasi pendaftaran akun">
                            <div class="flex items-center justify-between border-b border-slate-300 pb-4 text-sm">
                                <span class="font-semibold tracking-wide">DESA DIGITAL</span>
                                <span class="text-muted">AKUN WARGA</span>
                            </div>
                            <p class="mt-8 text-sm font-medium text-emerald-800">Pendaftaran dimulai di sini</p>
                            <h2 class="mt-3 max-w-[10ch] text-4xl font-semibold leading-[1.05] tracking-[-0.04em] sm:text-5xl">Satu identitas. Akses terarah.</h2>
                            <p class="mt-5 max-w-sm text-base leading-7 text-muted">Daftar dengan nama pengguna dan kata sandi. Setelah akun diaktifkan, Anda dapat masuk menggunakan nama pengguna atau email.</p>
                            <div class="mt-8 border-l-2 border-brand pl-4 text-sm leading-6 text-muted">
                                <p><span class="font-semibold text-ink">Status awal:</span> menunggu aktivasi.</p>
                                <p class="mt-1"><span class="font-semibold text-ink">Peran awal:</span> Warga.</p>
                            </div>
                        </article>
                    </div>
                </div>
            </div>

            <div class="mx-auto flex w-full max-w-7xl items-center justify-between border-t border-slate-700 pt-4 text-xs text-slate-300 sm:text-sm">
                <span>Portal digital satu desa</span>
                <a href="#cara-akses" class="inline-flex min-h-11 items-center gap-2 underline underline-offset-4 hover:text-white">
                    Cara akses <span aria-hidden="true">↓</span>
                </a>
            </div>
        </section>

        <section id="cara-akses" class="bg-canvas px-4 py-20 sm:px-8 sm:py-28" aria-labelledby="access-title">
            <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:gap-20">
                <div>
                    <p class="text-sm font-semibold text-emerald-800">Akun · Peran · Wilayah</p>
                    <h2 id="access-title" class="mt-4 max-w-[12ch] text-3xl font-semibold leading-tight tracking-[-0.035em] sm:text-4xl">Akses mengikuti kebutuhan.</h2>
                    <p class="mt-5 max-w-md text-base leading-7 text-muted">Satu akun dapat memiliki lebih dari satu peran. Petugas wilayah hanya memperoleh akses sesuai penugasan wilayahnya.</p>
                </div>
                <ol class="divide-y divide-slate-300 border-y border-slate-300" aria-label="Tahapan akses akun">
                    <li class="grid gap-2 py-6 sm:grid-cols-[minmax(9rem,0.7fr)_1fr] sm:gap-8 sm:py-7">
                        <h3 class="text-xl font-semibold">Daftar akun</h3>
                        <p class="text-base leading-7 text-muted">Pendaftar mendapat peran Warga. Pilihan peran administratif tidak tersedia di formulir publik.</p>
                    </li>
                    <li class="grid gap-2 py-6 sm:grid-cols-[minmax(9rem,0.7fr)_1fr] sm:gap-8 sm:py-7">
                        <h3 class="text-xl font-semibold">Tunggu aktivasi</h3>
                        <p class="text-base leading-7 text-muted">Akun baru menunggu aktivasi sebelum dapat digunakan untuk masuk.</p>
                    </li>
                    <li class="grid gap-2 py-6 sm:grid-cols-[minmax(9rem,0.7fr)_1fr] sm:gap-8 sm:py-7">
                        <h3 class="text-xl font-semibold">Masuk dengan aman</h3>
                        <p class="text-base leading-7 text-muted">Gunakan nama pengguna atau email dan kata sandi yang Anda buat saat mendaftar.</p>
                    </li>
                </ol>
            </div>
        </section>

        <section class="bg-paper px-4 py-16 sm:px-8 sm:py-20" aria-labelledby="account-access-title">
            <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-2 lg:items-start lg:gap-16">
                <h2 id="account-access-title" class="text-3xl font-semibold tracking-[-0.035em] sm:text-4xl">Akun membuka akses.</h2>
                <p class="max-w-2xl text-base leading-7 text-muted sm:text-lg">Akun mengidentifikasi pengguna. Peran menentukan jenis akses, sementara penugasan wilayah membatasi ruang kerja petugas RT dan RW. Peran teknis Super Admin tidak otomatis memiliki kewenangan persetujuan atau tanda tangan desa.</p>
            </div>
        </section>

        <section class="bg-brand px-4 py-16 text-ink sm:px-8 sm:py-20" aria-labelledby="register-callout-title">
            <div class="mx-auto flex max-w-7xl flex-col items-start justify-between gap-8 sm:flex-row sm:items-center">
                <div>
                    <h2 id="register-callout-title" class="max-w-[16ch] text-3xl font-semibold leading-tight tracking-[-0.035em] sm:text-4xl">Siap membuat akun?</h2>
                    <p class="mt-3 max-w-xl text-base leading-7 text-ink">Isi data akun Anda. Status aktivasi akan ditampilkan setelah pendaftaran.</p>
                </div>
                <a href="{{ route('register') }}" class="inline-flex min-h-12 shrink-0 items-center justify-center bg-ink px-6 text-base font-semibold text-white hover:bg-slate-800">Daftar akun warga</a>
            </div>
        </section>
    </main>

    <footer class="bg-ink px-4 py-8 text-white sm:px-8">
        <div class="mx-auto flex max-w-7xl flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center font-semibold">Desa Digital</a>
            <nav aria-label="Tautan footer" class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-slate-200">
                <a class="inline-flex min-h-11 items-center underline underline-offset-4 hover:text-white" href="{{ route('register') }}">Daftar</a>
                <a class="inline-flex min-h-11 items-center underline underline-offset-4 hover:text-white" href="{{ route('login') }}">Masuk</a>
            </nav>
            <p class="text-sm text-slate-300">Portal layanan digital warga</p>
        </div>
    </footer>
</body>
</html>
