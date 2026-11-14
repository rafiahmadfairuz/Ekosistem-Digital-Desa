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
    <meta name="theme-color" content="#FFFFFF">
    <title>{{ $title }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        #app-sidebar {
            width: 16.5rem;
            background-color: #ffffff;
            border-right: 1px solid #cbd5e1;
            box-shadow: 4px 0 24px -4px rgba(15, 23, 42, 0.09), 1px 0 6px -1px rgba(15, 23, 42, 0.05);
            transition: width 0.22s cubic-bezier(0.4, 0, 0.2, 1);
        }
        #app-sidebar[data-collapsed="true"] {
            width: 5rem;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }
        #app-sidebar .sidebar-topbar {
            height: 4rem;
            border-bottom: 1px solid #cbd5e1;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        #app-sidebar[data-collapsed="true"] .sidebar-topbar {
            justify-content: center;
            gap: 0;
        }
        #app-sidebar .sidebar-toggle-btn {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            border: 1px solid transparent;
            background-color: #ffffff;
            color: #475569;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            cursor: pointer;
            transition: background-color 0.16s ease, color 0.16s ease, border-color 0.16s ease;
        }
        #app-sidebar .sidebar-toggle-btn:hover {
            background-color: #ecfdf5;
            color: #047857;
            border-color: #a7f3d0;
        }
        #app-sidebar[data-collapsed="true"] .sidebar-text,
        #app-sidebar[data-collapsed="true"] .sidebar-section-label,
        #app-sidebar[data-collapsed="true"] .sidebar-badge-pill,
        #app-sidebar[data-collapsed="true"] .sidebar-chevron {
            display: none !important;
        }
        #app-sidebar[data-collapsed="true"] .sidebar-item-link,
        #app-sidebar[data-collapsed="true"] .sidebar-logout-btn {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
            gap: 0;
        }
        #app-sidebar[data-collapsed="true"] #sidebar-nav-container h1,
        #app-sidebar[data-collapsed="true"] #sidebar-nav-container h2,
        #app-sidebar[data-collapsed="true"] #sidebar-nav-container h3,
        #app-sidebar[data-collapsed="true"] #sidebar-nav-container h4,
        #app-sidebar[data-collapsed="true"] #sidebar-nav-container p,
        #app-sidebar[data-collapsed="true"] #sidebar-nav-container [data-nav-heading] {
            display: none !important;
        }
        #app-sidebar[data-collapsed="true"] #sidebar-nav-container section + section {
            border-top: 1px solid #cbd5e1;
            padding-top: 0.75rem;
            margin-top: 0.75rem;
        }
        #app-sidebar[data-collapsed="true"] #sidebar-nav-container a {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
            gap: 0;
            font-size: 0;
            line-height: 0;
        }
        #app-sidebar[data-collapsed="true"] #sidebar-nav-container a svg {
            width: 1.25rem;
            height: 1.25rem;
            flex-shrink: 0;
        }
        #app-sidebar[data-collapsed="true"] #sidebar-nav-container a span:not(:has(svg)) {
            display: none !important;
        }
        #sidebar-nav-container section + section {
            border-top: 1px solid #e2e8f0;
            padding-top: 1rem;
            margin-top: 1rem;
        }
        #sidebar-nav-container a {
            border-radius: 0.75rem;
            transition: background-color 0.16s ease, color 0.16s ease;
            color: #64748b;
            font-weight: 500;
        }
        #sidebar-nav-container a svg {
            color: #64748b;
            transition: color 0.16s ease;
        }
        #sidebar-nav-container a:hover {
            background-color: #ecfdf5;
            color: #047857;
        }
        #sidebar-nav-container a:hover svg {
            color: #059669;
        }
        #sidebar-nav-container a[aria-current="page"] {
            background-color: #059669;
            color: #ffffff;
            font-weight: 600;
        }
        #sidebar-nav-container a[aria-current="page"] svg {
            color: #ffffff;
        }
        .sidebar-divider-top {
            border-top: 1px solid #e2e8f0;
        }
        .sidebar-logout-btn {
            color: #64748b;
            transition: background-color 0.16s ease, color 0.16s ease;
        }
        .sidebar-logout-btn svg {
            color: #64748b;
            transition: color 0.16s ease;
        }
        .sidebar-logout-btn:hover {
            background-color: #ecfdf5;
            color: #047857;
        }
        .sidebar-logout-btn:hover svg {
            color: #059669;
        }
        #app-top-header {
            height: 4rem;
            background-color: #ffffff;
            border-bottom: 1px solid #cbd5e1;
            box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.08), 0 1px 4px -1px rgba(15, 23, 42, 0.04);
        }
        .app-content-surface {
            background-color: #f8faf9;
        }
        .header-page-title {
            color: #047857;
            font-weight: 700;
        }
        .header-notif-btn {
            position: relative;
            width: 2.625rem;
            height: 2.625rem;
            border-radius: 0.75rem;
            color: #64748b;
            background-color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background-color 0.16s ease, color 0.16s ease;
        }
        .header-notif-btn:hover {
            background-color: #ecfdf5;
            color: #047857;
        }
        .header-notif-badge {
            position: absolute;
            top: 0.125rem;
            right: 0.125rem;
            min-width: 1.125rem;
            height: 1.125rem;
            padding: 0 0.25rem;
            border-radius: 9999px;
            background-color: #059669;
            color: #ffffff;
            font-size: 0.65rem;
            font-weight: 700;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 0 2px #ffffff;
        }
        .header-vertical-divider {
            width: 1px;
            height: 1.875rem;
            background-color: #cbd5e1;
            flex-shrink: 0;
        }
        .header-profile-avatar {
            width: 2.375rem;
            height: 2.375rem;
            border-radius: 9999px;
            background-color: #065f46;
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
    </style>
</head>
<body class="min-h-dvh bg-white text-slate-700 antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-xl focus:bg-white focus:px-4 focus:py-3 focus:text-slate-800 focus:shadow-lg focus:ring-2 focus:ring-emerald-600">Lewati ke konten</a>

    <div class="min-h-dvh lg:flex">
        <aside id="app-sidebar" data-collapsed="false" class="relative z-30 hidden shrink-0 flex-col bg-white px-4 pb-5 lg:sticky lg:top-0 lg:flex lg:h-dvh" aria-label="Bilah sisi aplikasi">
            <div class="sidebar-topbar shrink-0">
                <button type="button" id="sidebar-collapse-btn" aria-expanded="true" aria-controls="app-sidebar" aria-label="Ciutkan atau luaskan bilah sisi" data-sidebar-tooltip="Luaskan menu" class="sidebar-toggle-btn focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </button>

                <a href="{{ $user ? route('dashboard') : route('home') }}" class="sidebar-text min-w-0 flex-1 truncate text-xl font-bold tracking-tight text-slate-800 transition hover:text-emerald-700 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" translate="no">
                      <img
                            src="{{ asset('images/logo.png') }}"
                            alt="{{ config('app.name') }}"
                            class="h-6 w-auto max-w-32 object-contain sm:h-8"
                        >
                </a>
            </div>

            @if($user)
                <div class="min-h-0 flex-1 overflow-y-auto pt-4">
                    <div id="sidebar-nav-container">
                        <x-navigation.items :groups="$navigationGroups" />
                    </div>

                    <div class="sidebar-divider-top mt-4 pt-4">
                        <div class="sidebar-section-label mb-1.5 px-3.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                            LAINNYA
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" data-sidebar-tooltip="Keluar" class="sidebar-logout-btn group flex min-h-11 w-full items-center gap-3.5 rounded-xl px-3.5 text-left text-sm font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                                <svg class="size-5 shrink-0 transition-colors" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                    <polyline points="16 17 21 12 16 7" />
                                    <line x1="21" y1="12" x2="9" y2="12" />
                                </svg>
                                <span class="sidebar-text truncate">Keluar</span>
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="min-h-0 flex-1 overflow-y-auto pt-4">
                    <div class="sidebar-section-label mb-1.5 px-3.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                        UTAMA
                    </div>
                    <nav aria-label="Navigasi pasar desa">
                        <a href="{{ route('marketplace.index') }}" data-sidebar-tooltip="Pasar desa" class="sidebar-item-link flex min-h-11 items-center gap-3.5 rounded-xl bg-emerald-600 px-3.5 font-semibold text-white transition hover:bg-emerald-700">
                            <svg class="size-5 shrink-0 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="3" width="7" height="7" rx="1.5" />
                                <rect x="14" y="3" width="7" height="7" rx="1.5" />
                                <rect x="14" y="14" width="7" height="7" rx="1.5" />
                                <rect x="3" y="14" width="7" height="7" rx="1.5" />
                            </svg>
                            <span class="sidebar-text truncate">Pasar desa</span>
                        </a>
                    </nav>
                    <div class="sidebar-divider-top mt-4 flex flex-col gap-2 pt-4">
                        <a href="{{ route('login') }}" data-sidebar-tooltip="Masuk" class="sidebar-item-link inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-3 text-sm font-semibold text-slate-600 transition hover:border-emerald-600 hover:bg-emerald-50 hover:text-emerald-700">
                            <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3" /></svg>
                            <span class="sidebar-text">Masuk</span>
                        </a>
                        <a href="{{ route('register') }}" data-sidebar-tooltip="Daftar" class="sidebar-item-link inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-3 text-sm font-semibold text-white transition hover:bg-emerald-700">
                            <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                            <span class="sidebar-text">Daftar</span>
                        </a>
                    </div>
                </div>
            @endif
        </aside>

        <div class="app-content-surface min-w-0 flex-1">
            <header id="app-top-header" class="sticky top-0 z-20 flex items-center justify-between gap-3 bg-white px-4 sm:px-8">
                <div class="flex min-w-0 items-center gap-3">
                    @if($user)
                    <button type="button" data-mobile-menu-open aria-controls="mobile-navigation" aria-haspopup="dialog" class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-900 transition hover:border-emerald-600 hover:bg-emerald-50 hover:text-emerald-700 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 lg:hidden" aria-label="Buka menu navigasi">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
                    </button>
                    @endif

                    <a href="{{ $user ? route('dashboard') : route('home') }}" class="flex min-h-11 min-w-0 items-center transition focus-visible:outline-offset-2 focus-visible:outline-emerald-600 lg:hidden" aria-label="{{ config('app.name') }}{{ $user ? ', ringkasan' : '' }}" translate="no">
                        {{-- <img
                            src="{{ asset('images/logo.png') }}"
                            alt="{{ config('app.name') }}"
                            class="h-7 w-auto max-w-32 object-contain sm:h-8"
                        > --}}
                    </a>

                    <span class="hidden h-5 w-px bg-slate-300 sm:inline-block lg:hidden" aria-hidden="true"></span>
                    <span class="header-page-title hidden min-w-0 truncate text-base tracking-tight sm:inline-block sm:text-lg">{{ $title }}</span>
                </div>

                <div class="flex shrink-0 items-center gap-3.5">
                    <span class="header-page-title max-w-28 truncate text-right text-sm sm:hidden">{{ $title }}</span>

                    @if($user)
                    <a href="{{ route('notifications.index') }}" class="header-notif-btn focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" aria-label="Pemberitahuan{{ $unreadNotificationCount > 0 ? ', '.$unreadNotificationCount.' belum dibaca' : '' }}">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                        </svg>
                        @if ($unreadNotificationCount > 0)
                            <span class="header-notif-badge" aria-hidden="true">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</span>
                        @endif
                    </a>

                    <span class="header-vertical-divider" aria-hidden="true"></span>

                    <div class="flex items-center gap-3">
                        <span class="header-profile-avatar" aria-hidden="true">
                            {{ mb_substr($user->username, 0, 1) }}
                        </span>
                        <div class="min-w-0 text-left">
                            <p class="max-w-36 truncate text-sm font-bold leading-tight text-slate-800 sm:max-w-48">{{ $user->username }}</p>
                            <p class="mt-0.5 max-w-36 truncate text-xs font-medium leading-tight text-slate-500 sm:max-w-48">{{ $accountRoles->isNotEmpty() ? $accountRoles->join(' · ') : 'Pengguna' }}</p>
                        </div>
                    </div>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex min-h-10 shrink-0 items-center rounded-xl border border-slate-300 bg-white px-3.5 text-sm font-semibold text-slate-600 transition hover:border-emerald-600 hover:bg-emerald-50 hover:text-emerald-700">Masuk</a>
                        <a href="{{ route('register') }}" class="hidden min-h-10 shrink-0 items-center rounded-xl bg-emerald-600 px-3.5 text-sm font-semibold text-white transition hover:bg-emerald-700 sm:inline-flex">Daftar</a>
                    @endif
                </div>
            </header>

            <div class="app-content-surface min-h-[calc(100dvh-4rem)]">
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

    <div id="sidebar-hover-tooltip" role="tooltip" style="border: 1px solid #6ee7b7; background-color: #ffffff; color: #334155;" class="pointer-events-none fixed left-0 top-0 z-50 hidden rounded-xl px-3 py-1.5 text-xs font-semibold shadow-lg"></div>

    <aside data-cart-added-toast role="status" aria-live="polite" aria-atomic="true" aria-labelledby="cart-added-title" aria-describedby="cart-added-description" @if(! session('cart_added')) hidden @else data-cart-added-animated @endif class="fixed inset-x-4 top-4 z-50 mx-auto w-auto max-w-md rounded-2xl border border-emerald-200 bg-white p-4 text-slate-800 shadow-xl sm:left-auto sm:right-6 sm:top-6 sm:mx-0 sm:w-[min(92vw,28rem)]">
        <div class="flex items-start gap-3">
            <span data-cart-added-check aria-hidden="true" class="inline-flex size-10 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-lg font-bold text-emerald-700 ring-1 ring-emerald-200">✓</span>
            <div class="min-w-0 flex-1">
                <h2 id="cart-added-title" data-cart-added-title class="font-semibold text-slate-800">{{ session('cart_added') ? 'Ditambahkan ke keranjang' : '' }}</h2>
                <p id="cart-added-description" data-cart-added-description class="mt-1 break-words text-sm text-slate-500">
                    @if (session('cart_added'))
                        {{ session('cart_added.product') }} · {{ session('cart_added.quantity') }} item
                    @endif
                </p>
            </div>
            <button type="button" data-cart-added-close class="inline-flex size-11 shrink-0 items-center justify-center rounded-xl text-xl text-slate-500 transition hover:bg-emerald-50 hover:text-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" aria-label="Tutup pemberitahuan">×</button>
        </div>
        <div class="mt-3 flex justify-end border-t border-slate-200 pt-3">
            <a href="{{ route('marketplace.cart') }}" class="inline-flex min-h-11 items-center rounded-xl bg-emerald-600 px-4 font-semibold text-white transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">Lihat keranjang</a>
        </div>
    </aside>

    @if($user)
    <dialog id="mobile-navigation" aria-labelledby="mobile-navigation-title" class="m-0 h-dvh w-full max-h-none max-w-none border-0 bg-white p-0 text-slate-950 backdrop:bg-slate-900/30">
        <div class="flex h-full flex-col overflow-y-auto px-5 pb-[env(safe-area-inset-bottom)] pt-[env(safe-area-inset-top)]">
            <div class="flex min-h-16 items-center justify-between gap-3 border-b border-slate-300 shadow-xs">
                <a href="{{ route('dashboard') }}" id="mobile-navigation-title" class="flex items-center" aria-label="{{ config('app.name') }}" translate="no">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="{{ config('app.name') }}"
                        class="h-8 w-auto object-contain"
                    >
                </a>
                <button type="button" data-mobile-menu-close class="inline-flex size-11 items-center justify-center rounded-xl border border-slate-300 text-slate-800 transition hover:border-emerald-600 hover:bg-emerald-50 hover:text-emerald-700 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" aria-label="Tutup menu navigasi">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" /></svg>
                </button>
            </div>
            <div class="min-h-0 flex-1 overflow-y-auto py-4">
                <div class="divide-y divide-slate-300">
                    <x-navigation.items :groups="$navigationGroups" />
                </div>
                <div class="mt-4 border-t border-slate-300 pt-4">
                    <div class="mb-2 px-1 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        LAINNYA
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-ui.button type="submit" variant="plain" class="w-full justify-start rounded-xl px-3 py-2.5 text-sm font-medium text-slate-900 hover:bg-emerald-50 hover:text-emerald-700">Keluar</x-ui.button>
                    </form>
                </div>
            </div>
        </div>
    </dialog>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mobileDialog = document.getElementById('mobile-navigation');
            const mobileOpenBtns = document.querySelectorAll('[data-mobile-menu-open]');
            const mobileCloseBtns = document.querySelectorAll('[data-mobile-menu-close]');

            if (mobileDialog) {
                const openMobileMenu = function () {
                    if (typeof mobileDialog.showModal === 'function') {
                        if (!mobileDialog.open) {
                            mobileDialog.showModal();
                        }
                    } else {
                        mobileDialog.setAttribute('open', '');
                    }
                    document.body.style.overflow = 'hidden';
                };

                const closeMobileMenu = function () {
                    if (typeof mobileDialog.close === 'function') {
                        if (mobileDialog.open) {
                            mobileDialog.close();
                        }
                    } else {
                        mobileDialog.removeAttribute('open');
                    }
                    document.body.style.overflow = '';
                };

                mobileOpenBtns.forEach(function (btn) {
                    btn.addEventListener('click', openMobileMenu);
                });

                mobileCloseBtns.forEach(function (btn) {
                    btn.addEventListener('click', closeMobileMenu);
                });

                mobileDialog.addEventListener('close', function () {
                    document.body.style.overflow = '';
                });

                mobileDialog.addEventListener('click', function (event) {
                    if (event.target === mobileDialog) {
                        closeMobileMenu();
                    }
                });

                mobileDialog.querySelectorAll('a').forEach(function (link) {
                    link.addEventListener('click', closeMobileMenu);
                });
            }

            const sidebar = document.getElementById('app-sidebar');
            const toggleBtn = document.getElementById('sidebar-collapse-btn');
            const navContainer = document.getElementById('sidebar-nav-container');
            const tooltip = document.getElementById('sidebar-hover-tooltip');

            if (navContainer) {
                navContainer.querySelectorAll('a').forEach(function (link) {
                    const labelText = link.textContent.trim();
                    if (labelText && !link.hasAttribute('data-sidebar-tooltip')) {
                        link.setAttribute('data-sidebar-tooltip', labelText);
                    }
                });
            }

            if (sidebar && tooltip) {
                const showTooltip = function (el) {
                    if (sidebar.getAttribute('data-collapsed') !== 'true') {
                        tooltip.classList.add('hidden');
                        return;
                    }
                    const text = el.getAttribute('data-sidebar-tooltip');
                    if (!text) return;
                    const rect = el.getBoundingClientRect();
                    tooltip.textContent = text;
                    tooltip.classList.remove('hidden');
                    const tipRect = tooltip.getBoundingClientRect();
                    tooltip.style.left = (rect.right + 12) + 'px';
                    tooltip.style.top = (rect.top + (rect.height - tipRect.height) / 2) + 'px';
                };

                const hideTooltip = function () {
                    tooltip.classList.add('hidden');
                };

                sidebar.querySelectorAll('[data-sidebar-tooltip]').forEach(function (item) {
                    item.addEventListener('mouseenter', function () { showTooltip(item); });
                    item.addEventListener('mouseleave', hideTooltip);
                    item.addEventListener('focus', function () { showTooltip(item); });
                    item.addEventListener('blur', hideTooltip);
                });

                const scrollArea = sidebar.querySelector('.overflow-y-auto');
                if (scrollArea) {
                    scrollArea.addEventListener('scroll', hideTooltip, { passive: true });
                }
            }

            if (sidebar && toggleBtn) {
                const storageKey = 'app_sidebar_collapsed';
                const applyState = function (collapsed) {
                    sidebar.setAttribute('data-collapsed', collapsed ? 'true' : 'false');
                    toggleBtn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                    if (!collapsed && tooltip) {
                        tooltip.classList.add('hidden');
                    }
                };

                try {
                    if (localStorage.getItem(storageKey) === 'true') {
                        applyState(true);
                    }
                } catch (e) {}

                toggleBtn.addEventListener('click', function () {
                    const isCollapsed = sidebar.getAttribute('data-collapsed') === 'true';
                    const nextState = !isCollapsed;
                    applyState(nextState);
                    try {
                        localStorage.setItem(storageKey, nextState ? 'true' : 'false');
                    } catch (e) {}
                });
            }
        });
    </script>
</body>
</html>
