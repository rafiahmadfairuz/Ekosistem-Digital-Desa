<x-layouts.app title="Pesanan {{ $order->order_number }}">
    @php
        $statusLabels = [
            'pending_seller' => 'Menunggu penjual',
            'accepted' => 'Diterima penjual',
            'rejected' => 'Ditolak',
            'processing' => 'Sedang disiapkan',
            'ready_for_delivery' => 'Siap diserahkan',
            'in_delivery' => 'Dalam pengantaran',
            'delivered' => 'Sudah diserahkan',
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

        $deliveryLabels = [
            'preparing' => 'Disiapkan',
            'ready' => 'Siap diserahkan',
            'in_delivery' => 'Dalam pengantaran',
            'delivered' => 'Diserahkan',
            'failed' => 'Ada kendala',
            'confirmed' => 'Diterima',
        ];

        $paymentLabels = [
            'pending' => 'Belum dibayar',
            'paid' => 'Lunas tercatat',
            'cancelled' => 'Tidak ditagihkan',
            'failed' => 'Gagal',
        ];

        $checkoutPayment = $order->group?->payment ?? $order->payment;
        $paymentStatus = $checkoutPayment?->status ?? 'pending';
        $sellerAllocation = $order->group?->payment?->allocations?->firstWhere('order_id', $order->id);

        $orderBadgeClass = $statusStyles[$order->status] ?? 'border-slate-200 bg-slate-50 text-slate-700';
        $orderDotClass = $statusDots[$order->status] ?? 'bg-slate-400';

        $paymentBadgeClass = $paymentStatus === 'paid'
            ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
            : ($paymentStatus === 'failed' || $paymentStatus === 'cancelled'
                ? 'border-rose-200 bg-rose-50 text-rose-700'
                : 'border-amber-200 bg-amber-50 text-amber-800');
        $paymentDotClass = $paymentStatus === 'paid'
            ? 'bg-emerald-500'
            : ($paymentStatus === 'failed' || $paymentStatus === 'cancelled'
                ? 'bg-rose-500'
                : 'bg-amber-500');
    @endphp

    <main id="main" class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        {{-- Navigasi Kembali --}}
        <div>
            <a
                href="{{ $viewerIsSeller ? route('marketplace.seller.orders') : route('marketplace.orders.index') }}"
                class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs transition hover:-translate-y-0.5 hover:border-emerald-600 hover:bg-emerald-50/40 hover:text-emerald-700 hover:shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
            >
                <svg class="size-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                <span>Kembali ke {{ $viewerIsSeller ? 'pesanan toko' : 'pesanan saya' }}</span>
            </a>
        </div>

        {{-- Header Identitas Pesanan --}}
        <header class="flex flex-col gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end sm:justify-between">
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                    {{ $viewerIsSeller ? 'Pesanan dari pembeli' : 'Pesanan dari penjual' }}
                </p>
                <h1 class="mt-1 break-all font-mono text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    {{ $order->order_number }}
                </h1>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-slate-600">
                    <span class="font-semibold text-slate-800">
                        {{ $viewerIsSeller ? $order->buyer->username : $order->seller->business_name }}
                    </span>
                    @if ($order->created_at)
                        <span class="text-slate-300" aria-hidden="true">·</span>
                        <span class="text-xs text-slate-500">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</span>
                    @endif
                </div>
            </div>

            <div class="shrink-0">
                <span class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-xs font-bold shadow-2xs sm:text-sm {{ $orderBadgeClass }}">
                    <span class="size-2 rounded-full {{ $orderDotClass }}" aria-hidden="true"></span>
                    <span>{{ $statusLabels[$order->status] ?? 'Status diperbarui' }}</span>
                </span>
            </div>
        </header>

        {{-- Banner Bukti / Struk Checkout (Khusus Pembeli) --}}
        @unless($viewerIsSeller)
            <section
                class="flex flex-col justify-between gap-4 rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm sm:flex-row sm:items-center"
                aria-label="Struk pembelian"
            >
                <div class="min-w-0">
                    <h2 class="text-sm font-bold text-slate-900">
                        {{ $groupIsFinal ? 'Pembelian selesai' : 'Bukti checkout' }}
                    </h2>
                    <p class="mt-0.5 text-xs leading-relaxed text-slate-500">
                        {{ $groupIsFinal ? 'Semua pesanan penjual selesai dan pembayarannya telah dikonfirmasi.' : 'Struk final tersedia setelah semua pesanan selesai dan pembayaran dikonfirmasi.' }}
                    </p>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2.5">
                    <a
                        href="{{ route('marketplace.orders.group', $order->group) }}"
                        class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs transition hover:border-emerald-600 hover:bg-emerald-50/40 hover:text-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                    >
                        Ringkasan checkout
                    </a>
                    <a
                        href="{{ route('marketplace.orders.group.receipt', $order->group) }}"
                        class="inline-flex min-h-10 items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                    >
                        {{ $groupIsFinal ? 'Buka struk final' : 'Buka bukti checkout' }}
                    </a>
                </div>
            </section>
        @endunless

        {{-- Grid Utama: Kiri (Rincian Barang & Riwayat), Kanan (Pembayaran & Pengiriman + Aksi) --}}
        <div class="grid items-start gap-6 lg:grid-cols-12">
            {{-- Kolom Kiri (7 cols): Rincian Barang & Riwayat Status --}}
            <div class="space-y-6 lg:col-span-7">
                {{-- Kartu Rincian Barang --}}
                <article class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <h2 class="text-base font-bold text-slate-900">Rincian barang</h2>
                        <span class="rounded-full border border-slate-200/80 bg-white px-2.5 py-0.5 text-xs font-medium text-slate-600">
                            {{ $order->items->count() }} item
                        </span>
                    </div>

                    <div class="divide-y divide-slate-100 px-6">
                        @foreach($order->items as $item)
                            <div class="flex items-start justify-between gap-4 py-4">
                                <div class="min-w-0">
                                    <h3 class="break-words text-sm font-semibold text-slate-900">
                                        {{ $item->product_name_snapshot }}
                                    </h3>
                                    <p class="mt-1 text-xs text-slate-500 tabular-nums">
                                        {{ number_format($item->quantity, 0, ',', '.') }} × Rp{{ number_format((float) $item->unit_price, 0, ',', '.') }}
                                        <span class="text-slate-400">/ {{ data_get($item->attributes_snapshot, 'unit', 'item') }}</span>
                                    </p>
                                </div>
                                <p class="shrink-0 text-sm font-bold text-slate-900 tabular-nums">
                                    Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex items-center justify-between gap-4 border-t border-slate-200/80 bg-slate-50/70 px-6 py-4">
                        <span class="text-sm font-semibold text-slate-700">Total pesanan</span>
                        <span class="text-xl font-bold tracking-tight text-slate-900 tabular-nums">
                            Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}
                        </span>
                    </div>
                </article>

                {{-- Kartu Riwayat Pesanan & Pengantaran --}}
                <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm" aria-labelledby="history-title">
                    <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <h2 id="history-title" class="text-base font-bold text-slate-900">Riwayat aktivitas</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Catatan perubahan status pesanan dan proses pengantaran.</p>
                    </div>

                    <div class="space-y-6 p-6">
                        {{-- Timeline Status Pesanan --}}
                        <div>
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Status pesanan</h3>
                            <ol class="mt-3.5 space-y-4 border-l-2 border-slate-200 pl-4">
                                @foreach($order->statusEvents as $event)
                                    <li class="relative">
                                        <span class="absolute top-1.5 -left-[21px] size-2.5 rounded-full border-2 border-white bg-emerald-600" aria-hidden="true"></span>
                                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                                            <p class="text-xs font-semibold text-slate-900 sm:text-sm">
                                                {{ $event->from_status ? ($statusLabels[$event->from_status] ?? 'Status sebelumnya').' → ' : '' }}{{ $statusLabels[$event->to_status] ?? 'Status diperbarui' }}
                                            </p>
                                            <span class="text-xs text-slate-400 tabular-nums">
                                                {{ $event->created_at->format('d/m/Y H:i') }}
                                            </span>
                                        </div>
                                        @if($event->reason)
                                            <p class="mt-1 rounded-lg bg-slate-50 px-3 py-1.5 text-xs leading-relaxed text-slate-600 break-words">
                                                {{ $event->reason }}
                                            </p>
                                        @endif
                                    </li>
                                @endforeach
                            </ol>
                        </div>

                        {{-- Timeline Pengantaran --}}
                        <div class="border-t border-slate-100 pt-5">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Status pengantaran</h3>
                            <ol class="mt-3.5 space-y-4 border-l-2 border-slate-200 pl-4">
                                @forelse($order->delivery->events as $event)
                                    <li class="relative">
                                        <span class="absolute top-1.5 -left-[21px] size-2.5 rounded-full border-2 border-white bg-sky-500" aria-hidden="true"></span>
                                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                                            <p class="text-xs font-semibold text-slate-900 sm:text-sm">
                                                {{ $event->from_status ? ($deliveryLabels[$event->from_status] ?? 'Status sebelumnya').' → ' : '' }}{{ $deliveryLabels[$event->to_status] ?? 'Status diperbarui' }}
                                            </p>
                                            <span class="text-xs text-slate-400 tabular-nums">
                                                {{ $event->created_at->format('d/m/Y H:i') }}
                                            </span>
                                        </div>
                                        @if($event->note)
                                            <p class="mt-1 rounded-lg bg-slate-50 px-3 py-1.5 text-xs leading-relaxed text-slate-600 break-words">
                                                {{ $event->note }}
                                            </p>
                                        @endif
                                    </li>
                                @empty
                                    <li class="text-xs text-slate-500">Belum ada pembaruan pengantaran.</li>
                                @endforelse
                            </ol>
                        </div>
                    </div>
                </section>
            </div>

            {{-- Kolom Kanan (5 cols): Pengiriman/Aksi Utama & Pembayaran --}}
            <div class="space-y-6 lg:col-span-5">
                {{-- Kartu Pengiriman / Pengambilan & Panel Aksi --}}
                <article class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-md shadow-slate-900/5">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <h2 class="text-base font-bold text-slate-900">Pengiriman & pengambilan</h2>
                        <span class="rounded-full border border-slate-200 bg-white px-2.5 py-0.5 text-xs font-semibold text-slate-700">
                            {{ $deliveryLabels[$order->delivery->delivery_status] ?? 'Status diperbarui' }}
                        </span>
                    </div>

                    <div class="space-y-4 p-6">
                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3.5">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">Metode layanan</p>
                            <p class="mt-0.5 text-sm font-bold text-slate-900">
                                {{ $order->delivery->delivery_method === 'pickup' ? 'Ambil langsung dari penjual' : 'Diantar oleh penjual' }}
                            </p>
                        </div>

                        <dl class="space-y-3 text-xs sm:text-sm">
                            <div>
                                <dt class="text-xs font-medium text-slate-400">Penerima</dt>
                                <dd class="mt-0.5 font-semibold text-slate-900 break-words">
                                    {{ $order->delivery->recipient_name }}
                                    <span class="mx-1 font-normal text-slate-300">·</span>
                                    <span class="font-mono text-xs font-normal text-slate-600">{{ $order->delivery->recipient_phone }}</span>
                                </dd>
                            </div>

                            <div>
                                <dt class="text-xs font-medium text-slate-400">Alamat tujuan</dt>
                                <dd class="mt-0.5 whitespace-pre-line break-words leading-relaxed text-slate-700">
                                    {{ $order->delivery->delivery_address ?: 'Pengambilan langsung; tidak memerlukan alamat pengantaran.' }}
                                </dd>
                            </div>

                            @if($order->delivery->delivery_reference)
                                <div>
                                    <dt class="text-xs font-medium text-slate-400">Referensi pengiriman</dt>
                                    <dd class="mt-0.5 font-mono text-xs font-semibold text-slate-800 break-words">
                                        {{ $order->delivery->delivery_reference }}
                                    </dd>
                                </div>
                            @endif
                        </dl>

                        {{-- Zona Aksi Penjual & Pembeli --}}
                        @php
                            $hasAction = ($viewerIsSeller && in_array($order->status, ['pending_seller', 'accepted', 'processing'], true))
                                || ($viewerIsSeller && in_array($order->delivery->delivery_status, ['ready', 'failed', 'in_delivery'], true))
                                || (! $viewerIsSeller && $order->status === 'delivered' && $order->delivery->delivery_status === 'delivered')
                                || (! $viewerIsSeller && in_array($order->status, ['pending_seller', 'awaiting_payment'], true) && $order->payment?->status !== 'paid');
                        @endphp

                        @if($hasAction)
                            <div class="space-y-3 border-t border-slate-100 pt-4">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Tindakan diperlukan</p>

                                {{-- Penjual: Terima atau Tolak Pesanan --}}
                                @if($viewerIsSeller && $order->status === 'pending_seller')
                                    <form method="POST" action="{{ route('marketplace.seller.orders.decision', $order) }}">
                                        @csrf
                                        <input type="hidden" name="decision" value="accepted">
                                        <button
                                            type="submit"
                                            class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                                        >
                                            Terima pesanan
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('marketplace.seller.orders.decision', $order) }}" class="space-y-2 rounded-xl border border-slate-200/80 bg-slate-50/70 p-3" data-confirm="Tolak pesanan ini?">
                                        @csrf
                                        <input type="hidden" name="decision" value="rejected">
                                        <label class="block text-xs font-medium text-slate-600" for="reject-{{ $order->id }}">
                                            Atau tolak pesanan dengan alasan:
                                        </label>
                                        <div class="flex gap-2">
                                            <input
                                                id="reject-{{ $order->id }}"
                                                name="reason"
                                                required
                                                minlength="5"
                                                maxlength="1000"
                                                placeholder="Alasan penolakan…"
                                                class="min-h-10 min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-900 placeholder:text-slate-400 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/15 focus:outline-none"
                                            >
                                            <button
                                                type="submit"
                                                class="inline-flex min-h-10 shrink-0 cursor-pointer items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-3.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-600"
                                            >
                                                Tolak
                                            </button>
                                        </div>
                                    </form>
                                @endif

                                {{-- Penjual: Lanjutkan Proses Pesanan --}}
                                @if($viewerIsSeller && $order->status === 'accepted')
                                    <form method="POST" action="{{ route('marketplace.seller.orders.advance', $order) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="processing">
                                        <button
                                            type="submit"
                                            class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                                        >
                                            Mulai menyiapkan pesanan
                                        </button>
                                    </form>
                                @elseif($viewerIsSeller && $order->status === 'processing')
                                    <form method="POST" action="{{ route('marketplace.seller.orders.advance', $order) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="ready_for_delivery">
                                        <button
                                            type="submit"
                                            class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                                        >
                                            Tandai siap diserahkan
                                        </button>
                                    </form>
                                @endif

                                {{-- Penjual: Pembaruan Status Pengiriman --}}
                                @if($viewerIsSeller && in_array($order->delivery->delivery_status, ['ready', 'failed'], true))
                                    <form method="POST" action="{{ route('marketplace.seller.orders.delivery', $order) }}" class="space-y-3 rounded-xl border border-slate-200/80 bg-slate-50/70 p-3.5">
                                        @csrf
                                        <div>
                                            <label for="delivery-status-{{ $order->id }}" class="block text-xs font-semibold text-slate-700">
                                                Perbarui status pengiriman
                                            </label>
                                            <select
                                                id="delivery-status-{{ $order->id }}"
                                                name="status"
                                                class="mt-1.5 min-h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs font-medium text-slate-900 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                            >
                                                @if($order->delivery->delivery_method === 'pickup')
                                                    <option value="delivered">Diserahkan saat diambil</option>
                                                @else
                                                    <option value="in_delivery">Diserahkan ke pengantar</option>
                                                @endif
                                                @if($order->delivery->delivery_status === 'ready')
                                                    <option value="failed">Pengantaran terkendala</option>
                                                @endif
                                            </select>
                                        </div>

                                        <div>
                                            <label for="delivery-ref-{{ $order->id }}" class="block text-xs font-semibold text-slate-700">
                                                Nomor referensi / kurir <span class="font-normal text-slate-400">(opsional)</span>
                                            </label>
                                            <input
                                                id="delivery-ref-{{ $order->id }}"
                                                name="reference"
                                                maxlength="100"
                                                placeholder="Contoh: Nama kurir atau kode resi…"
                                                class="mt-1.5 min-h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                            >
                                        </div>

                                        <button
                                            type="submit"
                                            class="inline-flex min-h-10 w-full cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                                        >
                                            Simpan status pengiriman
                                        </button>
                                    </form>
                                @elseif($viewerIsSeller && $order->delivery->delivery_status === 'in_delivery')
                                    <form method="POST" action="{{ route('marketplace.seller.orders.delivery', $order) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="delivered">
                                        <button
                                            type="submit"
                                            class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                                        >
                                            Tandai sudah diserahkan
                                        </button>
                                    </form>
                                @endif

                                {{-- Pembeli: Konfirmasi Barang Diterima --}}
                                @if(! $viewerIsSeller && $order->status === 'delivered' && $order->delivery->delivery_status === 'delivered')
                                    <form method="POST" action="{{ route('marketplace.orders.receipt', $order) }}" data-confirm="Konfirmasi barang sudah diterima dan selesaikan pesanan?">
                                        @csrf
                                        <button
                                            type="submit"
                                            class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                                        >
                                            Konfirmasi barang diterima
                                        </button>
                                    </form>
                                @endif

                                {{-- Pembeli: Batalkan Pesanan --}}
                                @if(! $viewerIsSeller && in_array($order->status, ['pending_seller', 'awaiting_payment'], true) && $order->payment?->status !== 'paid')
                                    <form method="POST" action="{{ route('marketplace.orders.cancel', $order) }}" class="space-y-2 rounded-xl border border-slate-200/80 bg-slate-50/70 p-3" data-confirm="Batalkan pesanan ini?">
                                        @csrf
                                        <label class="block text-xs font-medium text-slate-600" for="cancel-{{ $order->id }}">
                                            Batalkan pesanan:
                                        </label>
                                        <div class="flex gap-2">
                                            <input
                                                id="cancel-{{ $order->id }}"
                                                name="reason"
                                                required
                                                minlength="5"
                                                maxlength="1000"
                                                placeholder="Alasan pembatalan…"
                                                class="min-h-10 min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-3 text-xs text-slate-900 placeholder:text-slate-400 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/15 focus:outline-none"
                                            >
                                            <button
                                                type="submit"
                                                class="inline-flex min-h-10 shrink-0 cursor-pointer items-center justify-center rounded-xl border border-rose-200 bg-rose-50 px-3.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-600"
                                            >
                                                Batalkan
                                            </button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                </article>

                {{-- Kartu Informasi Pembayaran --}}
                <article class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <h2 class="text-base font-bold text-slate-900">Pembayaran</h2>
                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $paymentBadgeClass }}">
                            <span class="size-1.5 rounded-full {{ $paymentDotClass }}" aria-hidden="true"></span>
                            <span>{{ $paymentLabels[$paymentStatus] ?? 'Status pembayaran' }}</span>
                        </span>
                    </div>

                    <div class="space-y-4 p-6">
                        <div>
                            <p class="text-sm font-bold text-slate-900">
                                {{ $checkoutPayment?->payment_provider === 'xendit' ? 'Xendit · Satu pembayaran checkout' : 'Tunai saat barang diterima (COD)' }}
                            </p>
                            <p class="mt-1.5 text-xs leading-relaxed text-slate-500">
                                {{ $checkoutPayment?->payment_provider === 'xendit'
                                    ? 'Pembayaran mencakup seluruh pesanan pada checkout. Status lunas berasal dari notifikasi Xendit terverifikasi; pesanan diteruskan setelah pembayaran terkonfirmasi.'
                                    : 'Pembeli tidak dapat mengubah status pembayaran. COD dicatat lunas hanya setelah penerimaan dan uang dikonfirmasi pihak berwenang.' }}
                            </p>
                        </div>

                        {{-- Tombol Bayar Xendit (Pembeli) --}}
                        @if(! $viewerIsSeller && $checkoutPayment?->payment_provider === 'xendit' && $checkoutPayment?->status === 'pending' && $order->status === 'awaiting_payment')
                            <form method="POST" action="{{ route('marketplace.orders.group.payment-session', $order->group) }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                                >
                                    {{ $checkoutPayment->checkout_url ? 'Lanjutkan pembayaran checkout' : 'Bayar seluruh checkout dengan Xendit' }}
                                </button>
                            </form>
                        @endif

                        {{-- Alokasi Transfer Xendit (Penjual) --}}
                        @if($checkoutPayment?->payment_provider === 'xendit' && $sellerAllocation)
                            <dl class="space-y-2 rounded-xl border border-slate-100 bg-slate-50/70 p-3.5 text-xs">
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-slate-500">Alokasi ke usaha ini</dt>
                                    <dd class="font-bold text-slate-900 tabular-nums">Rp{{ number_format((float) $sellerAllocation->amount, 0, ',', '.') }}</dd>
                                </div>
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-slate-500">Status transfer Xendit</dt>
                                    <dd class="font-semibold {{ $sellerAllocation->status === 'completed' ? 'text-emerald-700' : ($sellerAllocation->status === 'failed' ? 'text-rose-600' : 'text-amber-700') }}">
                                        {{ $sellerAllocation->status === 'completed' ? 'Dikonfirmasi' : ($sellerAllocation->status === 'failed' ? 'Gagal · perlu rekonsiliasi' : 'Menunggu konfirmasi') }}
                                    </dd>
                                </div>
                            </dl>
                        @endif

                        {{-- Konfirmasi Uang COD (Penjual / BUMDes) --}}
                        @if($order->payment?->payment_provider === 'cod' && $order->payment?->status === 'pending' && $order->delivery?->delivery_status === 'confirmed' && ($viewerIsSeller || auth()->user()->hasRole('BUMDes Manager') || auth()->user()->hasRole('BUMDes Operator')))
                            <form method="POST" action="{{ route('marketplace.seller.payments.cod-confirmation', $order->payment) }}" data-confirm="Pastikan uang COD sudah diterima sebelum mengonfirmasi.">
                                @csrf
                                <button
                                    type="submit"
                                    class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                                >
                                    Konfirmasi uang COD diterima
                                </button>
                            </form>
                        @endif

                        {{-- Riwayat Event Pembayaran --}}
                        <div class="border-t border-slate-100 pt-4">
                            <h3 class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Riwayat pembayaran</h3>
                            <ol class="mt-2.5 space-y-2" aria-label="Riwayat pembayaran">
                                @forelse($checkoutPayment?->events ?? [] as $paymentEvent)
                                    <li class="flex items-baseline justify-between gap-2 text-xs">
                                        <span class="font-medium text-slate-700">{{ str_replace(['.', '_'], ' ', ucfirst($paymentEvent->event_type)) }}</span>
                                        <span class="shrink-0 text-slate-400 tabular-nums">{{ $paymentEvent->received_at?->format('d/m/Y H:i') }}</span>
                                    </li>
                                @empty
                                    <li class="text-xs text-slate-500">Belum ada pembaruan pembayaran.</li>
                                @endforelse
                            </ol>
                        </div>
                    </div>
                </article>
            </div>
        </div>
    </main>
</x-layouts.app>
