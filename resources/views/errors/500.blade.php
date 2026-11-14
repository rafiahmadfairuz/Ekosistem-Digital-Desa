<x-layouts.error title="Gangguan layanan">
    <main id="main" class="mx-auto max-w-3xl px-4 py-16 sm:px-8">
        <p class="text-sm font-semibold text-brand-strong">500 · Gangguan layanan</p>
        <h1 class="mt-3 text-2xl font-semibold">Terjadi gangguan saat memproses permintaan.</h1>
        <p class="mt-3 leading-7 text-muted">Coba kembali beberapa saat lagi. Jika masalah berlanjut, hubungi pengelola layanan.</p>
        <a class="mt-6 inline-flex min-h-11 items-center rounded-control bg-brand-strong px-4 font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2" href="{{ auth()->check() ? route('dashboard') : route('home') }}">Kembali</a>
    </main>
</x-layouts.error>
