<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#F4F6F9">
    <title>Pulihkan kata sandi · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas text-ink antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-20 focus:bg-paper focus:px-4 focus:py-3">Lewati ke konten</a>
    <main id="main" class="mx-auto flex min-h-screen max-w-2xl items-center px-4 py-12 sm:px-8">
        <section class="w-full border-y border-slate-300 bg-paper px-5 py-8 sm:px-10" aria-labelledby="reset-title">
            <p class="text-sm font-semibold text-emerald-800">{{ config('app.name') }}</p>
            <h1 id="reset-title" class="mt-3 text-2xl font-semibold">Pulihkan kata sandi</h1>
            <p class="mt-3 text-base leading-7 text-muted">Masukkan email terverifikasi. Jika akun memenuhi syarat, tautan pemulihan akan dikirim.</p>
            @if (session('status'))<p class="mt-5 border-l-4 border-brand bg-emerald-50 p-4" role="status" aria-live="polite">{{ session('status') }}</p>@endif
            @error('email')<p class="mt-4 text-sm text-red-800" role="alert">{{ $message }}</p>@enderror
            <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5">@csrf
                <div><label for="email" class="block text-sm font-semibold">Email akun</label><input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="150" autocomplete="email" inputmode="email" autocapitalize="none" spellcheck="false" class="mt-2 min-h-12 w-full border border-slate-400 bg-white px-3 text-base focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand" placeholder="nama@example.com…"></div>
                <button class="min-h-12 bg-brand px-5 font-semibold text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ink">Kirim tautan</button>
            </form>
            <a href="{{ route('login') }}" class="mt-5 inline-flex min-h-11 items-center text-sm font-medium underline underline-offset-4">Kembali ke masuk</a>
        </section>
    </main>
</body>
</html>
