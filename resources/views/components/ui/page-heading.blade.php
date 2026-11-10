@props(['title', 'description' => null])
<header {{ $attributes->class('flex flex-col justify-between gap-4 sm:flex-row sm:items-end') }}>
    <div class="min-w-0">
        <h1 class="text-2xl font-semibold tracking-tight text-ink sm:text-[1.75rem]">{{ $title }}</h1>
        @if ($description)
            <p class="mt-2 max-w-3xl text-base leading-7 text-muted">{{ $description }}</p>
        @endif
    </div>
    @if (isset($actions))
        <div class="flex shrink-0 flex-wrap gap-2">{{ $actions }}</div>
    @endif
</header>
