<x-layouts.app title="Keranjang belanja">
    <main id="main" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Header Halaman Minimalis --}}
        <header class="flex flex-col gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="max-w-2xl">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    Keranjang belanja
                </h1>
                <p class="mt-1.5 text-sm leading-relaxed text-slate-500">
                    Periksa kembali daftar produk dan jumlah pesanan Anda sebelum melanjutkan. Harga dan ketersediaan stok akan diverifikasi ulang saat checkout.
                </p>
            </div>

            <div class="shrink-0">
                <a
                    href="{{ route('marketplace.index') }}"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs transition hover:-translate-y-0.5 hover:border-emerald-600 hover:bg-emerald-50/40 hover:text-emerald-700 hover:shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                >
                    <svg class="size-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                    <span>Lanjut jelajahi produk</span>
                </a>
            </div>
        </header>

        {{-- Peringatan Validasi --}}
        <div class="mt-6 space-y-3">
            @if ($hasOwnProducts)
                <x-ui.alert type="error">Keranjang berisi produk dari usaha milik Anda. Hapus produk tersebut sebelum checkout. Penjual tidak dapat membeli produk miliknya sendiri.</x-ui.alert>
            @endif

            @if ($errors->has('cart'))
                <x-ui.alert type="error">{{ $errors->first('cart') }}</x-ui.alert>
            @endif
        </div>

        @php
            $total = 0;
        @endphp

        @if ($cart->items->isEmpty())
            <div class="mt-6 rounded-2xl border border-slate-200/90 bg-white p-8 shadow-sm">
                <x-ui.empty-state
                    title="Keranjang Anda masih kosong"
                    description="Jelajahi produk warga desa, lalu tambahkan barang yang ingin dibeli."
                    action="Jelajahi produk"
                    href="{{ route('marketplace.index') }}"
                />
            </div>
        @else
            <div class="mt-6 grid items-start gap-7 xl:grid-cols-[minmax(0,1fr)_22rem]">
                {{-- Kolom Daftar Barang per Penjual --}}
                <div class="space-y-6">
                    @foreach ($cart->items->groupBy(fn ($item) => $item->product->seller_id) as $sellerItems)
                        @php
                            $sellerSubtotal = 0;
                        @endphp
                        <section
                            class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm transition-shadow duration-200 hover:shadow-md"
                            aria-labelledby="seller-{{ $sellerItems->first()->product->seller_id }}"
                        >
                            {{-- Header Penjual --}}
                            <header class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/70 px-5 py-4">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <svg class="size-5 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7" />
                                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
                                        <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4" />
                                        <path d="M2 7h20" />
                                        <path d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7" />
                                    </svg>
                                    <div class="min-w-0">
                                        <p class="text-[11px] font-medium uppercase tracking-wider text-slate-400">Penjual</p>
                                        <h2 id="seller-{{ $sellerItems->first()->product->seller_id }}" class="truncate text-sm font-bold text-slate-900">
                                            {{ $sellerItems->first()->product->seller->business_name }}
                                        </h2>
                                    </div>
                                </div>

                                <span class="rounded-full border border-slate-200/80 bg-white px-3 py-1 text-xs font-medium text-slate-600 shadow-2xs">
                                    {{ $sellerItems->count() }} barang
                                </span>
                            </header>

                            {{-- Daftar Item --}}
                            <div class="divide-y divide-slate-100 px-5">
                                @foreach ($sellerItems as $item)
                                    @php
                                        $product = $item->product;
                                        $subtotal = (float) $product->price * $item->quantity;
                                        $total += $subtotal;
                                        $sellerSubtotal += $subtotal;
                                        $productImage = $product->isPubliclyVisible() ? $product->images->first() : null;
                                        $isOwnProduct = (int) $product->seller->user_id === (int) auth()->id();
                                    @endphp
                                    <article class="grid gap-4 py-5 sm:grid-cols-[6.5rem_minmax(0,1fr)] sm:items-center">
                                        {{-- Thumbnail --}}
                                        <a
                                            href="{{ route('marketplace.products.show', $product) }}"
                                            class="group relative block w-fit overflow-hidden rounded-xl border border-slate-200/80 bg-slate-50 shadow-2xs focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                                            aria-label="Lihat produk {{ $product->name }}"
                                        >
                                            @if ($productImage)
                                                <img
                                                    src="{{ route('marketplace.products.images.show', [$product, $productImage]) }}"
                                                    alt="{{ $productImage->alt_text ?: $product->name }}"
                                                    width="224"
                                                    height="224"
                                                    loading="lazy"
                                                    class="aspect-square size-26 object-cover transition duration-300 group-hover:scale-105"
                                                >
                                            @else
                                                <span class="flex aspect-square size-26 flex-col items-center justify-center gap-1 px-2 text-center text-[11px] text-slate-400">
                                                    <svg class="size-5 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                                        <circle cx="9" cy="9" r="2" />
                                                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                                    </svg>
                                                    <span>Belum ada foto</span>
                                                </span>
                                            @endif
                                        </a>

                                        {{-- Detail & Kontrol Kuantitas --}}
                                        <div class="grid min-w-0 gap-4 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-center">
                                            <div class="min-w-0">
                                                <h3 class="break-words text-base font-semibold leading-snug text-slate-900">
                                                    <a href="{{ route('marketplace.products.show', $product) }}" class="transition hover:text-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                                                        {{ $product->name }}
                                                    </a>
                                                </h3>

                                                <p class="mt-1 text-xs text-slate-500">
                                                    <span>Rp{{ number_format((float) $product->price, 0, ',', '.') }} / {{ $product->unit }}</span>
                                                    <span class="mx-1.5 text-slate-300" aria-hidden="true">·</span>
                                                    <span class="tabular-nums">Stok {{ $product->stock }}</span>
                                                </p>

                                                <p class="mt-2.5 text-sm font-bold text-slate-900 tabular-nums">
                                                    <span class="text-xs font-normal text-slate-500">Subtotal:</span>
                                                    Rp{{ number_format($subtotal, 0, ',', '.') }}
                                                </p>

                                                @if ($isOwnProduct)
                                                    <p class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-rose-50 px-2.5 py-1 text-xs font-medium text-rose-700">
                                                        <svg class="size-3.5 shrink-0 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                            <circle cx="12" cy="12" r="10" />
                                                            <line x1="12" x2="12" y1="8" y2="12" />
                                                            <line x1="12" x2="12.01" y1="16" y2="16" />
                                                        </svg>
                                                        <span>Produk usaha Anda. Hapus untuk melanjutkan checkout.</span>
                                                    </p>
                                                @elseif (! $product->isPubliclyAvailable())
                                                    <p class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-rose-50 px-2.5 py-1 text-xs font-medium text-rose-700">
                                                        <svg class="size-3.5 shrink-0 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                            <circle cx="12" cy="12" r="10" />
                                                            <line x1="12" x2="12" y1="8" y2="12" />
                                                            <line x1="12" x2="12.01" y1="16" y2="16" />
                                                        </svg>
                                                        <span>Produk ini sedang tidak tersedia untuk checkout.</span>
                                                    </p>
                                                @endif
                                            </div>

                                            {{-- Form Jumlah & Hapus --}}
                                            <div class="flex flex-wrap items-end gap-2.5 lg:justify-end">
                                                <form method="POST" action="{{ route('marketplace.cart.items.update', $item) }}" class="flex flex-wrap items-end gap-2">
                                                    @csrf
                                                    @method('PUT')
                                                    <div>
                                                        <label for="quantity-{{ $item->id }}" class="block text-[11px] font-medium text-slate-500">
                                                            Jumlah
                                                        </label>
                                                        <input
                                                            id="quantity-{{ $item->id }}"
                                                            name="quantity"
                                                            type="number"
                                                            min="1"
                                                            max="1000"
                                                            value="{{ $item->quantity }}"
                                                            inputmode="numeric"
                                                            class="mt-1 block min-h-10 w-20 rounded-xl border border-slate-200 bg-slate-50/70 px-2.5 text-center text-sm font-semibold text-slate-800 tabular-nums transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                                        >
                                                    </div>
                                                    <button
                                                        type="submit"
                                                        class="inline-flex min-h-10 cursor-pointer items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs transition hover:border-emerald-600 hover:bg-emerald-50/50 hover:text-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                                                    >
                                                        <svg class="size-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                            <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                                                            <path d="M3 3v5h5" />
                                                            <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16" />
                                                            <path d="M16 16h5v5" />
                                                        </svg>
                                                        <span>Perbarui</span>
                                                    </button>
                                                    @error('quantity')
                                                        <p class="w-full text-xs text-rose-600" role="alert">{{ $message }}</p>
                                                    @enderror
                                                </form>

                                                <form method="POST" action="{{ route('marketplace.cart.items.destroy', $item) }}" data-confirm="Hapus {{ $product->name }} dari keranjang?">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button
                                                        type="submit"
                                                        aria-label="Hapus {{ $product->name }} dari keranjang"
                                                        class="inline-flex min-h-10 cursor-pointer items-center justify-center gap-1.5 rounded-xl px-3 py-2 text-xs font-medium text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-600"
                                                    >
                                                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                            <path d="M3 6h18" />
                                                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6" />
                                                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2" />
                                                        </svg>
                                                        <span>Hapus</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>

                            {{-- Footer Subtotal Penjual --}}
                            <footer class="flex items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/60 px-5 py-3.5 text-sm">
                                <span class="text-xs font-medium text-slate-500">Subtotal penjual ini</span>
                                <span class="font-bold text-slate-900 tabular-nums">Rp{{ number_format($sellerSubtotal, 0, ',', '.') }}</span>
                            </footer>
                        </section>
                    @endforeach
                </div>

                {{-- Kolom Ringkasan Belanja (Elevated Shadow) --}}
                <aside
                    class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-lg shadow-slate-900/5 xl:sticky xl:top-6"
                    aria-labelledby="cart-total-heading"
                >
                    <div class="flex items-center gap-2">
                        <svg class="size-5 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z" />
                            <path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8" />
                            <path d="M12 17.5v-11" />
                        </svg>
                        <h2 id="cart-total-heading" class="text-base font-bold text-slate-900">
                            Ringkasan belanja
                        </h2>
                    </div>

                    <dl class="mt-5 space-y-3 border-b border-slate-100 pb-5 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Total barang</dt>
                            <dd class="font-semibold text-slate-800 tabular-nums">{{ $cart->items->sum('quantity') }} item</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-slate-500">Jumlah penjual</dt>
                            <dd class="font-semibold text-slate-800 tabular-nums">{{ $cart->items->groupBy(fn ($item) => $item->product->seller_id)->count() }} toko</dd>
                        </div>
                    </dl>

                    <div class="mt-5">
                        <p class="text-xs font-medium text-slate-500">Perkiraan total belanja</p>
                        <p class="mt-1 text-2xl font-bold tracking-tight text-slate-900 tabular-nums">
                            Rp{{ number_format($total, 0, ',', '.') }}
                        </p>
                    </div>

                    <div class="mt-3 flex items-start gap-2 rounded-xl bg-slate-50 p-3 text-xs leading-relaxed text-slate-500">
                        <svg class="mt-0.5 size-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 16v-4" />
                            <path d="M12 8h.01" />
                        </svg>
                        <p>Total dan ketersediaan dihitung ulang oleh server saat checkout. Ongkos kirim atau biaya lain di luar aplikasi belum termasuk.</p>
                    </div>

                    @unless ($hasOwnProducts)
                        <a
                            href="{{ route('marketplace.checkout') }}"
                            class="mt-5 inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white shadow-md shadow-emerald-600/20 transition hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow-lg hover:shadow-emerald-600/25 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                        >
                            <span>Lanjut ke checkout</span>
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M5 12h14" />
                                <path d="m12 5 7 7-7 7" />
                            </svg>
                        </a>
                    @else
                        <p class="mt-5 rounded-xl border border-rose-200 bg-rose-50 p-3.5 text-xs font-medium leading-relaxed text-rose-700">
                            Hapus produk usaha Anda dari keranjang untuk melanjutkan proses checkout.
                        </p>
                    @endunless

                    <a
                        href="{{ route('marketplace.index') }}"
                        class="mt-3 inline-flex min-h-10 w-full items-center justify-center rounded-xl px-4 py-2 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                    >
                        Kembali ke pasar desa
                    </a>
                </aside>
            </div>
        @endif
    </main>
</x-layouts.app>
