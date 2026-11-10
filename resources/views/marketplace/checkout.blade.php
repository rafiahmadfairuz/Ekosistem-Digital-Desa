<x-layouts.app title="Checkout">
    <main id="main" class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-8"><x-ui.page-heading title="Konfirmasi pesanan" description="Periksa barang dan alamat. Satu checkout membuat pesanan terpisah untuk setiap penjual." />
        <section class="rounded-card border border-line bg-paper p-5"><h2 class="text-lg font-semibold">Ringkasan checkout</h2>
            @php($estimatedTotal = 0)
            @foreach($cart->items->groupBy(fn($item) => $item->product->seller_id) as $sellerItems)
                @php($sellerTotal = 0)
                <div class="mt-4 border-t border-line pt-3"><h3 class="font-semibold">{{ $sellerItems->first()->product->seller->business_name }}</h3><div class="divide-y divide-line">@foreach($sellerItems as $item)@php($lineTotal = (float) $item->product->price * $item->quantity)@php($sellerTotal += $lineTotal)<div class="flex justify-between gap-4 py-3"><span class="min-w-0 break-words">{{ $item->product->name }} × {{ $item->quantity }}</span><span class="shrink-0 tabular-nums">Rp{{ number_format($lineTotal, 0, ',', '.') }}</span></div>@endforeach</div><p class="text-right text-sm font-medium">Subtotal penjual Rp{{ number_format($sellerTotal, 0, ',', '.') }}</p></div>
                @php($estimatedTotal += $sellerTotal)
            @endforeach
            <div class="mt-4 flex justify-between border-t-2 border-ink pt-4 text-lg font-bold"><span>Perkiraan total barang</span><span class="tabular-nums">Rp{{ number_format($estimatedTotal, 0, ',', '.') }}</span></div>
            <p class="mt-2 text-sm text-muted">Tidak ada pajak atau biaya pengiriman yang dihitung aplikasi. Server memeriksa ulang harga dan stok saat pesanan dibuat.</p>
        </section>
        <form method="POST" action="{{ route('marketplace.checkout.store') }}" data-unsaved-form class="grid gap-4 rounded-card border border-line bg-paper p-5 md:grid-cols-2">@csrf<input type="hidden" name="request_key" value="{{ $requestKey }}">
            <fieldset class="space-y-2 md:col-span-2"><legend class="font-semibold">Cara menerima barang</legend><label class="flex min-h-11 items-center gap-3"><input type="radio" name="delivery_method" value="delivery" checked class="size-5"><span>Diantar ke alamat</span></label><label class="flex min-h-11 items-center gap-3"><input type="radio" name="delivery_method" value="pickup" class="size-5"><span>Ambil langsung dari penjual</span></label></fieldset>
            <label class="md:col-span-2">Alamat pengantaran <span class="text-muted">(diisi jika memilih diantar)</span><textarea name="delivery_address" rows="3" maxlength="3000" class="mt-1 w-full rounded-control border border-line px-3 py-2" autocomplete="street-address" placeholder="Nama jalan, nomor rumah, RT/RW…"></textarea></label>
            <label>Nama penerima<input name="recipient_name" required maxlength="180" class="mt-1 min-h-11 w-full rounded-control border border-line px-3" autocomplete="name"></label><label>Nomor telepon penerima<input name="recipient_phone" type="tel" inputmode="tel" autocomplete="tel" required maxlength="40" class="mt-1 min-h-11 w-full rounded-control border border-line px-3"></label>
            <fieldset class="space-y-2 md:col-span-2"><legend class="font-semibold">Cara pembayaran</legend>
                <label class="flex min-h-11 items-start gap-3 rounded-control border border-line p-3"><input type="radio" name="payment_method" value="cod" checked class="mt-1 size-5"><span><strong>Tunai saat barang diterima (COD)</strong><span class="block text-sm text-muted">Belum dibayar saat checkout; pembayaran dikonfirmasi setelah barang diserahkan dan uang diterima.</span></span></label>
                @if($xenditEnabled)
                    <label class="flex min-h-11 items-start gap-3 rounded-control border border-line p-3"><input type="radio" name="payment_method" value="xendit" class="mt-1 size-5"><span><strong>Xendit · {{ config('services.xendit.mode') === 'test' ? 'Mode uji' : 'Online' }}</strong><span class="block text-sm text-muted">Bayar sekali untuk semua penjual. Xendit mengirim alokasi {{ $sellerPayoutPercent }}% per pesanan ke sub-account penjual. {{ $platformRetainedPercent }}% masuk ke akun platform terlebih dahulu untuk menutup biaya provider; pastikan persentasenya sesuai biaya Xendit. Pesanan baru diteruskan setelah seluruh transfer split terkonfirmasi.</span></span></label>
                @else
                    <p class="rounded-control border border-line bg-canvas p-3 text-sm text-muted">Pembayaran Xendit belum tersedia. {{ $xenditUnavailableReason }}</p>
                @endif
            </fieldset>
            <p class="md:col-span-2 rounded-control bg-warning-bg p-4 text-sm leading-6 text-warning-ink">Jumlah pembayaran diverifikasi dari notifikasi server Xendit. Kembali dari halaman pembayaran tidak otomatis menandai pesanan lunas.</p>
            <div class="md:col-span-2"><x-ui.button type="submit">Buat pesanan</x-ui.button></div>
        </form>
    </main>
</x-layouts.app>
