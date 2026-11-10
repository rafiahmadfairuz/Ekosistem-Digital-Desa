@props(['title' => null, 'description' => null])
<section {{ $attributes->class('min-w-0 rounded-card border border-line bg-paper p-5 sm:p-6') }}>
    @if ($title)
        <div class="mb-4">
            <h2 class="text-base font-semibold">{{ $title }}</h2>
            @if ($description)
                <p class="mt-1 text-sm leading-6 text-muted">{{ $description }}</p>
            @endif
        </div>
    @endif
    {{ $slot }}
</section>
