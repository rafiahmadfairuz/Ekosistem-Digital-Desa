<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#F4F6F9">
    <title>Kirim ulang tautan verifikasi · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas text-ink antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-20 focus:bg-paper focus:px-4 focus:py-3">Lewati ke konten</a>
    <header class="border-b border-line bg-paper">
        <div class="mx-auto flex min-h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-8">
            <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center font-semibold">{{ config('app.name') }}</a>
            <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center px-2 text-sm font-medium underline underline-offset-4">Masuk</a>
        </div>
    </header>

    <main id="main" class="mx-auto flex min-h-[calc(100svh-4rem)] max-w-6xl items-center px-4 py-10 sm:px-8">
        <section class="w-full max-w-xl border border-line bg-paper p-5 sm:p-8" aria-labelledby="resend-title">
            <p class="text-sm font-medium text-muted">Pemulihan akses akun</p>
            <h1 id="resend-title" class="mt-2 text-2xl font-semibold tracking-tight">Kirim ulang tautan verifikasi</h1>
            <p class="mt-2 text-sm leading-6 text-muted">Masukkan email yang digunakan saat mendaftar. Jika email sesuai dengan akun yang perlu diverifikasi, tautan baru akan dikirim.</p>

            @if (session('status'))
                <p class="mt-5 border-l-2 border-brand bg-emerald-50 px-4 py-3 text-sm leading-6 text-emerald-950" role="status" aria-live="polite">{{ session('status') }}</p>
            @endif

            @if (config('mail.default') === 'log')
                <p class="mt-4 border-l-2 border-slate-400 bg-slate-50 px-4 py-3 text-sm leading-6 text-muted">Mode email lokal aktif. Untuk mengambil tautan, minta administrator memeriksa <span class="font-medium text-ink">storage/logs/laravel.log</span>. Pada penggunaan normal, atur layanan email agar tautan masuk ke kotak email Anda.</p>
            @endif

            <form class="mt-6" method="POST" action="{{ route('verification.resend.public') }}">
                @csrf
                <label for="email" class="block text-sm font-medium">Alamat email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" inputmode="email" autocapitalize="none" spellcheck="false" maxlength="255" placeholder="nama@contoh.id…" @error('email') aria-invalid="true" aria-describedby="email-error" @enderror class="mt-2 min-h-11 w-full border border-slate-400 bg-white px-3 py-2 text-base text-ink placeholder:text-slate-600 focus-visible:border-brand focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                @error('email')<p id="email-error" class="mt-2 text-sm text-red-800">{{ $message }}</p>@enderror
                <button type="submit" data-submit-button class="mt-5 inline-flex min-h-11 w-full items-center justify-center gap-3 bg-brand px-4 py-3 text-sm font-semibold text-ink hover:bg-emerald-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ink disabled:cursor-wait disabled:opacity-70">
                    <span>Kirim tautan verifikasi</span>
                    <svg data-loading-indicator hidden class="size-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".25" stroke-width="3"></circle><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3"></path></svg>
                </button>
                <p data-loading-status class="sr-only" role="status" aria-live="polite"></p>
            </form>

            <p class="mt-5 text-sm text-muted">Tidak punya akses ke email itu? Hubungi administrator desa untuk bantuan memperbarui akun.</p>
        </section>
    </main>
</body>
</html>
