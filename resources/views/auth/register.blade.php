<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#F4F6F9">
    <title>Daftar akun · Desa Digital</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas text-ink antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-20 focus:bg-paper focus:px-4 focus:py-3">Lewati ke konten</a>
    <header class="border-b border-slate-200 bg-paper">
        <div class="mx-auto flex min-h-16 max-w-6xl items-center justify-between gap-4 px-4 sm:px-8">
            <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center gap-3 font-semibold" aria-label="Desa Digital, beranda">
                <span class="grid size-9 place-items-center bg-ink text-sm font-bold text-white" aria-hidden="true">D</span>
                Desa Digital
            </a>
            <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center px-3 text-sm font-semibold underline underline-offset-4 hover:text-emerald-800">Sudah punya akun? Masuk</a>
        </div>
    </header>

    <main id="main" class="mx-auto grid min-h-[calc(100svh-4rem)] max-w-6xl items-start gap-10 px-4 py-10 sm:px-8 sm:py-16 lg:grid-cols-[0.8fr_1.2fr] lg:gap-20">
        <section class="lg:sticky lg:top-10" aria-labelledby="register-title">
            <p class="text-sm font-semibold text-emerald-800">Akun warga</p>
            <h1 id="register-title" class="mt-3 max-w-[12ch] text-3xl font-semibold leading-tight tracking-[-0.04em] sm:text-4xl">Mulai dengan akun Anda.</h1>
            <p class="mt-4 max-w-md text-base leading-7 text-muted">Pendaftaran membuat akun Warga dengan status menunggu aktivasi. Formulir ini tidak meminta nama legal atau nomor identitas.</p>
            <a href="{{ route('home') }}" class="mt-5 inline-flex min-h-11 items-center text-sm font-medium text-ink underline underline-offset-4 hover:text-emerald-800">Kembali ke beranda</a>
        </section>

        <section class="bg-paper p-5 sm:p-8 lg:p-10" aria-label="Formulir pendaftaran">
            @if (session('status'))
                <div class="mb-6 border-l-4 border-brand bg-emerald-50 p-4 text-base leading-7 text-emerald-950" role="status" aria-live="polite">
                    {{ session('status') }}
                    <a href="{{ route('login') }}" class="ml-1 font-semibold underline underline-offset-4">Ke halaman masuk</a>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 border-l-4 border-danger bg-red-50 p-4 text-sm leading-6 text-red-900" role="alert" aria-live="polite">
                    <p class="font-semibold">Periksa kembali isian berikut.</p>
                    <ul class="mt-1 list-inside list-disc">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('register.store') }}" data-unsaved-form novalidate>
                @csrf
                <div class="space-y-6">
                    <div>
                        <label for="username" class="block text-base font-semibold">Nama pengguna</label>
                        <p id="username-help" class="mt-1 text-sm leading-6 text-muted">3–60 karakter; gunakan huruf, angka, tanda hubung, atau garis bawah.</p>
                        <input id="username" name="username" type="text" value="{{ old('username') }}" required minlength="3" maxlength="60" pattern="[A-Za-z0-9_-]+" autocomplete="username" autocapitalize="none" spellcheck="false" aria-describedby="username-help @error('username') username-error @enderror" @error('username') aria-invalid="true" @enderror class="mt-2 min-h-12 w-full border border-slate-400 bg-white px-3 py-2 text-base text-ink placeholder:text-slate-500 focus-visible:border-brand" placeholder="contoh: warga01…">
                        @error('username')<p id="username-error" class="mt-2 text-sm font-medium text-red-800">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="email" class="block text-base font-semibold">Email untuk verifikasi</label>
                        <p id="email-help" class="mt-1 text-sm leading-6 text-muted">Gunakan alamat yang dapat Anda buka. Akun perlu verifikasi email dan aktivasi.</p>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="150" autocomplete="email" inputmode="email" autocapitalize="none" spellcheck="false" aria-describedby="email-help @error('email') email-error @enderror" @error('email') aria-invalid="true" @enderror class="mt-2 min-h-12 w-full border border-slate-400 bg-white px-3 py-2 text-base text-ink placeholder:text-slate-500 focus-visible:border-brand" placeholder="nama@example.com…">
                        @error('email')<p id="email-error" class="mt-2 text-sm font-medium text-red-800">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password" class="block text-base font-semibold">Kata sandi</label>
                        <p id="password-help" class="mt-1 text-sm leading-6 text-muted">Minimal 8 karakter, dengan huruf besar, huruf kecil, dan angka. Anda dapat menempelkan kata sandi.</p>
                        <div class="mt-2 flex min-h-12 rounded-control border border-slate-400 bg-white focus-within:border-brand focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-brand"><input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" aria-describedby="password-help @error('password') password-error @enderror" @error('password') aria-invalid="true" @enderror class="min-h-12 min-w-0 flex-1 rounded-l-control bg-transparent px-3 py-2 text-base text-ink focus-visible:outline-none"><button type="button" data-password-toggle="password" aria-controls="password" aria-pressed="false" class="min-h-12 min-w-11 px-3 text-sm font-medium underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">Lihat</button></div>
                        @error('password')<p id="password-error" class="mt-2 text-sm font-medium text-red-800">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-base font-semibold">Ulangi kata sandi</label>
                        <div class="mt-2 flex min-h-12 rounded-control border border-slate-400 bg-white focus-within:border-brand focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-brand"><input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="min-h-12 min-w-0 flex-1 rounded-l-control bg-transparent px-3 py-2 text-base text-ink focus-visible:outline-none"><button type="button" data-password-toggle="password_confirmation" aria-controls="password_confirmation" aria-pressed="false" class="min-h-12 min-w-11 px-3 text-sm font-medium underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">Lihat</button></div>
                    </div>

                    <div>
                        <button type="submit" data-submit-button class="inline-flex min-h-12 w-full items-center justify-center gap-3 bg-brand px-5 text-base font-semibold text-ink hover:bg-emerald-400 disabled:cursor-wait disabled:opacity-70">
                            <span>Buat akun</span>
                            <svg data-loading-indicator hidden class="size-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".25" stroke-width="3"></circle>
                                <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3"></path>
                            </svg>
                        </button>
                        <p data-loading-status class="sr-only" role="status" aria-live="polite"></p>
                    </div>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
