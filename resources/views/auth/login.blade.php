<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#F4F6F9">
    <title>Masuk · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F4F6F9] text-[#0A192F] antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-10 focus:rounded-md focus:bg-white focus:px-4 focus:py-3 focus:ring-2 focus:ring-[#059669]">Lewati ke konten</a>
    <header class="mx-auto max-w-6xl px-4 pt-4 sm:px-8">
        <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center px-3 text-sm font-medium underline underline-offset-4 hover:text-emerald-800">Beranda</a>
    </header>
    <main id="main" class="mx-auto flex min-h-screen w-full max-w-6xl items-center justify-center px-4 py-10 sm:px-8">
        <section class="grid w-full max-w-4xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm md:grid-cols-[0.9fr_1.1fr]" aria-labelledby="login-title">
            <div class="hidden flex-col justify-between bg-[#0A192F] p-10 text-white md:flex">
                <div>
                    <p class="text-sm font-semibold tracking-wide text-emerald-300">{{ config('app.name') }}</p>
                    <h1 class="mt-12 max-w-xs text-3xl font-semibold leading-tight">Layanan desa dalam satu ruang.</h1>
                </div>
                <p class="max-w-xs text-sm leading-6 text-slate-300">Masuk untuk mengakses layanan sesuai peran dan wilayah tugas Anda.</p>
            </div>
            <div class="p-6 sm:p-10">
                <p class="text-sm font-semibold text-emerald-800 md:hidden">{{ config('app.name') }}</p>
                <h1 id="login-title" class="mt-2 text-2xl font-semibold tracking-tight">Masuk ke akun</h1>
                <p class="mt-2 text-sm leading-6 text-slate-600">Gunakan nama pengguna atau email yang terdaftar.</p>

                @if (session('status'))
                    <p class="mt-5 border-l-4 border-[#059669] bg-emerald-50 p-3 text-sm text-emerald-950" role="status" aria-live="polite">{{ session('status') }}</p>
                @endif

                @if ($errors->any())
                    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert" aria-live="polite" tabindex="-1">
                        <p class="font-semibold">Tidak dapat masuk</p>
                        <ul class="mt-1 list-inside list-disc">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form class="mt-8 space-y-5" method="POST" action="{{ route('login.store') }}" data-unsaved-form novalidate>
                    @csrf
                    <div>
                        <label for="login" class="block text-sm font-medium">Nama pengguna atau email</label>
                        <input id="login" name="login" type="text" value="{{ old('login') }}" required autocomplete="username" autocapitalize="none" spellcheck="false" maxlength="255" @error('login') aria-invalid="true" aria-describedby="login-error" @enderror class="mt-2 min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-base text-[#0A192F] placeholder:text-slate-500 focus-visible:border-[#059669] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#059669]" placeholder="contoh: nama.pengguna…">
                        @error('login')<p id="login-error" class="mt-2 text-sm text-red-700">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium">Kata sandi</label>
                        <div class="mt-2 flex min-h-11 rounded-lg border border-slate-300 bg-white focus-within:border-[#059669] focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-[#059669]"><input id="password" name="password" type="password" required autocomplete="current-password" class="min-h-11 min-w-0 flex-1 rounded-l-lg bg-transparent px-3 py-2 text-base text-[#0A192F] focus-visible:outline-none"><button type="button" data-password-toggle="password" aria-controls="password" aria-pressed="false" class="min-h-11 min-w-11 px-3 text-sm font-medium underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#059669]">Lihat</button></div>
                    </div>
                    <label class="flex min-h-11 items-center gap-3 text-sm text-slate-700">
                        <input name="remember" type="checkbox" value="1" class="size-4 rounded border-slate-400 text-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#059669]">
                        Ingat saya di perangkat ini
                    </label>
                        <button type="submit" data-submit-button class="inline-flex min-h-11 w-full items-center justify-center gap-3 rounded-lg bg-[#059669] px-4 py-3 text-sm font-semibold text-[#0A192F] transition-colors hover:bg-emerald-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#0A192F] active:bg-emerald-400 disabled:cursor-wait disabled:opacity-70">
                        <span>Masuk</span>
                        <svg data-loading-indicator hidden class="size-5 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".25" stroke-width="3"></circle><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3"></path></svg>
                    </button>
                    <p data-loading-status class="sr-only" role="status" aria-live="polite"></p>
                </form>
                <p class="mt-4 text-center text-sm"><a href="{{ route('password.request') }}" class="min-h-11 inline-flex items-center font-medium underline underline-offset-4 hover:text-emerald-800">Lupa kata sandi?</a></p>
                <p class="mt-2 text-center text-sm"><a href="{{ route('verification.resend.request') }}" class="min-h-11 inline-flex items-center font-medium underline underline-offset-4 hover:text-emerald-800">Belum menerima tautan verifikasi?</a></p>
                <p class="mt-6 text-center text-sm text-slate-600">Belum punya akun? <a href="{{ route('register') }}" class="font-semibold text-[#0A192F] underline underline-offset-4 hover:text-emerald-800">Daftar akun warga</a></p>
            </div>
        </section>
    </main>
</body>
</html>
