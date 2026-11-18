<x-layouts.app title="Pasar desa">
    <main id="main" class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        {{-- Header Halaman Minimalis --}}
        <header class="flex flex-col gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="max-w-2xl">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    Pasar desa
                </h1>
                <p class="mt-1.5 text-sm leading-relaxed text-slate-500">
                    Produk pilihan dari pelaku usaha dan warga lokal terverifikasi. Pesanan akan dipisah otomatis berdasarkan masing-masing penjual.
                </p>
            </div>

            <div class="shrink-0">
                <a
                    href="{{ route('marketplace.cart') }}"
                    class="inline-flex min-h-11 items-center justify-center gap-2.5 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-2xs transition hover:border-emerald-600 hover:bg-emerald-50/40 hover:text-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                >
                    <svg class="size-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="8" cy="21" r="1" />
                        <circle cx="19" cy="21" r="1" />
                        <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                    </svg>
                    <span>Keranjang</span>
                </a>
            </div>
        </header>

        {{-- Panel Pencarian & Filter --}}
        <form
            method="GET"
            action="{{ route('marketplace.index') }}"
            aria-label="Filter produk pasar desa"
            class="mt-6 rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs sm:p-5"
        >
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-12">
                {{-- Pencarian --}}
                <div class="sm:col-span-2 lg:col-span-4">
                    <label for="filter-q" class="block text-xs font-medium text-slate-600">
                        Cari produk
                    </label>
                    <div class="relative mt-1.5">
                        <svg class="pointer-events-none absolute top-1/2 left-3.5 size-4 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <circle cx="11" cy="11" r="8" />
                            <path d="m21 21-4.3-4.3" />
                        </svg>
                        <input
                            id="filter-q"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Nama produk atau kata kunci…"
                            class="min-h-10 w-full rounded-xl border border-slate-200 bg-slate-50/70 pr-3.5 pl-10 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                        >
                    </div>
                </div>

                {{-- Kategori --}}
                <div class="lg:col-span-2">
                    <label for="filter-category" class="block text-xs font-medium text-slate-600">
                        Kategori
                    </label>
                    <select
                        id="filter-category"
                        name="category"
                        class="mt-1.5 min-h-10 w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 text-sm text-slate-800 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                    >
                        <option value="">Semua kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category') == $category->id)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Harga Minimum --}}
                <div class="lg:col-span-2">
                    <label for="filter-min-price" class="block text-xs font-medium text-slate-600">
                        Harga minimum
                    </label>
                    <div class="relative mt-1.5">
                        <span class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-xs font-medium text-slate-400" aria-hidden="true">
                            Rp
                        </span>
                        <input
                            id="filter-min-price"
                            type="number"
                            min="0"
                            name="min_price"
                            value="{{ request('min_price') }}"
                            inputmode="numeric"
                            placeholder="0"
                            class="min-h-10 w-full rounded-xl border border-slate-200 bg-slate-50/70 pr-3 pl-8.5 text-sm text-slate-800 tabular-nums placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                        >
                    </div>
                </div>

                {{-- Harga Maksimum --}}
                <div class="lg:col-span-2">
                    <label for="filter-max-price" class="block text-xs font-medium text-slate-600">
                        Harga maksimum
                    </label>
                    <div class="relative mt-1.5">
                        <span class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-xs font-medium text-slate-400" aria-hidden="true">
                            Rp
                        </span>
                        <input
                            id="filter-max-price"
                            type="number"
                            min="0"
                            name="max_price"
                            value="{{ request('max_price') }}"
                            inputmode="numeric"
                            placeholder="Maksimal"
                            class="min-h-10 w-full rounded-xl border border-slate-200 bg-slate-50/70 pr-3 pl-8.5 text-sm text-slate-800 tabular-nums placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                        >
                    </div>
                </div>

                {{-- Urutkan --}}
                <div class="lg:col-span-2">
                    <label for="filter-sort" class="block text-xs font-medium text-slate-600">
                        Urutkan
                    </label>
                    <select
                        id="filter-sort"
                        name="sort"
                        class="mt-1.5 min-h-10 w-full rounded-xl border border-slate-200 bg-slate-50/70 px-3 text-sm text-slate-800 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                    >
                        <option value="newest">Terbaru</option>
                        <option value="price_low" @selected(request('sort') === 'price_low')>Harga terendah</option>
                        <option value="price_high" @selected(request('sort') === 'price_high')>Harga tertinggi</option>
                    </select>
                </div>
            </div>

            {{-- Baris Aksi Filter --}}
            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-3.5">
                <div class="flex flex-wrap items-center gap-2.5">
                    <button
                        type="submit"
                        class="inline-flex min-h-10 cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                    >
                        Terapkan filter
                    </button>

                    @if(request()->filled('q') || request()->filled('category') || request()->filled('min_price') || request()->filled('max_price') || (request()->filled('sort') && request('sort') !== 'newest'))
                        <a
                            href="{{ route('marketplace.index') }}"
                            class="inline-flex min-h-10 items-center justify-center rounded-xl px-3 py-2 text-sm font-medium text-slate-500 transition hover:bg-slate-100 hover:text-slate-800"
                        >
                            Hapus filter
                        </a>
                    @endif
                </div>

                <p class="text-xs text-slate-500">
                    Menampilkan <span class="font-semibold text-slate-700">{{ $products->count() }}</span> dari <span class="font-semibold text-slate-700">{{ number_format($products->total(), 0, ',', '.') }}</span> produk
                </p>
            </div>
        </form>

        {{-- Daftar Produk --}}
        @if($products->isEmpty())
            <div class="mt-6 rounded-2xl border border-slate-200/90 bg-white p-8">
                <x-ui.empty-state
                    title="Produk belum ditemukan"
                    description="Coba kata kunci atau kategori lain. Produk yang belum disetujui tidak ditampilkan."
                />
            </div>
        @else
            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($products as $product)
                    <article class="group flex flex-col overflow-hidden rounded-2xl border border-slate-200/90 bg-white transition duration-200 hover:border-slate-300 hover:shadow-md">
                        {{-- Thumbnail Produk --}}
                        <a href="{{ route('marketplace.products.show', $product) }}" class="relative block aspect-[4/3] w-full overflow-hidden bg-slate-100" aria-label="Lihat detail {{ $product->name }}">
                            @if($product->images->first())
                                <img
                                    src="{{ route('marketplace.products.images.show', [$product, $product->images->first()]) }}"
                                    alt="{{ $product->images->first()->alt_text ?: $product->name }}"
                                    width="640"
                                    height="480"
                                    loading="lazy"
                                    class="size-full object-cover transition duration-300 group-hover:scale-103"
                                >
                            @else
                                <div class="flex size-full flex-col items-center justify-center gap-1.5 text-slate-400">
                                    <svg class="size-7 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                        <circle cx="9" cy="9" r="2" />
                                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                                    </svg>
                                    <span class="text-xs">Belum ada foto</span>
                                </div>
                            @endif
                        </a>

                        {{-- Informasi Produk --}}
                        <div class="flex flex-1 flex-col p-4">
                            <p class="truncate text-xs text-slate-500">
                                <span class="font-medium text-emerald-700">{{ $product->category->name }}</span>
                                <span class="mx-1 text-slate-300">·</span>
                                <span>{{ $product->seller->business_name }}</span>
                            </p>

                            <h2 class="mt-1.5 line-clamp-2 text-[15px] font-semibold leading-snug text-slate-900">
                                <a href="{{ route('marketplace.products.show', $product) }}" class="transition hover:text-emerald-700 focus-visible:underline">
                                    {{ $product->name }}
                                </a>
                            </h2>

                            <div class="mt-3 flex items-baseline justify-between gap-2">
                                <p class="text-base font-bold text-slate-900 tabular-nums">
                                    Rp{{ number_format((float)$product->price, 0, ',', '.') }}
                                    <span class="text-xs font-normal text-slate-500">/ {{ $product->unit }}</span>
                                </p>

                                @if($product->stock <= 0)
                                    <span class="shrink-0 rounded-md bg-rose-50 px-2 py-0.5 text-[11px] font-medium text-rose-600">
                                        Habis
                                    </span>
                                @elseif($product->stock <= 5)
                                    <span class="shrink-0 rounded-md bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700 tabular-nums">
                                        Sisa {{ $product->stock }}
                                    </span>
                                @else
                                    <span class="shrink-0 text-xs text-slate-400 tabular-nums">
                                        Stok {{ $product->stock }}
                                    </span>
                                @endif
                            </div>

                            {{-- Aksi Pembelian --}}
                            <div class="mt-auto border-t border-slate-100 pt-3.5">
                                @auth
                                    @if(auth()->user()->hasRole('Resident'))
                                        <form method="POST" action="{{ route('marketplace.cart.add', $product) }}" class="flex items-center gap-2" data-cart-add-form>
                                            @csrf
                                            <label class="sr-only" for="quantity-{{ $product->id }}">Jumlah {{ $product->name }}</label>
                                            <input
                                                id="quantity-{{ $product->id }}"
                                                name="quantity"
                                                type="number"
                                                min="1"
                                                max="{{ max(1, min(1000, $product->stock)) }}"
                                                value="1"
                                                inputmode="numeric"
                                                @disabled($product->stock <= 0)
                                                class="min-h-10 w-16 rounded-xl border border-slate-200 bg-slate-50/70 px-2 text-center text-sm font-medium text-slate-800 tabular-nums transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                                            >
                                            <button
                                                type="submit"
                                                @disabled($product->stock <= 0)
                                                class="inline-flex min-h-10 flex-1 cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-3.5 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-500"
                                            >
                                                {{ $product->stock <= 0 ? 'Stok habis' : 'Tambah' }}
                                            </button>
                                        </form>
                                    @else
                                        <a
                                            href="{{ route('marketplace.products.show', $product) }}"
                                            class="inline-flex min-h-10 w-full items-center justify-center rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-100"
                                        >
                                            Lihat detail
                                        </a>
                                    @endif
                                @else
                                    <a
                                        href="{{ route('login') }}"
                                        class="inline-flex min-h-10 w-full items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50/60 px-3.5 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100/70"
                                    >
                                        Masuk untuk membeli
                                    </a>
                                @endauth
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-8">
                {{ $products->links() }}
            </div>
        @endif
    </main>
</x-layouts.app>
