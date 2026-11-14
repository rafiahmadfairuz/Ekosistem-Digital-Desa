<x-layouts.app title="Pembayaran Xendit marketplace">
    <main id="main" class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-8">
        <x-ui.breadcrumbs :items="[['label' => 'Akun dan akses', 'url' => route('admin.users.manage')], ['label' => 'Pembayaran Xendit']]" />
        <x-ui.page-heading title="Pembayaran dan payout Xendit" description="Pembayaran checkout masuk ke akun merchant. Penjual memperoleh saldo setelah pesanan selesai dan dapat meminta pencairan melalui Xendit." />

        <section class="space-y-4 rounded-card border border-line bg-paper p-5" aria-labelledby="xendit-status-heading">
            <h2 id="xendit-status-heading" class="font-semibold">Status konfigurasi</h2>
            <dl class="grid gap-4 sm:grid-cols-3">
                <div><dt class="text-sm text-muted">Mode</dt><dd class="mt-1 font-semibold">{{ $mode === 'test' ? 'Test' : ($mode === 'live' ? 'Live' : 'Simulasi') }}</dd></div>
                <div><dt class="text-sm text-muted">Kredensial API dan webhook</dt><dd class="mt-1 font-semibold">{{ $credentialsConfigured ? 'Terpasang' : 'Belum lengkap' }}</dd></div>
                <div><dt class="text-sm text-muted">Checkout marketplace</dt><dd class="mt-1 font-semibold">{{ $checkoutReady ? 'Siap' : 'Belum siap' }}</dd></div>
                <div><dt class="text-sm text-muted">Xendit Payouts</dt><dd class="mt-1 font-semibold">{{ $payoutsReady ? 'Aktif' : 'Belum aktif' }}</dd></div>
            </dl>
        </section>

        <x-ui.alert type="warning">
            Pembayaran masuk ke saldo akun merchant Xendit. Saldo penjual di aplikasi hanya bertambah berdasarkan pesanan yang selesai dan pembayaran terkonfirmasi. Payout Xendit baru dikirim ketika penjual mengajukan pencairan; sistem menahan saldo saat permintaan dibuat. Payout tetap dapat gagal, ditolak, atau menunggu pemeriksaan. Jangan aktifkan mode live sebelum akun memiliki izin API Money Out dan pemilik menyetujui model bisnis serta sumber dana payout.
        </x-ui.alert>

        <section class="rounded-card border border-line bg-paper p-5" aria-labelledby="xendit-setup-heading">
            <h2 id="xendit-setup-heading" class="font-semibold">Konfigurasi server</h2>
            <p class="mt-2 text-sm leading-6 text-muted">Checkout memerlukan <code>MARKETPLACE_PAYMENT_MODE</code>, <code>XENDIT_SECRET_KEY</code>, dan <code>XENDIT_WEBHOOK_TOKEN</code>. Pencairan memerlukan <code>XENDIT_PAYOUTS_ENABLED=true</code>, <code>XENDIT_PAYOUT_WEBHOOK_TOKEN</code>, izin Money Out, dan worker antrean aktif. Daftarkan kedua webhook pada dashboard Xendit. Tidak ada credential yang ditampilkan atau disimpan pada halaman ini.</p>
        </section>
    </main>
</x-layouts.app>
