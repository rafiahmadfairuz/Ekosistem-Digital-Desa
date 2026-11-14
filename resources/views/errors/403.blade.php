<x-layouts.error title="Akses tidak tersedia">
    <main id="main" class="mx-auto max-w-3xl px-4 py-16 sm:px-8">
        <p class="text-sm font-semibold text-brand-strong">403 · Akses tidak tersedia</p>
        <h1 class="mt-3 text-2xl font-semibold">Halaman tidak tersedia atau Anda tidak memiliki akses.</h1>
        <p class="mt-3 leading-7 text-muted">Periksa akun yang digunakan atau kembali ke halaman utama.</p>
        <a class="mt-6 inline-flex min-h-11 items-center rounded-control bg-brand-strong px-4 font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2" href="{{ auth()->check() ? route('dashboard') : route('home') }}">Kembali</a>
    </main>
</x-layouts.error>
