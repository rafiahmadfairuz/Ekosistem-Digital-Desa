<x-layouts.app title="Pembayaran Xendit marketplace">
    <main id="main" class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-8">
        <x-ui.breadcrumbs :items="[['label' => 'Akun dan akses', 'url' => route('dashboard')], ['label' => 'Pembayaran Xendit marketplace']]" />
        <x-ui.page-heading title="Pembayaran Xendit marketplace" description="Pantau kesiapan pembayaran checkout dan pencairan penjual." />

        <section class="grid gap-4 lg:grid-cols-2" aria-label="Status integrasi Xendit">
            <article class="rounded-card border border-line bg-paper p-5">
                <h2 class="text-lg font-semibold">Pembayaran checkout</h2>
                <p class="mt-2 text-sm text-muted">Mode: <span class="font-medium text-ink">{{ ucfirst($mode) }}</span></p>
                <p class="mt-3 font-medium" role="status">
                    {{ $checkoutReady ? 'Siap menerima pembayaran melalui Xendit.' : 'Belum siap menerima pembayaran melalui Xendit.' }}
                </p>
                <p class="mt-2 text-sm text-muted">{{ $credentialsConfigured ? 'Kredensial API dan token webhook terdeteksi.' : 'Kredensial API atau token webhook belum dikonfigurasi.' }}</p>
            </article>

            <article class="rounded-card border border-line bg-paper p-5">
                <h2 class="text-lg font-semibold">Pencairan penjual</h2>
                <p class="mt-3 font-medium" role="status">
                    {{ $payoutsReady ? 'Konfigurasi payout Xendit terdeteksi.' : 'Payout Xendit belum diaktifkan.' }}
                </p>
                <p class="mt-2 text-sm text-muted">Pembayaran checkout marketplace masuk ke akun merchant. Saldo penjual dicatat setelah pesanan memenuhi syarat; pencairan otomatis hanya berjalan jika payout Xendit telah dikonfigurasi.</p>
            </article>
        </section>

        <x-ui.alert type="warning">
            Kunci API dan token webhook disimpan di konfigurasi server. Jangan masukkan secret key ke halaman ini atau membagikannya melalui chat. Callback pembayaran harus memakai endpoint webhook marketplace dan token yang cocok.
        </x-ui.alert>
    </main>
</x-layouts.app>
