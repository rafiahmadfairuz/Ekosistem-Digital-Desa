@props(['title' => config('app.name')])
@php
    $user = auth()->user();
    $navigationGroups = $user ? app(\App\Services\NavigationMenu::class)->for($user) : [];
    $accountRoles = $user?->roles->pluck('name') ?? collect();
    $unreadNotificationCount = $user
        ? \Illuminate\Support\Facades\DB::table('notifications')->where('user_id', $user->getKey())->whereNull('read_at')->count()
        : 0;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#F4F6F9">
    <title>{{ $title }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh bg-canvas text-ink antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:bg-paper focus:px-4 focus:py-3 focus:ring-2 focus:ring-brand">Lewati ke konten</a>

    <div class="min-h-dvh lg:flex">
        <aside class="hidden w-60 shrink-0 flex-col border-r border-line bg-canvas px-4 py-5 lg:sticky lg:top-0 lg:flex lg:h-dvh" aria-label="Bilah sisi aplikasi">
            <a href="{{ $user ? route('dashboard') : route('home') }}" class="flex min-h-12 items-center gap-3 rounded-control px-2 font-semibold text-ink focus-visible:outline-offset-2">
                <svg class="size-8 shrink-0 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m3 10 9-6.5 9 6.5M5.5 9.5V20h13V9.5M10 20v-6h4v6" />
                    <path d="M8 11h.01M16 11h.01" />
                </svg>
                <span class="min-w-0 truncate" translate="no">{{ config('app.name') }}</span>
            </a>

            @if($user)
                <a href="{{ route('notifications.index') }}" class="mt-4 flex min-h-11 items-center justify-between gap-3 rounded-control border border-line bg-paper px-3 text-sm font-medium hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2" @if(request()->routeIs('notifications.*')) aria-current="page" @endif>
                    <span>Pemberitahuan</span>
                    @if ($unreadNotificationCount > 0)
                        <span class="inline-flex min-w-6 items-center justify-center rounded-full bg-brand-strong px-2 py-1 text-xs font-semibold text-white" aria-label="{{ $unreadNotificationCount }} belum dibaca">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                    @endif
                </a>
                <div class="mt-5 min-h-0 flex-1 overflow-y-auto">
                    <x-navigation.items :groups="$navigationGroups" />
                </div>
                <div class="relative mt-4 border-t border-line pt-4">
                <details class="group">
                    <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-2 rounded-control px-2 text-left text-sm hover:bg-slate-100 focus-visible:outline-offset-2 [&::-webkit-details-marker]:hidden">
                        <span class="min-w-0">
                            <span class="block truncate font-medium">{{ $user->username }}</span>
                            <span class="mt-0.5 block truncate text-xs text-muted">Akun dan akses</span>
                        </span>
                        <svg class="size-4 shrink-0 transition-transform group-open:rotate-180 motion-reduce:transition-none" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="m5 7 5 5 5-5" /></svg>
                    </summary>
                    <div class="absolute bottom-full left-0 z-20 mb-2 w-full rounded-card border border-line bg-paper p-3 shadow-menu">
                        <p class="break-words text-sm font-medium">{{ $user->username }}</p>
                        <p class="mt-1 text-xs leading-5 text-muted">{{ $accountRoles->join(' · ') }}</p>
                        <form method="POST" action="{{ route('logout') }}" class="mt-2 border-t border-line pt-2">
                            @csrf
                            <x-ui.button type="submit" variant="plain" class="w-full justify-start rounded-control px-2 text-left text-sm">Keluar</x-ui.button>
                        </form>
                    </div>
                </details>
                </div>
            @else
                <nav class="mt-5 flex-1" aria-label="Navigasi pasar desa">
                    <a href="{{ route('marketplace.index') }}" class="flex min-h-11 items-center rounded-control bg-slate-200 px-3 font-medium text-ink">Pasar desa</a>
                </nav>
                <div class="mt-4 flex flex-col gap-2 border-t border-line pt-4">
                    <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center justify-center rounded-control border border-line px-3 font-medium">Masuk</a>
                    <a href="{{ route('register') }}" class="inline-flex min-h-11 items-center justify-center rounded-control bg-brand-strong px-3 font-medium text-white">Daftar</a>
                </div>
            @endif
        </aside>

        <div class="min-w-0 flex-1">
            <header class="flex min-h-12 items-center justify-between gap-3 border-b border-line bg-paper px-shell lg:hidden">
                <a href="{{ $user ? route('dashboard') : route('home') }}" class="flex min-h-11 min-w-0 items-center gap-2 font-semibold" aria-label="{{ config('app.name') }}{{ $user ? ', ringkasan' : '' }}">
                    <svg class="size-7 shrink-0 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 10 9-6.5 9 6.5M5.5 9.5V20h13V9.5M10 20v-6h4v6" /></svg>
                    <span class="min-w-0 truncate text-sm" translate="no">{{ config('app.name') }}</span>
                </a>
                <span class="min-w-0 flex-1 truncate text-right text-sm text-muted">{{ $title }}</span>
                @if($user)
                <a href="{{ route('notifications.index') }}" class="relative inline-flex size-11 shrink-0 items-center justify-center rounded-control border border-line text-ink hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2" aria-label="Pemberitahuan{{ $unreadNotificationCount > 0 ? ', '.$unreadNotificationCount.' belum dibaca' : '' }}">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" /></svg>
                    @if ($unreadNotificationCount > 0)<span class="absolute right-1 top-1 size-2.5 rounded-full bg-danger" aria-hidden="true"></span>@endif
                </a>
                <button type="button" data-mobile-menu-open aria-controls="mobile-navigation" aria-haspopup="dialog" class="inline-flex size-11 shrink-0 items-center justify-center rounded-control text-ink hover:bg-slate-100 focus-visible:outline-offset-2" aria-label="Buka menu navigasi">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
                </button>
                @else
                    <a href="{{ route('login') }}" class="inline-flex min-h-11 shrink-0 items-center rounded-control border border-line px-3 text-sm font-medium">Masuk</a>
                @endif
            </header>

            <div class="min-h-[calc(100dvh-3rem)]">
                @if (session('status'))
                    <div class="mx-auto max-w-7xl px-4 pt-5 sm:px-8">
                        <x-ui.alert type="success" role="status">{{ session('status') }}</x-ui.alert>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mx-auto max-w-7xl px-4 pt-5 sm:px-8">
                        <x-ui.alert type="error" tabindex="-1">
                            <p class="font-semibold">Periksa isian berikut.</p>
                            <ul class="mt-1 list-inside list-disc">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </x-ui.alert>
                    </div>
                @endif
                {{ $slot }}
            </div>
        </div>
    </div>

    <aside data-cart-added-toast role="status" aria-live="polite" aria-atomic="true" aria-labelledby="cart-added-title" aria-describedby="cart-added-description" @if(! session('cart_added')) hidden @else data-cart-added-animated @endif class="fixed inset-x-4 top-4 z-50 mx-auto w-auto max-w-md rounded-card border border-line bg-paper p-4 text-ink shadow-menu sm:left-auto sm:right-6 sm:top-6 sm:mx-0 sm:w-[min(92vw,28rem)]">
        <div class="flex items-start gap-3">
            <span data-cart-added-check aria-hidden="true" class="inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-success-bg text-lg font-bold text-success-ink">✓</span>
            <div class="min-w-0 flex-1">
                <h2 id="cart-added-title" data-cart-added-title class="font-semibold">{{ session('cart_added') ? 'Ditambahkan ke keranjang' : '' }}</h2>
                <p id="cart-added-description" data-cart-added-description class="mt-1 break-words text-sm text-muted">
                    @if (session('cart_added'))
                        {{ session('cart_added.product') }} · {{ session('cart_added.quantity') }} item
                    @endif
                </p>
            </div>
            <button type="button" data-cart-added-close class="inline-flex size-11 shrink-0 items-center justify-center rounded-control text-xl hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2" aria-label="Tutup pemberitahuan">×</button>
        </div>
        <div class="mt-3 flex justify-end border-t border-line pt-3">
            <a href="{{ route('marketplace.cart') }}" class="inline-flex min-h-11 items-center rounded-control bg-brand-strong px-4 font-semibold text-white hover:bg-brand focus-visible:outline-2 focus-visible:outline-offset-2">Lihat keranjang</a>
        </div>
    </aside>

    @if($user)
    <dialog id="mobile-navigation" aria-labelledby="mobile-navigation-title" class="m-0 h-dvh w-full max-h-none max-w-none border-0 bg-canvas p-0 text-ink backdrop:bg-ink/40">
        <div class="flex h-full flex-col overflow-y-auto px-5 pb-[env(safe-area-inset-bottom)] pt-[env(safe-area-inset-top)]">
            <div class="flex min-h-14 items-center justify-between gap-3 border-b border-line">
                <h2 id="mobile-navigation-title" class="text-base font-semibold">{{ config('app.name') }}</h2>
                <button type="button" data-mobile-menu-close class="inline-flex size-11 items-center justify-center rounded-control hover:bg-slate-100 focus-visible:outline-offset-2" aria-label="Tutup menu navigasi">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" /></svg>
                </button>
            </div>
            <div class="min-h-0 flex-1 overflow-y-auto py-5">
                <x-navigation.items :groups="$navigationGroups" />
            </div>
            <div class="border-t border-line py-4">
                <p class="break-words font-medium">{{ $user->username }}</p>
                <p class="mt-1 text-sm leading-6 text-muted">{{ $accountRoles->join(' · ') }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <x-ui.button type="submit" variant="plain" class="rounded-control px-2 text-sm">Keluar</x-ui.button>
                </form>
            </div>
        </div>
    </dialog>
    @endif
</body>
</html>
