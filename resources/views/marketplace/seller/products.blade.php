<x-layouts.app title="Produk saya">
    <main id="main" class="mx-auto max-w-6xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        {{-- Header Halaman --}}
        <header class="flex flex-col gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="max-w-2xl">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    Produk toko saya
                </h1>
                <p class="mt-1.5 text-sm leading-relaxed text-slate-500">
                    Produk baru dan setiap perubahan akan dikirim ke tim pemeriksaan terlebih dahulu sebelum tampil di pasar desa.
                </p>
            </div>

            <div class="shrink-0">
                <a
                    href="{{ route('marketplace.seller.products.archived') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs transition hover:-translate-y-0.5 hover:border-emerald-600 hover:bg-emerald-50/40 hover:text-emerald-700 hover:shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                >
                    Kelola produk arsip
                </a>
            </div>
        </header>

        {{-- Form Tambah Produk --}}
        <form
            method="POST"
            enctype="multipart/form-data"
            action="{{ route('marketplace.seller.products.store') }}"
            data-unsaved-form
            class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm"
        >
            @csrf

            <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4 sm:px-8">
                <h2 class="text-base font-bold text-slate-900">Tambah produk baru</h2>
                <p class="mt-0.5 text-xs text-slate-500">Lengkapi rincian produk dan unggah foto terbaik agar mudah dikenali pembeli.</p>
            </div>

            <div class="space-y-6 p-6 sm:p-8">
                {{-- Baris 1: Nama & Kategori --}}
                <div class="grid gap-5 md:grid-cols-12">
                    <div class="md:col-span-7">
                        <label for="product-name" class="block text-xs font-semibold text-slate-700">
                            Nama produk <span class="text-rose-500">*</span>
                        </label>
                        <input
                            id="product-name"
                            name="name"
                            required
                            maxlength="180"
                            value="{{ old('name') }}"
                            placeholder="Contoh: Keripik Singkong Balado 250g"
                            class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                        >
                        @error('name')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-5">
                        <label for="product-category" class="block text-xs font-semibold text-slate-700">
                            Kategori <span class="text-rose-500">*</span>
                        </label>
                        <select
                            id="product-category"
                            name="category_id"
                            required
                            class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-sm text-slate-900 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                        >
                            <option value="">Pilih kategori…</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Baris 2: Harga, Stok & Satuan --}}
                <div class="grid gap-5 sm:grid-cols-3">
                    <div>
                        <label for="product-price" class="block text-xs font-semibold text-slate-700">
                            Harga satuan <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative mt-1.5">
                            <span class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-xs font-semibold text-slate-400">
                                Rp
                            </span>
                            <input
                                id="product-price"
                                name="price"
                                type="number"
                                min="0.01"
                                step="0.01"
                                required
                                value="{{ old('price') }}"
                                inputmode="decimal"
                                placeholder="0"
                                class="min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 pr-3.5 pl-9.5 text-sm font-medium text-slate-900 tabular-nums placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                            >
                        </div>
                        @error('price')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="product-stock" class="block text-xs font-semibold text-slate-700">
                            Stok tersedia <span class="text-rose-500">*</span>
                        </label>
                        <input
                            id="product-stock"
                            name="stock"
                            type="number"
                            min="0"
                            max="4294967295"
                            required
                            value="{{ old('stock') }}"
                            inputmode="numeric"
                            placeholder="0"
                            class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-sm font-medium text-slate-900 tabular-nums placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                        >
                        @error('stock')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="product-unit" class="block text-xs font-semibold text-slate-700">
                            Satuan jual <span class="text-rose-500">*</span>
                        </label>
                        <input
                            id="product-unit"
                            name="unit"
                            required
                            maxlength="40"
                            value="{{ old('unit', 'item') }}"
                            placeholder="Contoh: bungkus, kg, botol…"
                            class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                        >
                        @error('unit')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Baris 3: Upload Gambar Interaktif dengan Pratinjau --}}
                <div>
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <label for="product-images" class="block text-xs font-semibold text-slate-700">
                            Foto produk
                        </label>
                        <span id="image-counter" class="text-xs font-medium text-slate-400">
                            0 / 5 foto dipilih
                        </span>
                    </div>

                    <div
                        id="image-dropzone"
                        class="relative mt-1.5 rounded-2xl border-2 border-dashed border-slate-200 bg-slate-50/50 p-6 text-center transition-all duration-200 hover:border-emerald-500/70 hover:bg-emerald-50/20"
                    >
                        <input
                            id="product-images"
                            name="images[]"
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            multiple
                            class="absolute inset-0 z-10 size-full cursor-pointer opacity-0"
                        >

                        <div class="mx-auto flex max-w-md flex-col items-center">
                            <svg class="size-8 text-slate-400 transition-colors group-hover:text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect width="18" height="18" x="3" y="3" rx="2" ry="2" />
                                <circle cx="9" cy="9" r="2" />
                                <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
                            </svg>

                            <p class="mt-2.5 text-sm font-medium text-slate-700">
                                <span class="font-semibold text-emerald-700 underline underline-offset-2">Pilih foto produk</span>
                                atau seret gambar ke area ini
                            </p>
                            <p class="mt-1 text-xs leading-relaxed text-slate-400">
                                Maksimal 5 gambar · Format JPG, PNG, atau WebP · Maks. 5 MB (5.120 KB) per foto
                            </p>
                        </div>
                    </div>

                    {{-- Peringatan Batas File (Client-Side) --}}
                    <p id="image-upload-warning" class="mt-2 hidden text-xs font-medium text-rose-600" role="alert"></p>

                    @error('images')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                    @error('images.*')
                        <p class="mt-1.5 text-xs text-rose-600">{{ $message }}</p>
                    @enderror

                    {{-- Grid Pratinjau Foto Terpilih --}}
                    <div id="image-preview-grid" class="mt-3.5 hidden grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5"></div>
                </div>

                {{-- Baris 4: Deskripsi Produk --}}
                <div>
                    <label for="product-description" class="block text-xs font-semibold text-slate-700">
                        Deskripsi produk <span class="text-rose-500">*</span>
                    </label>
                    <textarea
                        id="product-description"
                        name="description"
                        required
                        maxlength="10000"
                        rows="4"
                        placeholder="Jelaskan komposisi, ukuran, cara penyimpanan, atau keunggulan produk Anda…"
                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm leading-relaxed text-slate-900 placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Footer Submit Form --}}
            <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/60 px-6 py-4 sm:px-8">
                <p class="text-xs text-slate-500">
                    Pastikan seluruh data sudah sesuai sebelum dikirim.
                </p>
                <button
                    type="submit"
                    class="inline-flex min-h-11 cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow-md hover:shadow-emerald-600/25 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                >
                    Kirim produk untuk pemeriksaan
                </button>
            </div>
        </form>

        {{-- Daftar Produk Toko --}}
        <section class="space-y-4" aria-labelledby="seller-products-heading">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h2 id="seller-products-heading" class="text-lg font-bold text-slate-900 sm:text-xl">
                        Daftar produk aktif
                    </h2>
                    <p class="text-xs text-slate-500">Produk yang belum diarsipkan akan tampil di bawah ini.</p>
                </div>
            </div>

            <div class="space-y-3.5">
                @forelse($products as $product)
                    @php
                        $statusBadgeStyles = [
                            'approved' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                            'published' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                            'pending' => 'border-amber-200 bg-amber-50 text-amber-800',
                            'pending_review' => 'border-amber-200 bg-amber-50 text-amber-800',
                            'rejected' => 'border-rose-200 bg-rose-50 text-rose-700',
                        ];
                        $statusDotStyles = [
                            'approved' => 'bg-emerald-500',
                            'published' => 'bg-emerald-500',
                            'pending' => 'bg-amber-500',
                            'pending_review' => 'bg-amber-500',
                            'rejected' => 'bg-rose-500',
                        ];
                        $badgeStyle = $statusBadgeStyles[$product->status] ?? 'border-slate-200 bg-slate-50 text-slate-700';
                        $dotStyle = $statusDotStyles[$product->status] ?? 'bg-slate-400';
                        $thumb = $product->images->first();
                    @endphp

                    <article class="flex flex-col justify-between gap-4 rounded-2xl border border-slate-200/90 bg-white p-4 shadow-sm transition-all duration-200 hover:border-slate-300 hover:shadow-md sm:flex-row sm:items-center sm:p-5">
                        <div class="flex min-w-0 items-start gap-4 sm:items-center">
                            {{-- Thumbnail Kecil Produk --}}
                            <div class="size-16 shrink-0 overflow-hidden rounded-xl border border-slate-200/80 bg-slate-50">
                                @if($thumb)
                                    <img
                                        src="{{ route('marketplace.products.images.show', [$product, $thumb]) }}"
                                        alt="{{ $thumb->alt_text ?: $product->name }}"
                                        width="128"
                                        height="128"
                                        loading="lazy"
                                        class="size-full object-cover"
                                    >
                                @else
                                    <div class="flex size-full items-center justify-center text-[10px] font-medium text-slate-400">
                                        No foto
                                    </div>
                                @endif
                            </div>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="truncate text-base font-bold text-slate-900">
                                        {{ $product->name }}
                                    </h3>
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $badgeStyle }}">
                                        <span class="size-1.5 rounded-full {{ $dotStyle }}" aria-hidden="true"></span>
                                        <span>{{ str_replace('_', ' ', ucfirst($product->status)) }}</span>
                                    </span>
                                </div>

                                <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                                    <span class="font-medium text-slate-700">{{ $product->category->name }}</span>
                                    <span class="mx-1.5 text-slate-300" aria-hidden="true">·</span>
                                    <span class="font-semibold text-slate-900 tabular-nums">Rp{{ number_format((float)$product->price, 0, ',', '.') }}</span>
                                    <span class="text-slate-400">/ {{ $product->unit }}</span>
                                    <span class="mx-1.5 text-slate-300" aria-hidden="true">·</span>
                                    <span class="tabular-nums">Stok {{ $product->stock }}</span>
                                </p>

                                @if($product->moderation_note)
                                    <p class="mt-2 rounded-lg border border-rose-200/80 bg-rose-50 px-2.5 py-1.5 text-xs font-medium text-rose-700">
                                        Catatan pemeriksaan: {{ $product->moderation_note }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        <div class="flex shrink-0 flex-wrap items-center gap-2 border-t border-slate-100 pt-3 sm:border-t-0 sm:pt-0">
                            <a
                                href="{{ route('marketplace.seller.products.edit', $product) }}"
                                class="inline-flex min-h-10 items-center justify-center rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-2xs transition hover:border-emerald-600 hover:bg-emerald-50/50 hover:text-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                            >
                                Ubah
                            </a>

                            <form
                                method="POST"
                                action="{{ route('marketplace.seller.products.archive', $product) }}"
                                data-confirm="Arsipkan produk ini? Riwayat pesanan tetap disimpan."
                            >
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="inline-flex min-h-10 cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2 text-xs font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-600"
                                >
                                    Arsipkan
                                </button>
                            </form>
                        </div>
                    </article>
                @empty
                    <div class="rounded-2xl border border-slate-200/90 bg-white p-8 shadow-sm">
                        <x-ui.empty-state
                            title="Belum ada produk aktif"
                            description="Produk yang belum diarsipkan akan muncul di sini."
                        />
                    </div>
                @endforelse
            </div>

            <div class="pt-2">
                {{ $products->links() }}
            </div>
        </section>
    </main>

    {{-- Script Pratinjau & Validasi Gambar Produk --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const input = document.getElementById('product-images');
            const dropzone = document.getElementById('image-dropzone');
            const previewGrid = document.getElementById('image-preview-grid');
            const counter = document.getElementById('image-counter');
            const warning = document.getElementById('image-upload-warning');

            if (!input || !dropzone || !previewGrid) return;

            const MAX_FILES = 5;
            const MAX_SIZE_BYTES = 5 * 1024 * 1024; // 5 MB / 5.120 KB
            let selectedFiles = [];

            const syncInputFiles = () => {
                const dt = new DataTransfer();
                selectedFiles.forEach((file) => dt.items.add(file));
                input.files = dt.files;
            };

            const formatSize = (bytes) => {
                if (bytes < 1024 * 1024) {
                    return Math.round(bytes / 1024) + ' KB';
                }
                return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
            };

            const renderPreviews = () => {
                previewGrid.innerHTML = '';
                counter.textContent = `${selectedFiles.length} / ${MAX_FILES} foto dipilih`;

                if (selectedFiles.length === 0) {
                    previewGrid.classList.add('hidden');
                    previewGrid.classList.remove('grid');
                    return;
                }

                previewGrid.classList.remove('hidden');
                previewGrid.classList.add('grid');

                selectedFiles.forEach((file, index) => {
                    const card = document.createElement('div');
                    card.className = 'group relative overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xs';

                    const url = URL.createObjectURL(file);

                    card.innerHTML = `
                        <div class="aspect-square w-full overflow-hidden bg-slate-100">
                            <img src="${url}" alt="${file.name}" class="size-full object-cover">
                        </div>
                        <div class="flex items-center justify-between gap-1.5 border-t border-slate-100 px-2.5 py-1.5">
                            <span class="truncate text-[11px] font-medium text-slate-700" title="${file.name}">${index === 0 ? 'Utama · ' : ''}${file.name}</span>
                            <span class="shrink-0 text-[10px] text-slate-400 tabular-nums">${formatSize(file.size)}</span>
                        </div>
                        <button
                            type="button"
                            data-remove-index="${index}"
                            aria-label="Hapus foto ${file.name}"
                            class="absolute top-1.5 right-1.5 inline-flex size-6 cursor-pointer items-center justify-center rounded-full bg-slate-900/75 text-xs font-bold text-white transition hover:bg-rose-600"
                        >
                            &times;
                        </button>
                    `;

                    const img = card.querySelector('img');
                    img.onload = () => URL.revokeObjectURL(url);

                    previewGrid.appendChild(card);
                });
            };

            const handleFiles = (incomingList) => {
                const warnings = [];
                const incoming = Array.from(incomingList);

                for (const file of incoming) {
                    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                        warnings.push(`"${file.name}" dilewati karena format harus JPG, PNG, atau WebP.`);
                        continue;
                    }
                    if (file.size > MAX_SIZE_BYTES) {
                        warnings.push(`"${file.name}" melebihi batas 5 MB.`);
                        continue;
                    }
                    if (selectedFiles.length >= MAX_FILES) {
                        warnings.push(`Maksimal hanya ${MAX_FILES} foto yang dapat diunggah.`);
                        break;
                    }
                    selectedFiles.push(file);
                }

                if (warnings.length > 0) {
                    warning.textContent = warnings.join(' ');
                    warning.classList.remove('hidden');
                } else {
                    warning.textContent = '';
                    warning.classList.add('hidden');
                }

                syncInputFiles();
                renderPreviews();
            };

            input.addEventListener('change', (e) => {
                if (e.target.files && e.target.files.length > 0) {
                    handleFiles(e.target.files);
                }
            });

            previewGrid.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-remove-index]');
                if (!btn) return;
                const idx = Number(btn.getAttribute('data-remove-index'));
                selectedFiles.splice(idx, 1);
                syncInputFiles();
                renderPreviews();
            });

            ['dragenter', 'dragover'].forEach((evt) => {
                dropzone.addEventListener(evt, (e) => {
                    e.preventDefault();
                    dropzone.classList.add('border-emerald-600', 'bg-emerald-50/40');
                });
            });

            ['dragleave', 'drop'].forEach((evt) => {
                dropzone.addEventListener(evt, (e) => {
                    e.preventDefault();
                    dropzone.classList.remove('border-emerald-600', 'bg-emerald-50/40');
                });
            });
        });
    </script>
</x-layouts.app>
