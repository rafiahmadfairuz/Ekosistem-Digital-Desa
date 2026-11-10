<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#F4F6F9">
    <title>Buat kata sandi baru · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas text-ink antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-20 focus:bg-paper focus:px-4 focus:py-3">Lewati ke konten</a>
    <main id="main" class="mx-auto flex min-h-screen max-w-2xl items-center px-4 py-12 sm:px-8">
        <section class="w-full border-y border-slate-300 bg-paper px-5 py-8 sm:px-10" aria-labelledby="reset-title">
            <h1 id="reset-title" class="text-2xl font-semibold">Buat kata sandi baru</h1>
            @if ($errors->any())<div class="mt-5 border-l-4 border-red-700 bg-red-50 p-4 text-sm text-red-900" role="alert" tabindex="-1"><ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-5">@csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div><label for="email" class="block text-sm font-semibold">Email akun</label><input id="email" name="email" type="email" value="{{ old('email', $email) }}" required maxlength="150" autocomplete="email" inputmode="email" autocapitalize="none" spellcheck="false" class="mt-2 min-h-12 w-full border border-slate-400 bg-white px-3 text-base focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand"></div>
                <div><label for="password" class="block text-sm font-semibold">Kata sandi baru</label><p id="password-help" class="mt-1 text-sm text-muted">Minimal 8 karakter, dengan huruf besar, huruf kecil, dan angka.</p><input id="password" name="password" type="password" required minlength="8" autocomplete="new-password" aria-describedby="password-help" class="mt-2 min-h-12 w-full border border-slate-400 bg-white px-3 text-base focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand"></div>
                <div><label for="password_confirmation" class="block text-sm font-semibold">Ulangi kata sandi baru</label><input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password" class="mt-2 min-h-12 w-full border border-slate-400 bg-white px-3 text-base focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand"></div>
                <button class="min-h-12 bg-brand px-5 font-semibold text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ink">Simpan kata sandi</button>
            </form>
        </section>
    </main>
</body>
</html>
