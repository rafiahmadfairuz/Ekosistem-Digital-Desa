<x-layouts.app title="Checkout {{ $group->checkout_number }}">
    <main id="main" class="mx-auto max-w-5xl space-y-5 px-4 py-8 sm:px-8">
        @php($checkoutPayment = $group->payment)
        <x-ui.page-heading title="{{ $isFinal ? 'Pembelian selesai' : ($group->status === 'awaiting_payment' ? 'Selesaikan pembayaran checkout' : 'Ringkasan checkout') }}" description="{{ $group->checkout_number }} · Satu pembayaran untuk seluruh pesanan dari beberapa penjual." />
        @if($group->status === 'awaiting_payment' && $group->payment?->payment_provider === 'xendit')
            <section class="flex flex-wrap items-center justify-between gap-4 rounded-card border border-warning-ink/30 bg-warning-bg p-5 text-warning-ink" aria-labelledby="checkout-payment-title">
                <div><h2 id="checkout-payment-title" class="font-semibold">Pembayaran checkout</h2><p class="mt-1 text-sm">Total {{ 'Rp'.number_format((float) $group->grand_total, 0, ',', '.') }} · Dana dialokasikan ke setiap penjual setelah pembayaran terverifikasi.</p></div>
                <form method="POST" action="{{ route('marketplace.orders.group.payment-session', $group) }}">@csrf<x-ui.button type="submit">{{ $group->payment->checkout_url ? 'Lanjutkan pembayaran' : 'Bayar seluruh pesanan dengan Xendit' }}</x-ui.button></form>
            </section>
        @elseif($group->status === 'settlement_pending')
            <p class="rounded-card border border-line bg-paper p-4" role="status">Pembayaran diterima. Pesanan akan diteruskan ke penjual setelah Xendit mengonfirmasi seluruh pembagian dana.</p>
        @elseif($group->status === 'settlement_failed')
            <p class="rounded-card border border-danger-ink/30 bg-danger-bg p-4 text-danger-ink" role="alert">Pembayaran diterima, tetapi pembagian dana ke penjual perlu rekonsiliasi. Pesanan belum diteruskan. Hubungi pengelola; jangan membayar ulang.</p>
        @endif
        <section class="rounded-card border border-line bg-success-bg p-5 text-success-ink" aria-labelledby="next-steps-title">
            <h2 id="next-steps-title" class="font-semibold">Langkah berikutnya</h2>
            <ol class="mt-3 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-5">
                <li><strong>1. Pembayaran & alokasi</strong><span class="mt-1 block">{{ $group->payment?->payment_provider === 'xendit' ? 'Xendit mengonfirmasi pembayaran dan alokasi tiap penjual.' : 'COD menunggu pesanan diterima penjual.' }}</span></li>
                <li><strong>2. Penjual memeriksa</strong><span class="mt-1 block">Setiap penjual menerima pesanan barang miliknya.</span></li>
                <li><strong>3. Barang disiapkan</strong><span class="mt-1 block">Status persiapan diperbarui pada halaman pesanan.</span></li>
                <li><strong>4. Barang diserahkan</strong><span class="mt-1 block">Lihat pembaruan pengantaran atau pengambilan.</span></li>
                <li><strong>5. Anda konfirmasi</strong><span class="mt-1 block">Konfirmasi setelah barang diterima.</span></li>
            </ol>
            <p class="mt-4 border-t border-success-ink/20 pt-3 text-sm font-medium">{{ $isFinal ? 'Semua pesanan selesai dan pembayarannya telah dikonfirmasi. Struk final tersedia.' : ($group->payment?->payment_provider === 'xendit' ? 'Penjual belum menerima pesanan. Checkout baru diteruskan setelah pembayaran dan seluruh alokasi dana terverifikasi.' : 'Bukti checkout ini mencatat pesanan. COD tetap menunggu konfirmasi pengumpulan uang yang sah.') }}</p>
        </section>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('marketplace.orders.group.receipt', $group) }}" class="inline-flex min-h-11 items-center rounded-control bg-brand-strong px-4 font-semibold text-white">{{ $isFinal ? 'Lihat struk final pembelian' : 'Lihat bukti checkout' }}</a>
            <a class="inline-flex min-h-11 items-center rounded-control border border-line bg-paper px-4 font-medium" href="{{ route('marketplace.orders.index') }}">Buka riwayat pesanan</a>
        </div>
        @if($canArchive)
            <form method="POST" action="{{ route('marketplace.orders.group.history.destroy', $group) }}" data-confirm="Sembunyikan checkout ini dari riwayat Anda? Catatan transaksi tetap disimpan untuk penjual dan pemeriksaan." class="rounded-card border border-line bg-paper p-4">
                @csrf @method('DELETE')
                <p class="text-sm text-muted">Checkout tertutup dapat disembunyikan dari daftar pesanan Anda. Informasi keuangan dan riwayat penjual tetap tersimpan.</p>
                <button type="submit" class="mt-3 inline-flex min-h-11 items-center rounded-control border border-danger px-4 font-semibold text-danger hover:bg-danger/5 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger">Hapus dari riwayat saya</button>
            </form>
        @endif
        @if($checkoutPayment?->payment_provider === 'xendit')
            <section class="rounded-card border border-line bg-paper p-5" aria-labelledby="allocation-title">
                <h2 id="allocation-title" class="font-semibold">Rincian pembagian pembayaran</h2>
                <p class="mt-1 text-sm leading-6 text-muted">Total yang dibayar sekali akan dialokasikan otomatis oleh Xendit ke sub-account tiap penjual setelah dana masuk. Sisa saldo platform dipakai untuk biaya provider; jumlah bersih akhirnya mengikuti biaya Xendit dan dapat diperiksa pada dasbor XenPlatform.</p>
                <ul class="mt-4 divide-y divide-line">
                    @foreach($group->orders as $order)
                        @php($allocation = $checkoutPayment->allocations->firstWhere('order_id', $order->id))
                        <li class="flex flex-wrap justify-between gap-3 py-3 text-sm">
                            <span class="min-w-0 break-words"><strong>{{ $order->seller->business_name }}</strong><span class="block text-muted">{{ $allocation?->status === 'completed' ? 'Transfer split dikonfirmasi Xendit' : ($allocation?->status === 'failed' ? 'Transfer split gagal · perlu rekonsiliasi' : 'Menunggu konfirmasi transfer split') }}</span></span>
                            <span class="shrink-0 font-semibold tabular-nums">{{ $allocation ? 'Rp'.number_format((float) $allocation->amount, 0, ',', '.') : 'Belum dialokasikan' }}</span>
                        </li>
                    @endforeach
                </ul>
                <dl class="mt-2 space-y-2 border-t border-line pt-3 text-sm"><div class="flex justify-between gap-3"><dt>Total ditagihkan satu kali</dt><dd class="font-semibold tabular-nums">Rp{{ number_format((float) $checkoutPayment->amount, 0, ',', '.') }}</dd></div><div class="flex justify-between gap-3"><dt>Saldo yang tetap di akun platform sebelum biaya</dt><dd class="font-semibold tabular-nums">Rp{{ number_format((float) $checkoutPayment->platform_retained_amount, 0, ',', '.') }}</dd></div></dl>
            </section>
        @endif
        <section class="space-y-3" aria-label="Pesanan per penjual">
            @foreach($group->orders as $order)
                <article class="rounded-card border border-line bg-paper p-5 hover:border-brand">
                    <div class="flex flex-wrap justify-between gap-3"><div><h2 class="font-semibold">{{ $order->seller->business_name }}</h2><p class="text-sm text-muted">{{ $order->order_number }} · {{ str_replace('_', ' ', ucfirst($order->status)) }}</p></div><p class="font-semibold tabular-nums">Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}</p></div>
                    <p class="mt-3 break-words text-sm">{{ $order->items->pluck('product_name_snapshot')->join(', ') }}</p>
                    <p class="mt-2 text-sm text-muted">{{ $checkoutPayment?->payment_provider === 'xendit' ? 'Xendit · ' : 'COD · ' }}{{ $checkoutPayment?->status === 'paid' ? 'Lunas' : 'Belum dibayar' }} · {{ $order->delivery?->delivery_method === 'pickup' ? 'Ambil dari penjual' : 'Diantar' }}</p>
                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        <a href="{{ route('marketplace.orders.show', $order) }}" class="inline-flex min-h-11 items-center font-medium text-brand-strong underline">Lacak pesanan ini</a>
                    </div>
                </article>
            @endforeach
        </section>
    </main>
</x-layouts.app>
