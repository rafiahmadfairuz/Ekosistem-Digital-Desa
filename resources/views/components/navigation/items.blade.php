@props(['groups'])
<nav aria-label="Navigasi aplikasi" class="space-y-5">
    @foreach ($groups as $group => $items)
        <section aria-labelledby="nav-group-{{ \Illuminate\Support\Str::slug($group) }}">
            <h2 id="nav-group-{{ \Illuminate\Support\Str::slug($group) }}" class="flex min-h-9 items-center rounded-control border border-brand/25 bg-brand/5 px-3 text-xs font-semibold text-brand-strong">{{ $group }}</h2>
            <ul class="mt-1 space-y-0.5">
                @foreach ($items as $item)
                    @php($isCurrent = request()->routeIs($item['route']) || ($item['route'] === 'officer.residents.index' && request()->routeIs('officer.residents.show')))
                    <li>
                        <a href="{{ route($item['route']) }}" @if ($isCurrent) aria-current="page" @endif class="flex min-h-11 min-w-0 items-center rounded-control px-3 text-sm {{ $isCurrent ? 'bg-slate-200 text-ink font-semibold' : 'text-muted hover:bg-slate-100 hover:text-ink' }} focus-visible:outline-offset-2">
                            <span class="break-words">{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endforeach
</nav>
