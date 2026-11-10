@props(['title', 'description', 'action' => null, 'href' => null])
<section {{ $attributes->class('rounded-card border border-line bg-paper px-5 py-6') }} aria-labelledby="{{ $attributes->get('id', 'empty-state-title') }}">
    <h2 id="{{ $attributes->get('id', 'empty-state-title') }}" class="text-base font-semibold">{{ $title }}</h2>
    <p class="mt-2 max-w-2xl text-sm leading-6 text-muted">{{ $description }}</p>
    @if ($action && $href)
        <a href="{{ $href }}" class="mt-4 inline-flex min-h-11 items-center font-medium text-ink underline underline-offset-4 hover:text-emerald-900">{{ $action }}</a>
    @endif
    {{ $slot }}
</section>
