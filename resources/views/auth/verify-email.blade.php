<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#F4F6F9">
    <title>Verifikasi email · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas text-ink antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-20 focus:bg-paper focus:px-4 focus:py-3">Lewati ke konten</a>
    <main id="main" class="mx-auto flex min-h-screen max-w-2xl items-center px-4 py-12 sm:px-8">
        <section class="w-full border-y border-slate-300 bg-paper px-5 py-8 sm:px-10" aria-labelledby="verify-title">
            <p class="text-sm font-semibold text-emerald-800">{{ config('app.name') }}</p>
            <h1 id="verify-title" class="mt-3 text-2xl font-semibold">Verifikasi alamat email</h1>
            <p class="mt-3 text-base leading-7 text-muted">Buka tautan yang kami kirim ke email Anda. Setelah diverifikasi, akun tetap menunggu aktivasi administrator.</p>
            @if (session('status'))<p class="mt-5 border-l-4 border-brand bg-emerald-50 p-4" role="status" aria-live="polite">{{ session('status') }}</p>@endif
            @if(auth()->user()->email)<form class="mt-6" method="POST" action="{{ route('verification.send') }}">@csrf<button class="min-h-11 bg-brand px-5 font-semibold text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ink">Kirim ulang tautan</button></form>@else<p class="mt-5 border-l-4 border-amber-600 bg-amber-50 p-4">Akun ini belum memiliki email. Hubungi administrator untuk memperbarui metode verifikasi.</p>@endif
            <form class="mt-3" method="POST" action="{{ route('logout') }}">@csrf<button class="min-h-11 px-3 font-medium underline underline-offset-4">Keluar</button></form>
        </section>
    </main>
</body>
</html>
