<x-layouts.app title="{{ $sellerView ? 'Pesanan toko' : 'Pesanan saya' }}">
    <main id="main" class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        {{-- Header Halaman Minimalis --}}
        <header class="flex flex-col gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="max-w-2xl">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    {{ $sellerView ? 'Pesanan toko' : 'Pesanan saya' }}
                </h1>
                <p class="mt-1.5 text-sm leading-relaxed text-slate-500">
                    {{ $sellerView ? 'Periksa pesanan masuk dan perbarui status pengiriman untuk pembeli.' : 'Pantau status dan pembayaran pesanan dari setiap penjual. Checkout dari beberapa toko tampil terpisah.' }}
                </p>
            </div>

            <div class="shrink-0">
                <a
                    href="{{ $sellerView ? route('marketplace.seller.products') : route('marketplace.index') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs transition hover:-translate-y-0.5 hover:border-emerald-600 hover:bg-emerald-50/40 hover:text-emerald-700 hover:shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                >
                    {{ $sellerView ? 'Kelola produk toko' : 'Jelajahi pasar desa' }}
                </a>
            </div>
        </header>

        {{-- Informasi Riwayat --}}
        @if (! $sellerView)
            <div class="flex items-start gap-2.5 rounded-2xl border border-slate-200/80 bg-slate-50/80 px-4 py-3.5 text-xs leading-relaxed text-slate-600 shadow-2xs">
                <svg class="mt-0.5 size-4 shrink-0 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 16v-4" />
                    <path d="M12 8h.01" />
                </svg>
                <p>Riwayat hanya bisa disembunyikan setelah semua pesanan dalam checkout selesai, dibatalkan, atau ditolak. Catatan penjualan tetap tersimpan.</p>
            </div>
        @endif

        {{-- Daftar Kartu Pesanan --}}
        <section class="space-y-4" aria-label="Daftar pesanan">
            @forelse ($orders as $order)
                @php
                    $statusLabels = [
                        'pending_seller' => 'Menunggu penjual',
                        'accepted' => 'Diterima penjual',
                        'rejected' => 'Ditolak',
                        'processing' => 'Sedang disiapkan',
                        'ready_for_delivery' => 'Siap diserahkan',
                        'in_delivery' => 'Dalam pengantaran',
                        'delivered' => 'Sudah diterima kurir/diambil',
                        'delivery_failed' => 'Pengantaran terkendala',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        'awaiting_payment' => 'Menunggu pembayaran',
                    ];

                    $statusStyles = [
                        'pending_seller' => 'border-amber-200 bg-amber-50 text-amber-800',
                        'awaiting_payment' => 'border-amber-200 bg-amber-50 text-amber-800',
                        'accepted' => 'border-sky-200 bg-sky-50 text-sky-800',
                        'processing' => 'border-sky-200 bg-sky-50 text-sky-800',
                        'ready_for_delivery' => 'border-indigo-200 bg-indigo-50 text-indigo-800',
                        'in_delivery' => 'border-indigo-200 bg-indigo-50 text-indigo-800',
                        'delivered' => 'border-teal-200 bg-teal-50 text-teal-800',
                        'completed' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                        'rejected' => 'border-rose-200 bg-rose-50 text-rose-700',
                        'cancelled' => 'border-rose-200 bg-rose-50 text-rose-700',
                        'delivery_failed' => 'border-rose-200 bg-rose-50 text-rose-700',
                    ];

                    $statusDots = [
                        'pending_seller' => 'bg-amber-500',
                        'awaiting_payment' => 'bg-amber-500',
                        'accepted' => 'bg-sky-500',
                        'processing' => 'bg-sky-500',
                        'ready_for_delivery' => 'bg-indigo-500',
                        'in_delivery' => 'bg-indigo-500',
                        'delivered' => 'bg-teal-500',
                        'completed' => 'bg-emerald-500',
                        'rejected' => 'bg-rose-500',
                        'cancelled' => 'bg-rose-500',
                        'delivery_failed' => 'bg-rose-500',
                    ];

                    $paymentLabels = [
                        'pending' => 'Belum dibayar',
                        'paid' => 'Lunas tercatat',
                        'cancelled' => 'Tidak ditagihkan',
                        'failed' => 'Gagal',
                    ];

                    $checkoutPayment = $order->group?->payment ?? $order->payment;
                    $paymentStatus = $checkoutPayment?->status ?? 'pending';
                    $statusText = $statusLabels[$order->status] ?? 'Status diperbarui';
                    $badgeClass = $statusStyles[$order->status] ?? 'border-slate-200 bg-slate-50 text-slate-700';
                    $dotClass = $statusDots[$order->status] ?? 'bg-slate-400';
                @endphp

                <a
                    href="{{ route('marketplace.orders.show', $order) }}"
                    class="group block rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-500/70 hover:shadow-md focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:p-6"
                >
                    {{-- Baris Atas: Nomor Pesanan, Tanggal & Status --}}
                    <div class="flex flex-wrap items-center justify-between gap-2.5 border-b border-slate-100 pb-3.5">
                        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500">
                            <span class="font-mono font-semibold text-slate-700 break-all">{{ $order->order_number }}</span>
                            @if ($order->created_at)
                                <span class="text-slate-300" aria-hidden="true">·</span>
                                <span>{{ $order->created_at->translatedFormat('d M Y, H:i') }}</span>
                            @endif
                        </div>

                        <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold {{ $badgeClass }}">
                            <span class="size-1.5 rounded-full {{ $dotClass }}" aria-hidden="true"></span>
                            <span>{{ $statusText }}</span>
                        </span>
                    </div>

                    {{-- Baris Tengah: Pihak Terkait & Nominal Pembayaran --}}
                    <div class="mt-4 flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-[11px] font-medium uppercase tracking-wider text-slate-400">
                                {{ $sellerView ? 'Pembeli' : 'Penjual' }}
                            </p>
                            <h2 class="mt-0.5 break-words text-base font-bold text-slate-900 transition-colors group-hover:text-emerald-700 sm:text-lg">
                                {{ $sellerView ? $order->buyer->username : $order->seller->business_name }}
                            </h2>
                        </div>

                        <div class="min-w-0 text-left sm:text-right">
                            <p class="text-[11px] font-medium uppercase tracking-wider text-slate-400">Total pesanan</p>
                            <p class="mt-0.5 text-lg font-bold text-slate-900 tabular-nums sm:text-xl">
                                Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}
                            </p>
                            <p class="mt-1 text-xs font-medium {{ $paymentStatus === 'paid' ? 'text-emerald-700' : ($paymentStatus === 'failed' ? 'text-rose-600' : 'text-slate-500') }}">
                                {{ $checkoutPayment?->payment_provider === 'xendit' ? 'Xendit' : 'COD' }} · {{ $paymentLabels[$paymentStatus] ?? 'Status pembayaran' }}
                            </p>
                        </div>
                    </div>

                    {{-- Baris Bawah: Ringkasan Barang & Aksi Rincian --}}
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3.5">
                        <p class="min-w-0 truncate text-xs text-slate-600 sm:text-sm">
                            <span class="font-semibold text-slate-800">{{ $order->items->count() }} jenis barang</span>
                            <span class="mx-1 text-slate-300" aria-hidden="true">·</span>
                            <span class="text-slate-500">{{ $order->items->pluck('product_name_snapshot')->join(', ') }}</span>
                        </p>

                        <span class="inline-flex shrink-0 items-center gap-1 text-xs font-semibold text-emerald-700 transition-transform group-hover:translate-x-0.5 sm:text-sm">
                            <span>Lihat rincian</span>
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14" />
                                <path d="m12 5 7 7-7 7" />
                            </svg>
                        </span>
                    </div>
                </a>
            @empty
                <div class="rounded-2xl border border-slate-200/90 bg-white p-8 shadow-sm">
                    <x-ui.empty-state
                        title="Belum ada pesanan"
                        description="Pesanan akan muncul di sini setelah checkout berhasil."
                        action="Jelajahi produk"
                        href="{{ route('marketplace.index') }}"
                    />
                </div>
            @endforelse
        </section>

        <div class="pt-2">
            {{ $orders->links() }}
        </div>
    </main>
</x-layouts.app>
