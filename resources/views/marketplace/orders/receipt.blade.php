<x-layouts.app title="{{ $isFinal ? 'Struk final' : 'Bukti checkout' }} {{ $group->checkout_number }}">
    <style>
        @media print {
            @page { size: A4 portrait; margin: 12mm; }
            html, body { background: #fff !important; color: #0A192F !important; }
            body > a[href="#main"], body > div > aside, body > div > div > header, body > dialog,
            [data-receipt-actions] { display: none !important; }
            [data-receipt-main] { max-width: none !important; margin: 0 !important; padding: 0 !important; }
            [data-receipt-paper] { max-width: none !important; border: 0 !important; padding: 0 !important; box-shadow: none !important; }
            [data-receipt-section], table, tr { break-inside: avoid; }
            a { color: inherit !important; text-decoration: none !important; }
            thead { display: table-header-group; }
        }
    </style>
    @php($delivery = $group->orders->first()?->delivery)
    @php($checkoutPayment = $group->payment)
    @php($codPaymentStatus = $group->orders->every(fn ($order) => $order->payment?->status === 'paid') ? 'Lunas tercatat' : 'Menunggu konfirmasi')
    <main id="main" data-receipt-main class="mx-auto max-w-5xl space-y-4 px-4 py-8 sm:px-8">
        <nav data-receipt-actions class="flex flex-wrap items-center justify-between gap-3" aria-label="Aksi struk">
            <a href="{{ route('marketplace.orders.group', $group) }}" class="inline-flex min-h-11 items-center underline">Kembali ke pesanan</a>
            <button type="button" onclick="window.print()" class="inline-flex min-h-11 items-center rounded-control bg-brand-strong px-4 font-semibold text-white">Cetak atau simpan PDF</button>
        </nav>

        <article data-receipt-paper class="mx-auto max-w-4xl rounded-card border border-line bg-paper p-5 shadow-sm sm:p-9" aria-labelledby="receipt-title">
            <header class="grid gap-6 border-b-2 border-ink pb-5 sm:grid-cols-[1fr_auto] sm:items-start">
                <div class="flex items-start gap-3">
                    <span class="grid size-14 shrink-0 place-items-center rounded-control border border-brand bg-success-bg text-brand-strong" aria-hidden="true">
                        <svg class="size-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="m3 10 9-6.5 9 6.5M5.5 9.5V20h13V9.5M10 20v-6h4v6"/><path d="M8 11h.01M16 11h.01"/></svg>
                    </span>
                    <div class="min-w-0">
                        <p class="text-lg font-bold" translate="no">{{ config('app.name') }}</p>
                        <p class="mt-1 text-sm text-muted">Pasar desa · {{ $isFinal ? 'Bukti pembelian' : 'Informasi checkout' }}</p>
                    </div>
                </div>
                <div class="sm:text-right">
                    <h1 id="receipt-title" class="text-2xl font-bold tracking-tight sm:text-3xl">{{ $isFinal ? 'Struk final pembelian' : 'Bukti checkout' }}</h1>
                    <p class="mt-1 text-sm font-semibold {{ $isFinal ? 'text-success-ink' : 'text-warning-ink' }}">{{ $isFinal ? 'Pesanan selesai · pembayaran dikonfirmasi' : 'Pesanan belum selesai · bukan bukti pembayaran' }}</p>
                </div>
            </header>

            <section data-receipt-section class="grid gap-x-10 gap-y-4 border-b border-line py-5 sm:grid-cols-2" aria-label="Informasi checkout">
                <dl class="grid grid-cols-[auto_1fr] gap-x-4 gap-y-2 text-sm">
                    <dt class="font-semibold">Tanggal</dt><dd>{{ $group->created_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                    <dt class="font-semibold">Nomor checkout</dt><dd class="break-all font-mono">{{ $group->checkout_number }}</dd>
                    <dt class="font-semibold">Pembayaran</dt><dd>{{ $checkoutPayment?->payment_provider === 'xendit' ? 'Xendit · '.$checkoutPayment->status : 'COD · '.$codPaymentStatus }}</dd>
                </dl>
                <div class="text-sm sm:text-right">
                    <p class="font-semibold">Pemesan</p>
                    <p class="mt-1 break-words">{{ $group->buyer->username }}</p>
                    <p class="mt-1 text-muted">{{ $group->orders->count() }} pesanan penjual</p>
                </div>
            </section>

            @if($checkoutPayment?->payment_provider === 'xendit' && $checkoutPayment->allocations->isNotEmpty())
                <section data-receipt-section class="border-b border-line py-5" aria-labelledby="receipt-allocation-title">
                    <h2 id="receipt-allocation-title" class="mb-3 text-lg font-semibold">Pembagian pembayaran Xendit</h2>
                    <table class="w-full border-collapse text-left text-sm">
                        <thead><tr class="bg-ink text-white"><th scope="col" class="border border-ink px-2 py-2">Penjual</th><th scope="col" class="border border-ink px-2 py-2 text-right">Alokasi</th><th scope="col" class="border border-ink px-2 py-2">Status transfer</th></tr></thead>
                        <tbody>
                            @foreach($group->orders as $order)
                                @php($allocation = $checkoutPayment->allocations->firstWhere('order_id', $order->id))
                                <tr><th scope="row" class="border border-line px-2 py-2 font-medium">{{ $order->seller->business_name }}</th><td class="border border-line px-2 py-2 text-right tabular-nums">{{ $allocation ? 'Rp'.number_format((float) $allocation->amount, 0, ',', '.') : '—' }}</td><td class="border border-line px-2 py-2">{{ $allocation?->status === 'completed' ? 'Dikonfirmasi Xendit' : ($allocation?->status === 'failed' ? 'Gagal · perlu rekonsiliasi' : 'Menunggu konfirmasi') }}</td></tr>
                            @endforeach
                        </tbody>
                        <tfoot><tr><th scope="row" colspan="2" class="border border-line px-2 py-2 text-right">Saldo platform sebelum biaya Xendit</th><td class="border border-line px-2 py-2 text-right font-semibold tabular-nums">Rp{{ number_format((float) $checkoutPayment->platform_retained_amount, 0, ',', '.') }}</td></tr></tfoot>
                    </table>
                    <p class="mt-2 text-xs leading-5 text-muted">Angka alokasi adalah nilai rute split yang diminta dan status transfer dari notifikasi Xendit. Biaya Xendit dapat mengurangi saldo platform; cocokkan saldo bersih melalui dasbor XenPlatform.</p>
                </section>
            @endif

            <section data-receipt-section class="grid gap-6 border-b border-line py-5 sm:grid-cols-2" aria-label="Penjual dan penerima">
                <div>
                    <h2 class="bg-ink px-3 py-2 text-sm font-semibold text-white">Penjual</h2>
                    <ul class="mt-3 space-y-3 text-sm">
                        @foreach($group->orders as $order)
                            <li class="break-words"><span class="font-semibold">{{ $order->seller->business_name }}</span><span class="block text-muted">Pesanan {{ $order->order_number }}</span></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h2 class="bg-ink px-3 py-2 text-sm font-semibold text-white">Tujuan pesanan</h2>
                    <p class="mt-3 text-sm font-semibold">{{ $delivery?->recipient_name ?: $group->buyer->username }}</p>
                    @if($delivery?->delivery_method === 'delivery')
                        <p class="mt-1 whitespace-pre-line break-words text-sm">{{ $delivery->delivery_address }}</p>
                        <p class="mt-1 break-words text-sm">{{ $delivery->recipient_phone }}</p>
                        <p class="mt-2 text-sm text-muted">Metode: Diantar oleh penjual</p>
                    @else
                        <p class="mt-1 text-sm">{{ $delivery?->recipient_phone }}</p>
                        <p class="mt-2 text-sm text-muted">Metode: Ambil langsung dari penjual</p>
                    @endif
                </div>
            </section>

            <section data-receipt-section class="py-5" aria-labelledby="receipt-items-title">
                <h2 id="receipt-items-title" class="mb-3 text-lg font-semibold">Rincian barang</h2>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse text-left text-sm">
                        <thead>
                            <tr class="bg-ink text-white">
                                <th scope="col" class="border border-ink px-2 py-2">Barang</th>
                                <th scope="col" class="border border-ink px-2 py-2 text-right">Jumlah</th>
                                <th scope="col" class="border border-ink px-2 py-2 text-right">Harga satuan</th>
                                <th scope="col" class="border border-ink px-2 py-2 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group->orders as $order)
                                <tr class="bg-slate-100"><th colspan="4" scope="colgroup" class="border border-line px-2 py-2 text-left">{{ $order->seller->business_name }} · {{ $order->order_number }}</th></tr>
                                @foreach($order->items as $item)
                                    <tr>
                                        <th scope="row" class="max-w-[18rem] break-words border border-line px-2 py-2 font-medium">{{ $item->product_name_snapshot }}<span class="block text-xs font-normal text-muted">{{ data_get($item->attributes_snapshot, 'unit', 'item') }}</span></th>
                                        <td class="border border-line px-2 py-2 text-right tabular-nums">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                                        <td class="border border-line px-2 py-2 text-right tabular-nums">Rp{{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                                        <td class="border border-line px-2 py-2 text-right tabular-nums">Rp{{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                                <tr><th colspan="3" scope="row" class="border border-line px-2 py-2 text-right font-medium">Subtotal pesanan penjual</th><td class="border border-line px-2 py-2 text-right font-semibold tabular-nums">Rp{{ number_format((float) $order->total_amount, 0, ',', '.') }}</td></tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-200"><th colspan="3" scope="row" class="border border-line px-2 py-3 text-right text-base font-bold">Total checkout</th><td class="border border-line px-2 py-3 text-right text-base font-bold tabular-nums">Rp{{ number_format((float) $group->grand_total, 0, ',', '.') }}</td></tr>
                        </tfoot>
                    </table>
                </div>
            </section>

            <footer class="border-t border-line pt-4 text-sm">
                <p class="font-semibold">Status transaksi</p>
                <ul class="mt-2 space-y-1">
                    @foreach($group->orders as $order)
                        <li class="break-words">{{ $order->seller->business_name }}: {{ str_replace('_', ' ', ucfirst($order->status)) }} · {{ $checkoutPayment?->payment_provider === 'xendit' ? 'Xendit '.$checkoutPayment->status : 'COD '.($order->payment?->status === 'paid' ? 'lunas tercatat' : 'belum dibayar') }}</li>
                    @endforeach
                </ul>
                <p class="mt-4 border-t border-dashed border-line pt-3 text-sm leading-6 text-muted">{{ $isFinal ? 'Semua pesanan dalam checkout ini telah diselesaikan dan pembayaran telah dikonfirmasi oleh pihak berwenang. Simpan struk ini sebagai catatan pembelian.' : 'Dokumen ini mencatat barang dan nilai saat checkout. Status akan menjadi struk final setelah semua penjual menyelesaikan pesanan dan pembayaran dikonfirmasi. Biaya di luar aplikasi tidak tercantum.' }}</p>
            </footer>
        </article>
    </main>
</x-layouts.app>
