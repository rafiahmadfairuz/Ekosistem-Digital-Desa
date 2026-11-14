<x-layouts.error title="Sesi berakhir">
    <main id="main" class="mx-auto max-w-3xl px-4 py-16 sm:px-8">
        <p class="text-sm font-semibold text-brand-strong">419 · Sesi berakhir</p>
        <h1 class="mt-3 text-2xl font-semibold">Sesi Anda sudah berakhir.</h1>
        <p class="mt-3 leading-7 text-muted">Muat ulang halaman, lalu kirim kembali isian Anda.</p>
        <a class="mt-6 inline-flex min-h-11 items-center rounded-control bg-brand-strong px-4 font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2" href="{{ url()->previous() }}">Kembali ke formulir</a>
    </main>
</x-layouts.error>
