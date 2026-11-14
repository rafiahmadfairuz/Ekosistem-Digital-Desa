@props(['groups'])
<style>
    #app-sidebar .overflow-y-auto,
    #sidebar-nav-container {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    #app-sidebar .overflow-y-auto::-webkit-scrollbar,
    #sidebar-nav-container::-webkit-scrollbar {
        display: none;
        width: 0;
        height: 0;
    }
    .app-nav-group-heading {
        color: #94a3b8 !important;
    }
    .app-nav-link {
        color: #64748b !important;
    }
    .app-nav-link svg {
        color: #64748b !important;
    }
    .app-nav-link:hover {
        background-color: #ecfdf5 !important;
        color: #047857 !important;
    }
    .app-nav-link:hover svg {
        color: #059669 !important;
    }
    .app-nav-link[aria-current="page"] {
        background-color: #059669 !important;
        color: #ffffff !important;
    }
    .app-nav-link[aria-current="page"] svg {
        color: #ffffff !important;
    }
</style>
<nav aria-label="Navigasi aplikasi" class="space-y-5">
    @foreach ($groups as $group => $items)
        <section aria-labelledby="nav-group-{{ \Illuminate\Support\Str::slug($group) }}">
            <h2 id="nav-group-{{ \Illuminate\Support\Str::slug($group) }}" data-nav-heading class="app-nav-group-heading nav-group-heading px-3.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $group }}</h2>
            <ul class="mt-2 space-y-1">
                @foreach ($items as $item)
                    @php($isCurrent = request()->routeIs($item['route']) || ($item['route'] === 'officer.residents.index' && request()->routeIs('officer.residents.show')))
                    <li>
                        <a href="{{ route($item['route']) }}" @if ($isCurrent) aria-current="page" @endif data-sidebar-tooltip="{{ $item['label'] }}" class="app-nav-link nav-item-link group flex min-h-11 min-w-0 items-center gap-3.5 rounded-xl px-3.5 text-sm transition-colors duration-150 {{ $isCurrent ? 'bg-emerald-600 text-white font-semibold shadow-xs' : 'font-medium text-slate-500 hover:bg-emerald-50 hover:text-emerald-700' }} focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                            @if (\Illuminate\Support\Str::contains($item['route'], ['dashboard', 'home', 'overview']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="3" y="3" width="7" height="7" rx="1.5" />
                                    <rect x="14" y="3" width="7" height="7" rx="1.5" />
                                    <rect x="14" y="14" width="7" height="7" rx="1.5" />
                                    <rect x="3" y="14" width="7" height="7" rx="1.5" />
                                </svg>
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace', 'market', 'shop', 'store']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                                    <path d="M3 6h18" />
                                    <path d="M16 10a4 4 0 0 1-8 0" />
                                </svg>
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['cart', 'checkout']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="9" cy="21" r="1" />
                                    <circle cx="20" cy="21" r="1" />
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
                                </svg>
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['order', 'transaction', 'invoice', 'payment']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                    <polyline points="14 2 14 8 20 8" />
                                    <line x1="16" y1="13" x2="8" y2="13" />
                                    <line x1="16" y1="17" x2="8" y2="17" />
                                </svg>
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['product', 'catalog', 'stock', 'inventory']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="m7.5 4.27 9 5.15" />
                                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                                    <path d="m3.3 7 8.7 5 8.7-5" />
                                    <path d="M12 22V12" />
                                </svg>
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['resident', 'citizen', 'member', 'user', 'officer', 'account']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['report', 'analytic', 'stat', 'finance', 'ledger']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <line x1="18" y1="20" x2="18" y2="10" />
                                    <line x1="12" y1="20" x2="12" y2="4" />
                                    <line x1="6" y1="20" x2="6" y2="14" />
                                </svg>
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['message', 'chat', 'inbox', 'support']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                                </svg>
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['setting', 'config', 'profile', 'role', 'permission']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="3" />
                                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z" />
                                </svg>
                            @else
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="9" />
                                    <path d="M12 8v4l2.5 2.5" />
                                </svg>
                            @endif
                            <span class="nav-item-label break-words">{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </section>
    @endforeach
</nav>
