@props(['items' => []])
<nav aria-label="Jejak navigasi" {{ $attributes->class('mb-5 text-sm') }}>
    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
        @foreach ($items as $item)
            <li class="flex items-center gap-2">
                @if (! $loop->first)
                    <span class="text-muted" aria-hidden="true">/</span>
                @endif
                @if (! $loop->last && isset($item['url']))
                    <a href="{{ $item['url'] }}" class="min-h-11 inline-flex items-center text-muted underline underline-offset-4 hover:text-ink">{{ $item['label'] }}</a>
                @else
                    <span @if ($loop->last) aria-current="page" @endif class="min-h-11 inline-flex items-center font-medium">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
