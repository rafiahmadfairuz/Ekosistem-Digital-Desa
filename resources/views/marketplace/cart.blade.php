<x-layouts.app title="Keranjang belanja">
    <main id="main" class="mx-auto max-w-7xl space-y-7 px-4 py-8 sm:px-8">
        <x-ui.page-heading title="Keranjang belanja" description="Periksa barang dan jumlahnya sebelum melanjutkan. Harga dan stok akan diperiksa kembali saat checkout." >
            <x-slot:actions>
                <a href="{{ route('marketplace.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-control border border-line bg-paper px-4 text-sm font-semibold text-ink hover:bg-canvas focus-visible:outline-2 focus-visible:outline-offset-2">Lanjut jelajahi produk</a>
            </x-slot:actions>
        </x-ui.page-heading>

        @if ($hasOwnProducts)
            <x-ui.alert type="error">Keranjang berisi produk dari usaha milik Anda. Hapus produk tersebut sebelum checkout. Penjual tidak dapat membeli produk miliknya sendiri.</x-ui.alert>
        @endif

        @if ($errors->has('cart'))
            <x-ui.alert type="error">{{ $errors->first('cart') }}</x-ui.alert>
        @endif

        @php
            $total = 0;
        @endphp
        @if ($cart->items->isEmpty())
            <x-ui.empty-state title="Keranjang Anda masih kosong" description="Jelajahi produk warga desa, lalu tambahkan barang yang ingin dibeli." action="Jelajahi produk" href="{{ route('marketplace.index') }}" />
        @else
            <div class="grid items-start gap-6 xl:grid-cols-[minmax(0,1fr)_20rem]">
                <div class="space-y-5">
                    @foreach ($cart->items->groupBy(fn ($item) => $item->product->seller_id) as $sellerItems)
                        @php
                            $sellerSubtotal = 0;
                        @endphp
                        <section class="overflow-hidden rounded-card border border-line bg-paper" aria-labelledby="seller-{{ $sellerItems->first()->product->seller_id }}">
                            <header class="flex flex-wrap items-center justify-between gap-2 border-b border-line bg-canvas/60 px-5 py-4">
                                <div><p class="text-xs font-semibold uppercase tracking-wide text-muted">Dijual oleh</p><h2 id="seller-{{ $sellerItems->first()->product->seller_id }}" class="mt-1 font-semibold">{{ $sellerItems->first()->product->seller->business_name }}</h2></div>
                                <span class="rounded-full border border-line bg-paper px-3 py-1 text-xs font-medium">{{ $sellerItems->count() }} barang</span>
                            </header>

                            <div class="divide-y divide-line px-4 sm:px-5">
                                @foreach ($sellerItems as $item)
                                    @php
                                        $product = $item->product;
                                        $subtotal = (float) $product->price * $item->quantity;
                                        $total += $subtotal;
                                        $sellerSubtotal += $subtotal;
                                        $productImage = $product->isPubliclyVisible() ? $product->images->first() : null;
                                        $isOwnProduct = (int) $product->seller->user_id === (int) auth()->id();
                                    @endphp
                                    <article class="grid gap-4 py-5 sm:grid-cols-[7rem_minmax(0,1fr)] sm:items-center">
                                        <a href="{{ route('marketplace.products.show', $product) }}" class="block w-fit rounded-control focus-visible:outline-2 focus-visible:outline-offset-2" aria-label="Lihat produk {{ $product->name }}">
                                            @if ($productImage)
                                                <img src="{{ route('marketplace.products.images.show', [$product, $productImage]) }}" alt="{{ $productImage->alt_text }}" width="224" height="224" loading="lazy" class="aspect-square size-28 rounded-control border border-line bg-canvas object-cover">
                                            @else
                                                <span class="grid aspect-square size-28 place-items-center rounded-control border border-line bg-canvas px-2 text-center text-xs text-muted">Belum ada foto</span>
                                            @endif
                                        </a>

                                        <div class="grid min-w-0 gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                                            <div class="min-w-0">
                                                <h3 class="break-words text-base font-semibold"><a href="{{ route('marketplace.products.show', $product) }}" class="rounded-sm underline decoration-transparent underline-offset-4 hover:decoration-current focus-visible:outline-2 focus-visible:outline-offset-2">{{ $product->name }}</a></h3>
                                                <p class="mt-1 text-sm text-muted">Rp{{ number_format((float) $product->price, 0, ',', '.') }} / {{ $product->unit }} <span aria-hidden="true">·</span> Stok {{ $product->stock }}</p>
                                                <p class="mt-2 font-semibold tabular-nums">Subtotal Rp{{ number_format($subtotal, 0, ',', '.') }}</p>
                                                @if ($isOwnProduct)
                                                    <p class="mt-2 text-sm font-medium text-danger">Produk usaha Anda. Hapus untuk melanjutkan checkout.</p>
                                                @elseif (! $product->isPubliclyAvailable())
                                                    <p class="mt-2 text-sm font-medium text-danger">Produk ini sedang tidak tersedia untuk checkout.</p>
                                                @endif
                                            </div>

                                            <div class="flex flex-wrap items-center gap-3 lg:justify-end">
                                                <form method="POST" action="{{ route('marketplace.cart.items.update', $item) }}" class="flex flex-wrap items-end gap-2">
                                                    @csrf @method('PUT')
                                                    <label for="quantity-{{ $item->id }}" class="text-sm font-medium">Jumlah
                                                        <input id="quantity-{{ $item->id }}" name="quantity" type="number" min="1" max="1000" value="{{ $item->quantity }}" inputmode="numeric" class="mt-1 block min-h-11 w-24 rounded-control border border-line bg-paper px-3 text-base text-ink">
                                                    </label>
                                                    <x-ui.button type="submit" variant="secondary">Perbarui</x-ui.button>
                                                    @error('quantity')<p class="w-full text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                                                </form>
                                                <form method="POST" action="{{ route('marketplace.cart.items.destroy', $item) }}" data-confirm="Hapus {{ $product->name }} dari keranjang?">
                                                    @csrf @method('DELETE')
                                                    <x-ui.button type="submit" variant="plain">Hapus</x-ui.button>
                                                </form>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>

                            <footer class="flex items-center justify-between gap-3 border-t border-line bg-canvas/40 px-5 py-4 text-sm">
                                <span class="text-muted">Subtotal dari penjual ini</span><span class="font-semibold tabular-nums">Rp{{ number_format($sellerSubtotal, 0, ',', '.') }}</span>
                            </footer>
                        </section>
                    @endforeach
                </div>

                <aside class="rounded-card border border-line bg-paper p-5 xl:sticky xl:top-6" aria-labelledby="cart-total-heading">
                    <h2 id="cart-total-heading" class="text-lg font-semibold">Ringkasan belanja</h2>
                    <dl class="mt-5 space-y-3 border-b border-line pb-5 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-muted">Jumlah barang</dt><dd class="font-medium tabular-nums">{{ $cart->items->sum('quantity') }}</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-muted">Penjual</dt><dd class="font-medium tabular-nums">{{ $cart->items->groupBy(fn ($item) => $item->product->seller_id)->count() }}</dd></div>
                    </dl>
                    <div class="mt-5"><p class="text-sm text-muted">Perkiraan total barang</p><p class="mt-1 text-2xl font-semibold tabular-nums">Rp{{ number_format($total, 0, ',', '.') }}</p></div>
                    <p class="mt-3 text-xs leading-5 text-muted">Total dan ketersediaan dihitung ulang oleh server saat checkout. Ongkos kirim atau biaya lain di luar aplikasi belum termasuk.</p>
                    @unless ($hasOwnProducts)
                        <a href="{{ route('marketplace.checkout') }}" class="mt-5 inline-flex min-h-12 w-full items-center justify-center rounded-control bg-brand-strong px-4 text-sm font-semibold text-white hover:bg-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2">Lanjut ke checkout</a>
                    @else
                        <p class="mt-5 rounded-control border border-danger/30 bg-red-50 p-3 text-sm font-medium text-danger">Hapus produk usaha Anda untuk melanjutkan checkout.</p>
                    @endunless
                    <a href="{{ route('marketplace.index') }}" class="mt-3 inline-flex min-h-11 w-full items-center justify-center rounded-control px-4 text-sm font-medium text-brand-strong underline underline-offset-4 hover:text-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2">Kembali ke pasar desa</a>
                </aside>
            </div>
        @endif
    </main>
</x-layouts.app>
