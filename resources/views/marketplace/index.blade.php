<x-layouts.app title="Pasar desa">
    <main id="main" class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-8">
        <x-ui.page-heading title="Pasar desa" description="Temukan produk dari penjual lokal yang telah disetujui. Pesanan akan dipisah sesuai penjual." />
        <form method="GET" action="{{ route('marketplace.index') }}" class="grid gap-3 rounded-card border border-line bg-paper p-4 sm:grid-cols-2 lg:grid-cols-6">
            <label class="sm:col-span-2 lg:col-span-2">Cari produk<input name="q" value="{{ request('q') }}" class="mt-1 min-h-11 w-full rounded-control border border-line px-3" placeholder="Contoh: keripik singkong…"></label>
            <label>Kategori<select name="category" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>@endforeach</select></label>
            <label>Harga minimum<input type="number" min="0" name="min_price" value="{{ request('min_price') }}" class="mt-1 min-h-11 w-full rounded-control border border-line px-3" inputmode="numeric"></label>
            <label>Harga maksimum<input type="number" min="0" name="max_price" value="{{ request('max_price') }}" class="mt-1 min-h-11 w-full rounded-control border border-line px-3" inputmode="numeric"></label>
            <label>Urutkan<select name="sort" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3"><option value="newest">Terbaru</option><option value="price_low" @selected(request('sort') === 'price_low')>Harga terendah</option><option value="price_high" @selected(request('sort') === 'price_high')>Harga tertinggi</option></select></label>
            <div class="flex items-end gap-2 lg:col-span-6"><x-ui.button type="submit">Terapkan filter</x-ui.button><a href="{{ route('marketplace.index') }}" class="inline-flex min-h-11 items-center px-3 text-sm underline">Hapus filter</a><a href="{{ route('marketplace.cart') }}" class="ml-auto inline-flex min-h-11 items-center px-3 text-sm font-semibold text-brand underline">Lihat keranjang</a></div>
        </form>
        @if($products->isEmpty())<x-ui.empty-state title="Produk belum ditemukan" description="Coba kata kunci atau kategori lain. Produk yang belum disetujui tidak ditampilkan." />
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($products as $product)
                    <article class="overflow-hidden rounded-card border border-line bg-paper">
                        @if($product->images->first())<img src="{{ route('marketplace.products.images.show', [$product, $product->images->first()]) }}" alt="{{ $product->images->first()->alt_text }}" width="640" height="480" loading="lazy" class="aspect-[4/3] w-full object-cover">@else<div class="flex aspect-[4/3] items-center justify-center bg-slate-100 text-sm text-muted">Belum ada foto</div>@endif
                        <div class="space-y-2 p-4"><p class="text-xs text-muted">{{ $product->category->name }} · {{ $product->seller->business_name }}</p><h2 class="line-clamp-2 font-semibold"><a href="{{ route('marketplace.products.show', $product) }}" class="hover:underline focus-visible:underline">{{ $product->name }}</a></h2><p class="text-lg font-semibold tabular-nums">Rp{{ number_format((float)$product->price, 0, ',', '.') }} <span class="text-sm font-normal text-muted">/ {{ $product->unit }}</span></p><p class="text-sm text-muted">Stok {{ $product->stock }}</p>
                            @auth @if(auth()->user()->hasRole('Resident'))<form method="POST" action="{{ route('marketplace.cart.add', $product) }}" class="flex items-center gap-2" data-cart-add-form>@csrf<label class="sr-only" for="quantity-{{ $product->id }}">Jumlah {{ $product->name }}</label><input id="quantity-{{ $product->id }}" name="quantity" type="number" min="1" max="{{ min(1000,$product->stock) }}" value="1" class="min-h-11 w-20 rounded-control border border-line px-2" inputmode="numeric"><x-ui.button type="submit">Tambah</x-ui.button></form>@endif @else<a href="{{ route('login') }}" class="inline-flex min-h-11 items-center text-sm font-medium text-brand underline">Masuk untuk membeli</a>@endauth
                        </div>
                    </article>
                @endforeach
            </div>
            {{ $products->links() }}
        @endif
    </main>
</x-layouts.app>
