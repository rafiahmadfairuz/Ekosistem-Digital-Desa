<x-layouts.app title="Produk arsip">
    <main id="main" class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-8">
        <x-ui.page-heading title="Produk arsip" description="Produk tetap tersimpan bersama riwayat pesanannya. Pulihkan produk untuk memasukkannya kembali ke daftar toko." />
        <a href="{{ route('marketplace.seller.products') }}" class="inline-flex min-h-11 items-center underline underline-offset-4">Kembali ke produk toko saya</a>
        <section class="space-y-3" aria-label="Daftar produk yang diarsipkan">
            @forelse($products as $product)
                @php($archivedEvent = $product->moderationEvents->where('to_status', 'archived')->last())
                <article class="grid gap-4 rounded-card border border-line bg-paper p-4 sm:grid-cols-[7rem_minmax(0,1fr)_auto] sm:items-center">
                    @if($product->images->isNotEmpty())
                        <img src="{{ route('marketplace.products.images.show', [$product, $product->images->first()]) }}" alt="{{ $product->images->first()->alt_text }}" width="112" height="112" loading="lazy" class="size-28 rounded-control border border-line object-cover">
                    @else
                        <div class="flex size-28 items-center justify-center rounded-control border border-line bg-canvas text-sm text-muted">Tanpa foto</div>
                    @endif
                    <div class="min-w-0">
                        <h2 class="break-words font-semibold">{{ $product->name }}</h2>
                        <p class="mt-1 text-sm text-muted">Rp{{ number_format((float) $product->price, 0, ',', '.') }} · Status arsip</p>
                        @if($archivedEvent)<p class="mt-1 text-sm text-muted">Diarsipkan {{ $archivedEvent->created_at->format('d/m/Y H:i') }}</p>@endif
                    </div>
                    <form method="POST" action="{{ route('marketplace.seller.products.restore', $product) }}" data-confirm="Pulihkan {{ $product->name }} ke daftar produk toko?" class="sm:justify-self-end">
                        @csrf
                        <x-ui.button type="submit" variant="secondary">Pulihkan produk</x-ui.button>
                    </form>
                </article>
            @empty
                <x-ui.empty-state title="Tidak ada produk arsip" description="Produk yang Anda arsipkan akan muncul di sini dan dapat dipulihkan." />
            @endforelse
            {{ $products->links() }}
        </section>
    </main>
</x-layouts.app>
