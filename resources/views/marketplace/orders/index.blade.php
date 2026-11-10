<x-layouts.app title="{{ $sellerView ? 'Pesanan toko' : 'Pesanan saya' }}">
    <main id="main" class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-8">
        <x-ui.page-heading title="{{ $sellerView ? 'Pesanan toko' : 'Pesanan saya' }}" description="{{ $sellerView ? 'Periksa pesanan dan perbarui status untuk pembeli.' : 'Pantau pesanan dari setiap penjual. Checkout dari beberapa toko tampil sebagai pesanan terpisah.' }}" />
        @if(! $sellerView)<x-ui.alert type="info">Riwayat hanya bisa disembunyikan setelah semua pesanan dalam checkout selesai, dibatalkan, atau ditolak. Catatan penjualan tetap tersimpan.</x-ui.alert>@endif
        <section class="space-y-3" aria-label="Daftar pesanan">
            @forelse($orders as $order)
                @php
                    $statusLabels = ['pending_seller' => 'Menunggu penjual', 'accepted' => 'Diterima penjual', 'rejected' => 'Ditolak', 'processing' => 'Sedang disiapkan', 'ready_for_delivery' => 'Siap diserahkan', 'in_delivery' => 'Dalam pengantaran', 'delivered' => 'Sudah diterima kurir/diambil', 'delivery_failed' => 'Pengantaran terkendala', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan', 'awaiting_payment' => 'Menunggu pembayaran'];
                    $paymentLabels = ['pending' => 'Belum dibayar', 'paid' => 'Lunas tercatat', 'cancelled' => 'Tidak ditagihkan', 'failed' => 'Gagal'];
                    $checkoutPayment = $order->group?->payment ?? $order->payment;
                @endphp
                <a href="{{ route('marketplace.orders.show', $order) }}" class="group block rounded-card border border-line bg-paper p-5 transition-colors hover:border-brand focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0"><p class="text-sm text-muted">{{ $sellerView ? 'Pembeli' : 'Penjual' }}</p><h2 class="mt-1 break-words text-lg font-semibold">{{ $sellerView ? $order->buyer->username : $order->seller->business_name }}</h2><p class="mt-1 break-all font-mono text-sm text-muted">{{ $order->order_number }}</p></div>
                        <div class="min-w-0 text-left sm:text-right"><p class="text-lg font-semibold tabular-nums">Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}</p><p class="mt-1 text-sm font-medium">{{ $statusLabels[$order->status] ?? 'Status diperbarui' }}</p><p class="text-sm text-muted">{{ $checkoutPayment?->payment_provider === 'xendit' ? 'Xendit · ' : 'COD · ' }}{{ $paymentLabels[$checkoutPayment?->status ?? 'pending'] ?? 'Status pembayaran' }}</p></div>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-line pt-3"><p class="min-w-0 break-words text-sm text-muted">{{ $order->items->count() }} jenis barang · {{ $order->items->pluck('product_name_snapshot')->join(', ') }}</p><span class="min-h-11 shrink-0 font-medium text-brand-strong underline underline-offset-4">Lihat rincian →</span></div>
                </a>
            @empty
                <x-ui.empty-state title="Belum ada pesanan" description="Pesanan akan muncul di sini setelah checkout berhasil." action="Jelajahi produk" href="{{ route('marketplace.index') }}" />
            @endforelse
        </section>
        {{ $orders->links() }}
    </main>
</x-layouts.app>
