<x-layouts.app title="Pesanan {{ $order->order_number }}">
    @php
        $statusLabels = [
            'pending_seller' => 'Menunggu penjual', 'accepted' => 'Diterima penjual', 'rejected' => 'Ditolak',
            'processing' => 'Sedang disiapkan', 'ready_for_delivery' => 'Siap diserahkan', 'in_delivery' => 'Dalam pengantaran',
            'delivered' => 'Sudah diserahkan', 'delivery_failed' => 'Pengantaran terkendala', 'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan', 'awaiting_payment' => 'Menunggu pembayaran',
        ];
        $deliveryLabels = ['preparing' => 'Disiapkan', 'ready' => 'Siap diserahkan', 'in_delivery' => 'Dalam pengantaran', 'delivered' => 'Diserahkan', 'failed' => 'Ada kendala', 'confirmed' => 'Diterima'];
        $paymentLabels = ['pending' => 'Belum dibayar', 'paid' => 'Lunas tercatat', 'cancelled' => 'Tidak ditagihkan', 'failed' => 'Gagal'];
        $checkoutPayment = $order->group?->payment ?? $order->payment;
        $sellerAllocation = $order->group?->payment?->allocations?->firstWhere('order_id', $order->id);
    @endphp
    <main id="main" class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-8">
        <a href="{{ $viewerIsSeller ? route('marketplace.seller.orders') : route('marketplace.orders.index') }}" class="inline-flex min-h-11 items-center font-medium text-brand-strong underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">Kembali ke {{ $viewerIsSeller ? 'pesanan toko' : 'pesanan saya' }}</a>
        <header class="flex flex-wrap items-end justify-between gap-4">
            <div class="min-w-0">
                <p class="text-sm text-muted">{{ $viewerIsSeller ? 'Pesanan dari pembeli' : 'Pesanan dari penjual' }}</p>
                <h1 class="mt-1 break-all text-2xl font-bold tracking-tight sm:text-3xl">{{ $order->order_number }}</h1>
                <p class="mt-2 text-base text-muted">{{ $viewerIsSeller ? $order->buyer->username : $order->seller->business_name }}</p>
            </div>
            <span class="inline-flex min-h-11 items-center rounded-control border border-line bg-paper px-4 font-semibold">{{ $statusLabels[$order->status] ?? 'Status diperbarui' }}</span>
        </header>

        @unless($viewerIsSeller)
            <section class="flex flex-wrap items-center justify-between gap-4 rounded-card border border-line bg-paper p-5" aria-label="Struk pembelian">
                <div class="min-w-0"><h2 class="font-semibold">{{ $groupIsFinal ? 'Pembelian selesai' : 'Bukti checkout' }}</h2><p class="mt-1 text-sm text-muted">{{ $groupIsFinal ? 'Semua pesanan penjual selesai dan pembayarannya telah dikonfirmasi.' : 'Struk final tersedia setelah semua pesanan selesai dan pembayaran dikonfirmasi.' }}</p></div>
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('marketplace.orders.group', $order->group) }}" class="inline-flex min-h-11 items-center rounded-control border border-line bg-paper px-4 font-semibold focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">Ringkasan checkout</a>
                    <a href="{{ route('marketplace.orders.group.receipt', $order->group) }}" class="inline-flex min-h-11 items-center rounded-control bg-brand-strong px-4 font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand">{{ $groupIsFinal ? 'Buka struk final' : 'Buka bukti checkout' }}</a>
                </div>
            </section>
        @endunless

        <section class="grid gap-4 lg:grid-cols-[1.2fr_.8fr]">
            <article class="rounded-card border border-line bg-paper p-5 sm:p-6">
                <h2 class="text-lg font-semibold">Rincian barang</h2>
                <div class="mt-3 divide-y divide-line">
                    @foreach($order->items as $item)
                        <div class="flex flex-wrap items-start justify-between gap-4 py-4">
                            <div class="min-w-0"><h3 class="break-words font-medium">{{ $item->product_name_snapshot }}</h3><p class="mt-1 text-sm text-muted">{{ number_format($item->quantity, 0, ',', '.') }} × Rp{{ number_format((float) $item->unit_price, 0, ',', '.') }} per {{ data_get($item->attributes_snapshot, 'unit', 'item') }}</p></div>
                            <p class="shrink-0 font-semibold tabular-nums">Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}</p>
                        </div>
                    @endforeach
                </div>
                <p class="mt-2 flex justify-between gap-4 border-t-2 border-ink pt-4 text-lg font-bold"><span>Total pesanan</span><span class="shrink-0 tabular-nums">Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}</span></p>
            </article>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                <article class="rounded-card border border-line bg-paper p-5 sm:p-6">
                    <h2 class="text-lg font-semibold">Pembayaran</h2>
                    <p class="mt-2 font-medium">{{ $checkoutPayment?->payment_provider === 'xendit' ? 'Xendit · satu pembayaran checkout' : 'Tunai saat barang diterima (COD)' }}</p>
                    <p class="mt-1 text-sm">{{ $paymentLabels[$checkoutPayment?->status ?? 'pending'] ?? 'Status pembayaran' }}</p>
                    <p class="mt-2 text-sm leading-6 text-muted">{{ $checkoutPayment?->payment_provider === 'xendit' ? 'Pembayaran mencakup seluruh pesanan pada checkout. Status lunas berasal dari notifikasi Xendit terverifikasi; pesanan diteruskan setelah pembayaran terkonfirmasi.' : 'Pembeli tidak dapat mengubah status pembayaran. COD dicatat lunas hanya setelah penerimaan dan uang dikonfirmasi pihak berwenang.' }}</p>
                    @if(! $viewerIsSeller && $checkoutPayment?->payment_provider === 'xendit' && $checkoutPayment?->status === 'pending' && $order->status === 'awaiting_payment')
                        <form method="POST" action="{{ route('marketplace.orders.group.payment-session', $order->group) }}" class="mt-4">@csrf<x-ui.button type="submit">{{ $checkoutPayment->checkout_url ? 'Lanjutkan pembayaran checkout' : 'Bayar seluruh checkout dengan Xendit' }}</x-ui.button></form>
                    @endif
                    @if($checkoutPayment?->payment_provider === 'xendit' && $sellerAllocation)
                        <dl class="mt-4 space-y-1 border-t border-line pt-3 text-sm"><div class="flex justify-between gap-3"><dt>Alokasi ke usaha ini</dt><dd class="font-semibold tabular-nums">Rp{{ number_format((float) $sellerAllocation->amount, 0, ',', '.') }}</dd></div><div class="flex justify-between gap-3"><dt>Status transfer Xendit</dt><dd>{{ $sellerAllocation->status === 'completed' ? 'Dikonfirmasi' : ($sellerAllocation->status === 'failed' ? 'Gagal · perlu rekonsiliasi' : 'Menunggu konfirmasi') }}</dd></div></dl>
                    @endif
                    @if($order->payment?->payment_provider === 'cod' && $order->payment?->status === 'pending' && $order->delivery?->delivery_status === 'confirmed' && ($viewerIsSeller || auth()->user()->hasRole('BUMDes Manager') || auth()->user()->hasRole('BUMDes Operator')))
                        <form method="POST" action="{{ route('marketplace.seller.payments.cod-confirmation', $order->payment) }}" class="mt-4" data-confirm="Pastikan uang COD sudah diterima sebelum mengonfirmasi.">@csrf<x-ui.button type="submit">Konfirmasi uang diterima</x-ui.button></form>
                    @endif
                    <ol class="mt-4 space-y-2 border-t border-line pt-3" aria-label="Riwayat pembayaran">
                        @forelse($checkoutPayment?->events ?? [] as $paymentEvent)
                            <li class="text-sm">{{ str_replace(['.', '_'], ' ', ucfirst($paymentEvent->event_type)) }} <span class="text-muted">· {{ $paymentEvent->received_at?->format('d/m/Y H:i') }}</span></li>
                        @empty
                            <li class="text-sm text-muted">Belum ada pembaruan pembayaran.</li>
                        @endforelse
                    </ol>
                </article>

                <article class="rounded-card border border-line bg-paper p-5 sm:p-6">
                    <h2 class="text-lg font-semibold">Pengiriman atau pengambilan</h2>
                    <p class="mt-2 font-medium">{{ $order->delivery->delivery_method === 'pickup' ? 'Ambil dari penjual' : 'Diantar oleh penjual' }} · {{ $deliveryLabels[$order->delivery->delivery_status] ?? 'Status diperbarui' }}</p>
                    <p class="mt-3 whitespace-pre-line break-words text-sm">{{ $order->delivery->delivery_address ?: 'Pengambilan langsung; tidak perlu alamat pengantaran.' }}</p>
                    <p class="mt-2 break-words text-sm">Penerima: {{ $order->delivery->recipient_name }} · {{ $order->delivery->recipient_phone }}</p>
                    @if($order->delivery->delivery_reference)<p class="mt-2 break-words text-sm">Referensi pengiriman: {{ $order->delivery->delivery_reference }}</p>@endif

                    @if($viewerIsSeller && $order->status === 'pending_seller')
                        <div class="mt-5 flex flex-wrap gap-2"><form method="POST" action="{{ route('marketplace.seller.orders.decision', $order) }}">@csrf<input type="hidden" name="decision" value="accepted"><x-ui.button type="submit">Terima pesanan</x-ui.button></form><form method="POST" action="{{ route('marketplace.seller.orders.decision', $order) }}" class="flex flex-wrap gap-2" data-confirm="Tolak pesanan ini?">@csrf<input type="hidden" name="decision" value="rejected"><label class="sr-only" for="reject-{{ $order->id }}">Alasan penolakan</label><input id="reject-{{ $order->id }}" name="reason" required minlength="5" maxlength="1000" placeholder="Alasan penolakan…" class="min-h-11 min-w-0 rounded-control border border-line px-3"><x-ui.button type="submit" variant="secondary">Tolak</x-ui.button></form></div>
                    @endif
                    @if($viewerIsSeller && $order->status === 'accepted')<form method="POST" action="{{ route('marketplace.seller.orders.advance', $order) }}" class="mt-4">@csrf<input type="hidden" name="status" value="processing"><x-ui.button type="submit">Mulai menyiapkan</x-ui.button></form>@elseif($viewerIsSeller && $order->status === 'processing')<form method="POST" action="{{ route('marketplace.seller.orders.advance', $order) }}" class="mt-4">@csrf<input type="hidden" name="status" value="ready_for_delivery"><x-ui.button type="submit">Tandai siap diserahkan</x-ui.button></form>@endif
                    @if($viewerIsSeller && in_array($order->delivery->delivery_status, ['ready', 'failed'], true))
                        <form method="POST" action="{{ route('marketplace.seller.orders.delivery', $order) }}" class="mt-4 flex flex-wrap items-end gap-2">
                            @csrf
                            <label class="min-w-0">Status
                                <select name="status" class="mt-1 min-h-11 max-w-full rounded-control border border-line bg-paper px-3">
                                    @if($order->delivery->delivery_method === 'pickup')
                                        <option value="delivered">Diserahkan saat diambil</option>
                                    @else
                                        <option value="in_delivery">Diserahkan ke pengantar</option>
                                    @endif
                                    @if($order->delivery->delivery_status === 'ready')
                                        <option value="failed">Pengantaran terkendala</option>
                                    @endif
                                </select>
                            </label>
                            <label class="min-w-0">Nomor referensi
                                <input name="reference" maxlength="100" class="mt-1 min-h-11 max-w-full rounded-control border border-line px-3" placeholder="Opsional…">
                            </label>
                            <x-ui.button type="submit">Simpan status</x-ui.button>
                        </form>
                    @elseif($viewerIsSeller && $order->delivery->delivery_status === 'in_delivery')
                        <form method="POST" action="{{ route('marketplace.seller.orders.delivery', $order) }}" class="mt-4">@csrf<input type="hidden" name="status" value="delivered"><x-ui.button type="submit">Tandai sudah diserahkan</x-ui.button></form>
                    @endif
                    @if(! $viewerIsSeller && $order->status === 'delivered' && $order->delivery->delivery_status === 'delivered')<form method="POST" action="{{ route('marketplace.orders.receipt', $order) }}" class="mt-5" data-confirm="Konfirmasi barang sudah diterima dan selesaikan pesanan?">@csrf<x-ui.button type="submit">Konfirmasi barang diterima</x-ui.button></form>@endif
                    @if(! $viewerIsSeller && in_array($order->status, ['pending_seller', 'awaiting_payment'], true) && $order->payment?->status !== 'paid')<form method="POST" action="{{ route('marketplace.orders.cancel', $order) }}" class="mt-5 flex flex-wrap gap-2" data-confirm="Batalkan pesanan ini?">@csrf<label class="sr-only" for="cancel-{{ $order->id }}">Alasan pembatalan</label><input id="cancel-{{ $order->id }}" name="reason" required minlength="5" maxlength="1000" placeholder="Alasan pembatalan…" class="min-h-11 min-w-0 rounded-control border border-line px-3"><x-ui.button type="submit" variant="secondary">Batalkan pesanan</x-ui.button></form>@endif
                </article>
            </div>
        </section>

        <section class="rounded-card border border-line bg-paper p-5 sm:p-6" aria-labelledby="history-title">
            <h2 id="history-title" class="text-lg font-semibold">Riwayat pesanan</h2>
            <ol class="mt-4 space-y-3 border-l border-line pl-4">
                @foreach($order->statusEvents as $event)
                    <li class="relative text-sm leading-6"><span class="font-semibold">{{ $event->from_status ? ($statusLabels[$event->from_status] ?? 'Status sebelumnya').' → ' : '' }}{{ $statusLabels[$event->to_status] ?? 'Status diperbarui' }}</span><span class="text-muted"> · {{ $event->created_at->format('d/m/Y H:i') }}</span>@if($event->reason)<p class="mt-1 break-words text-muted">{{ $event->reason }}</p>@endif</li>
                @endforeach
            </ol>
            <h3 class="mt-6 border-t border-line pt-4 font-semibold">Riwayat pengantaran</h3>
            <ol class="mt-3 space-y-3">
                @forelse($order->delivery->events as $event)
                    <li class="text-sm leading-6"><span class="font-semibold">{{ $event->from_status ? ($deliveryLabels[$event->from_status] ?? 'Status sebelumnya').' → ' : '' }}{{ $deliveryLabels[$event->to_status] ?? 'Status diperbarui' }}</span><span class="text-muted"> · {{ $event->created_at->format('d/m/Y H:i') }}</span>@if($event->note)<p class="mt-1 break-words text-muted">{{ $event->note }}</p>@endif</li>
                @empty
                    <li class="text-sm text-muted">Belum ada pembaruan pengantaran.</li>
                @endforelse
            </ol>
        </section>
    </main>
</x-layouts.app>
