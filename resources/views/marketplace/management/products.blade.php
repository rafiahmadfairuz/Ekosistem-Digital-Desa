<x-layouts.app title="Moderasi produk">
    <main id="main" class="mx-auto max-w-7xl space-y-6 px-4 py-7 sm:px-8 sm:py-9">
        <header class="max-w-3xl">
            <p class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-600">
                <span class="size-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                <span>Manajemen Pasar Desa · BUMDes</span>
            </p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-800 sm:text-[1.625rem]">Pemeriksaan produk</h1>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">
                Produk baru dan perubahan menunggu pemeriksaan BUMDes sebelum ditampilkan kepada pembeli.
            </p>
        </header>

        <div class="space-y-5">
            @forelse($products as $product)
                @php
                    $statusBadgeClass = match($product->status) {
                        'active' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                        'pending_review' => 'border-amber-200 bg-amber-50 text-amber-800',
                        'out_of_stock' => 'border-slate-200 bg-slate-100 text-slate-700',
                        'rejected', 'inactive' => 'border-red-200 bg-red-50 text-red-700',
                        default => 'border-slate-200 bg-slate-50 text-slate-600',
                    };
                    $statusDotClass = match($product->status) {
                        'active' => 'bg-emerald-500',
                        'pending_review' => 'bg-amber-500',
                        'out_of_stock' => 'bg-slate-400',
                        'rejected', 'inactive' => 'bg-red-500',
                        default => 'bg-slate-400',
                    };
                @endphp
                <article class="grid gap-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-xs transition hover:border-slate-300 sm:p-6 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,22rem)] lg:items-start">
                    <div class="min-w-0 space-y-4">
                        <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 pb-4">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50/70 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                        {{ $product->category->name }}
                                    </span>
                                    <span class="text-xs font-medium text-slate-500">
                                        Penjual: <strong class="font-semibold text-slate-700">{{ $product->seller->business_name }}</strong>
                                    </span>
                                </div>
                                <h2 class="mt-2 break-words text-lg font-bold text-slate-800 sm:text-xl">{{ $product->name }}</h2>
                            </div>

                            <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold {{ $statusBadgeClass }}">
                                <span class="size-1.5 rounded-full {{ $statusDotClass }}" aria-hidden="true"></span>
                                <span>{{ str_replace('_',' ',ucfirst($product->status)) }}</span>
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            <div class="rounded-xl border border-emerald-100 bg-emerald-50/50 px-3.5 py-2">
                                <span class="block text-[11px] font-semibold uppercase tracking-wider text-emerald-700/80">Harga Satuan</span>
                                <span class="text-base font-bold tabular-nums text-emerald-700">Rp{{ number_format((float)$product->price,0,',','.') }}</span>
                            </div>
                            <div class="rounded-xl border border-slate-200 bg-slate-50/70 px-3.5 py-2">
                                <span class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400">Ketersediaan</span>
                                <span class="text-base font-bold tabular-nums text-slate-800">Stok {{ $product->stock }}</span>
                            </div>
                        </div>

                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Deskripsi produk</h3>
                            <p class="mt-1.5 whitespace-pre-line break-words text-sm leading-relaxed text-slate-700">{{ $product->description }}</p>
                        </div>

                        @if($product->images->isNotEmpty())
                            <div>
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Foto produk</h3>
                                <div class="mt-2 flex flex-wrap gap-2.5">
                                    @foreach($product->images as $image)
                                        <img src="{{ route('marketplace.products.images.show',[$product,$image]) }}" alt="{{ $image->alt_text }}" width="120" height="90" class="size-24 rounded-xl border border-slate-200 bg-slate-50 object-cover shadow-2xs transition hover:scale-105">
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="border-t border-slate-100 pt-4 lg:border-l lg:border-t-0 lg:pl-6 lg:pt-0">
                        @if(in_array($product->status,['pending_review','active','out_of_stock'],true))
                            <form method="POST" action="{{ route('marketplace.management.products.review',$product) }}" class="space-y-4 rounded-xl border border-slate-100 bg-slate-50/60 p-4">
                                @csrf
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Keputusan Moderasi</p>

                                <label class="block text-xs font-semibold text-slate-700">
                                    <span>Status</span>
                                    <select name="status" class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15">
                                        @if($product->status==='pending_review')
                                            <option value="active">Setujui dan terbitkan</option>
                                            <option value="rejected">Tolak</option>
                                        @else
                                            <option value="inactive">Nonaktifkan</option>
                                        @endif
                                    </select>
                                </label>

                                <label class="block text-xs font-semibold text-slate-700">
                                    <span>Catatan jika ditolak</span>
                                    <input name="reason" maxlength="2000" placeholder="Alasan untuk penjual…" class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15">
                                </label>

                                <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-xs transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                                    Simpan keputusan
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
                    <x-ui.empty-state title="Tidak ada produk untuk ditinjau" description="Produk yang dikirim penjual akan muncul di sini." />
                </div>
            @endforelse
        </div>

        {{ $products->links() }}
    </main>
</x-layouts.app>
