@props(['title' => config('app.name')])
@php
    $user = auth()->user();
    $navigationGroups = $user ? app(\App\Services\NavigationMenu::class)->for($user) : [];
    $accountRoles = $user?->roles->map(fn ($role) => \App\Models\Role::displayName($role->name)) ?? collect();
    $recentNotifications = $user
        ? \Illuminate\Support\Facades\DB::table('notifications')->where('user_id', $user->getKey())->orderByDesc('created_at')->orderByDesc('id')->limit(8)->get()
        : collect();
    $unreadNotificationCount = $user
        ? \Illuminate\Support\Facades\DB::table('notifications')->where('user_id', $user->getKey())->whereNull('read_at')->count()
        : 0;
    $canReviewPendingAccounts = $user && ($user->hasRole('Super Admin') || $user->hasRole('Village Admin'));
    $pendingActivationUsers = $canReviewPendingAccounts
        ? \App\Models\User::query()->where('account_status', 'pending')->orderBy('created_at')->limit(8)->get(['id', 'username', 'email', 'email_verified_at'])
        : collect();
    $pendingActivationCount = $canReviewPendingAccounts ? \App\Models\User::query()->where('account_status', 'pending')->count() : 0;
    $notificationBadgeCount = $unreadNotificationCount + $pendingActivationCount;
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
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
            color: #64748b;
            background-color: #ffffff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: background-color 0.16s ease, color 0.16s ease;
        }
        @media (min-width: 640px) {
            .header-notif-btn {
                width: 2.625rem;
                height: 2.625rem;
            }
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
        #notifications-panel {
            position: fixed;
            inset: 0 0 0 auto;
            width: min(30rem, 100vw);
            max-width: 100vw;
            height: 100dvh;
            max-height: none;
            margin: 0;
            padding: 0;
            border: 0;
            color: #0f172a;
            background: #f8faf9;
            box-shadow: -16px 0 40px -18px rgba(15, 23, 42, .35);
        }
        #notifications-panel::backdrop { background: rgba(15, 23, 42, .35); }
        .header-vertical-divider {
            width: 1px;
            height: 1.75rem;
            background-color: #cbd5e1;
            flex-shrink: 0;
        }
        .header-profile-avatar {
            width: 2.25rem;
            height: 2.25rem;
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
        @media (min-width: 640px) {
            .header-profile-avatar {
                width: 2.375rem;
                height: 2.375rem;
            }
        }
        @keyframes profileDropdownIn {
            from {
                opacity: 0;
                transform: translateY(-6px) scale(0.97);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .profile-dropdown-animate {
            animation: profileDropdownIn 0.16s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
    </style>
</head>
<body class="min-h-dvh bg-white text-slate-700 antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-xl focus:bg-white focus:px-4 focus:py-3 focus:text-slate-800 focus:shadow-lg focus:ring-2 focus:ring-emerald-600">Lewati ke konten</a>

    <div class="min-h-dvh lg:flex">
        {{-- Desktop Sidebar --}}
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
                        class="h-7 w-auto max-w-36 object-contain"
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
                            <button type="submit" data-sidebar-tooltip="Keluar" class="sidebar-logout-btn group flex min-h-11 w-full cursor-pointer items-center gap-3.5 rounded-xl px-3.5 text-left text-sm font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
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

        {{-- Area Konten Utama --}}
        <div class="app-content-surface min-w-0 flex-1">
            <header id="app-top-header" class="sticky top-0 z-20 flex items-center justify-between gap-2 bg-white px-3.5 sm:gap-4 sm:px-6 lg:px-8">
                {{-- Kiri Header: Tombol Menu Mobile & Judul Halaman --}}
                <div class="flex min-w-0 flex-1 items-center gap-2.5 sm:gap-3">
                    <button
                        type="button"
                        data-mobile-menu-open
                        aria-controls="mobile-navigation"
                        aria-haspopup="dialog"
                        class="inline-flex size-10 shrink-0 cursor-pointer items-center justify-center rounded-xl border border-slate-300 bg-white text-slate-800 transition hover:border-emerald-600 hover:bg-emerald-50 hover:text-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 lg:hidden"
                        aria-label="Buka menu navigasi"
                    >
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16" /></svg>
                    </button>

                    <span class="header-page-title min-w-0 truncate text-sm tracking-tight sm:text-base lg:text-lg">
                        {{ $title }}
                    </span>
                </div>

                {{-- Kanan Header: Notifikasi & Profil Dropdown --}}
                <div class="flex shrink-0 items-center gap-2 sm:gap-3.5">
                    @if($user)

                    <button type="button" data-notifications-open aria-controls="notifications-panel" aria-haspopup="dialog" class="header-notif-btn focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" aria-label="Buka pemberitahuan{{ $notificationBadgeCount > 0 ? ', '.$notificationBadgeCount.' perlu ditinjau' : '' }}">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                        </svg>
                        @if ($notificationBadgeCount > 0)
                            <span data-notification-badge class="header-notif-badge" aria-hidden="true">{{ $notificationBadgeCount > 99 ? '99+' : $notificationBadgeCount }}</span>
                        @endif
                    </button>


                        <span class="header-vertical-divider" aria-hidden="true"></span>

                        {{-- Wrapper Dropdown Profil --}}
                        <div class="relative" id="header-profile-wrapper">
                            <button
                                type="button"
                                id="header-profile-trigger"
                                aria-expanded="false"
                                aria-haspopup="true"
                                aria-controls="header-profile-menu"
                                class="group flex min-h-10 cursor-pointer items-center gap-2 rounded-xl border border-transparent p-1 text-left transition hover:border-slate-200 hover:bg-slate-50/90 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:gap-2.5 sm:px-2"
                            >
                                <span class="header-profile-avatar shadow-2xs transition-transform group-hover:scale-103" aria-hidden="true">
                                    {{ mb_substr($user->username, 0, 1) }}
                                </span>

                                <div class="hidden min-w-0 text-left sm:block">
                                    <p class="max-w-36 truncate text-sm font-bold leading-tight text-slate-800 md:max-w-44">
                                        {{ $user->username }}
                                    </p>
                                    <p class="mt-0.5 max-w-36 truncate text-xs font-medium leading-tight text-slate-500 md:max-w-44">
                                        {{ $accountRoles->isNotEmpty() ? $accountRoles->join(' · ') : 'Pengguna' }}
                                    </p>
                                </div>

                                <svg
                                    id="header-profile-chevron"
                                    class="size-4 shrink-0 text-slate-400 transition-transform duration-200 group-hover:text-slate-600"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                            </button>

                            {{-- Popup Menu Profil & Logout --}}
                            <div
                                id="header-profile-menu"
                                role="menu"
                                aria-labelledby="header-profile-trigger"
                                class="profile-dropdown-animate absolute right-0 z-50 mt-2 hidden w-64 origin-top-right overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-xl shadow-slate-900/10 ring-1 ring-slate-900/5"
                            >
                                {{-- Info Akun di Dalam Popup (Sangat berguna di Mobile) --}}
                                <div class="border-b border-slate-100 bg-slate-50/70 px-4 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <span class="flex size-9 shrink-0 items-center justify-center rounded-full bg-emerald-800 text-xs font-bold uppercase text-white" aria-hidden="true">
                                            {{ mb_substr($user->username, 0, 1) }}
                                        </span>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-sm font-bold text-slate-900">{{ $user->username }}</p>
                                            <p class="mt-0.5 truncate text-xs font-medium text-emerald-700">
                                                {{ $accountRoles->isNotEmpty() ? $accountRoles->join(' · ') : 'Pengguna' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Navigasi Cepat Akun --}}
                                <div class="p-1.5">
                                    <a
                                        href="{{ route('dashboard') }}"
                                        role="menuitem"
                                        class="flex min-h-10 items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-emerald-50/70 hover:text-emerald-800 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-emerald-600"
                                    >
                                        <svg class="size-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <rect x="3" y="3" width="7" height="7" rx="1.5" />
                                            <rect x="14" y="3" width="7" height="7" rx="1.5" />
                                            <rect x="14" y="14" width="7" height="7" rx="1.5" />
                                            <rect x="3" y="14" width="7" height="7" rx="1.5" />
                                        </svg>
                                        <span>Ringkasan akses</span>
                                    </a>

                                    @if (\Illuminate\Support\Facades\Route::has('profile.show') && $user->hasRole('Resident'))
                                        <a
                                            href="{{ route('profile.show') }}"
                                            role="menuitem"
                                            class="flex min-h-10 items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-emerald-50/70 hover:text-emerald-800 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-emerald-600"
                                        >
                                            <svg class="size-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <circle cx="12" cy="12" r="10" />
                                                <circle cx="12" cy="10" r="3" />
                                                <path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662" />
                                            </svg>
                                            <span>Profil saya</span>
                                        </a>
                                    @endif
                                </div>

                                {{-- Tombol Keluar (Logout) --}}
                                <div class="border-t border-slate-100 p-1.5">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button
                                            type="submit"
                                            role="menuitem"
                                            class="flex min-h-10 w-full cursor-pointer items-center gap-2.5 rounded-xl px-3 py-2 text-left text-sm font-semibold text-rose-600 transition hover:bg-rose-50 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-rose-600"
                                        >
                                            <svg class="size-4 shrink-0 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                                <polyline points="16 17 21 12 16 7" />
                                                <line x1="21" y1="12" x2="9" y2="12" />
                                            </svg>
                                            <span>Keluar dari akun</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex min-h-10 shrink-0 items-center rounded-xl border border-slate-300 bg-white px-3 text-xs font-semibold text-slate-600 transition hover:border-emerald-600 hover:bg-emerald-50 hover:text-emerald-700 sm:px-3.5 sm:text-sm">Masuk</a>
                        <a href="{{ route('register') }}" class="inline-flex min-h-10 shrink-0 items-center rounded-xl bg-emerald-600 px-3 text-xs font-semibold text-white transition hover:bg-emerald-700 sm:px-3.5 sm:text-sm">Daftar</a>
                    @endif
                </div>
            </header>

            @if($user)
                <dialog id="notifications-panel" aria-labelledby="notifications-panel-title" class="overflow-hidden">
                    <div class="flex h-full flex-col">
                        <header class="flex items-start justify-between gap-4 border-b border-slate-200 bg-white px-5 py-5 sm:px-6">
                            <div>
                                <h2 id="notifications-panel-title" class="text-xl font-bold text-slate-900">Pemberitahuan</h2>
                                <p class="mt-1 text-sm text-slate-600">Pembaruan terbaru untuk akun Anda.</p>
                                <p data-notification-status role="status" aria-live="polite" class="mt-2 text-sm font-medium text-emerald-800"></p>
                            </div>
                            <button type="button" data-notifications-close class="inline-flex size-11 shrink-0 items-center justify-center rounded-xl text-2xl text-slate-600 hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" aria-label="Tutup pemberitahuan">×</button>
                        </header>
                        <div class="min-h-0 flex-1 space-y-7 overflow-y-auto overscroll-contain px-5 py-5 sm:px-6">
                            @if($canReviewPendingAccounts)
                                <section aria-labelledby="pending-accounts-title">
                                    <div class="mb-3 flex items-center justify-between gap-3">
                                        <h3 id="pending-accounts-title" class="font-semibold text-slate-900">Aktivasi akun</h3>
                                        <span data-pending-account-count="{{ $pendingActivationCount }}" class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-800">{{ $pendingActivationCount }} menunggu</span>
                                    </div>
                                    <div data-pending-account-list class="space-y-3">
                                        @forelse($pendingActivationUsers as $pendingUser)
                                            <article data-pending-account-row class="rounded-xl border border-slate-200 bg-white p-4">
                                                <p class="break-words font-semibold text-slate-900">{{ $pendingUser->username }}</p>
                                                <p class="mt-1 break-all text-sm text-slate-600">{{ $pendingUser->email ?: 'Email belum tersedia' }}</p>
                                                @if($pendingUser->hasVerifiedEmail())
                                                    <form method="POST" action="{{ route('admin.users.activate', $pendingUser) }}" data-async-notification-form data-action-kind="activate" class="mt-3">
                                                        @csrf
                                                        <input type="hidden" name="action_kind" value="activate">
                                                        <button class="inline-flex min-h-11 items-center rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white hover:bg-emerald-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">Aktifkan akun</button>
                                                    </form>
                                                @elseif($pendingUser->email)
                                                    <div class="mt-3 flex flex-wrap items-center gap-3">
                                                        <span class="text-sm text-amber-800">Menunggu verifikasi email</span>
                                                        <form method="POST" action="{{ route('admin.users.resend-verification', $pendingUser) }}" data-async-notification-form data-action-kind="resend">
                                                            @csrf
                                                            <input type="hidden" name="action_kind" value="resend">
                                                            <button class="inline-flex min-h-11 items-center rounded-lg border border-slate-300 px-3 text-sm font-semibold text-slate-800 hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">Kirim ulang tautan</button>
                                                        </form>
                                                    </div>
                                                @endif
                                                <p data-notification-action-feedback class="mt-2 text-sm text-emerald-800" aria-live="polite"></p>
                                            </article>
                                        @empty
                                            <p data-pending-empty class="rounded-xl border border-dashed border-slate-300 bg-white p-4 text-sm text-slate-600">Tidak ada akun yang menunggu aktivasi.</p>
                                        @endforelse
                                    </div>
                                </section>
                            @endif

                            <section aria-labelledby="recent-notifications-title">
                                <h3 id="recent-notifications-title" class="mb-3 font-semibold text-slate-900">Pembaruan terbaru</h3>
                                <div data-notification-list class="space-y-3">
                                    @forelse($recentNotifications as $notification)
                                        <article data-notification-row data-read="{{ $notification->read_at ? 'true' : 'false' }}" class="rounded-xl border border-slate-200 bg-white p-4 {{ $notification->read_at ? '' : 'ring-1 ring-emerald-100' }}">
                                            <div class="flex items-start justify-between gap-3">
                                                <h4 class="break-words font-semibold text-slate-900">{{ $notification->title }}</h4>
                                                <span data-notification-read-label class="shrink-0 text-xs font-medium text-slate-500">{{ $notification->read_at ? 'Sudah dibaca' : 'Belum dibaca' }}</span>
                                            </div>
                                            <p class="mt-2 break-words text-sm leading-6 text-slate-600">{{ $notification->message }}</p>
                                            <time class="mt-2 block text-xs text-slate-500" datetime="{{ $notification->created_at }}">{{ \Illuminate\Support\Carbon::parse($notification->created_at)->timezone('Asia/Jakarta')->format('d M Y, H:i') }}</time>
                                            @if(!$notification->read_at)
                                                <form method="POST" action="{{ route('notifications.mark-read', $notification->id) }}" data-async-notification-form data-action-kind="read" class="mt-3">
                                                    @csrf
                                                    <input type="hidden" name="action_kind" value="read">
                                                    <button class="inline-flex min-h-11 items-center text-sm font-semibold text-emerald-800 underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">Tandai sudah dibaca</button>
                                                </form>
                                            @endif
                                            <p data-notification-action-feedback role="status" aria-live="polite" class="mt-2 text-sm text-rose-800"></p>
                                        </article>
                                    @empty
                                        <p data-notification-empty class="rounded-xl border border-dashed border-slate-300 bg-white p-4 text-sm text-slate-600">Belum ada pemberitahuan.</p>
                                    @endforelse
                                </div>
                            </section>
                        </div>
                    </div>
                </dialog>
            @endif

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

    {{-- Mobile Navigation Drawer (Untuk Pengguna Login maupun Tamu) --}}
    <dialog id="mobile-navigation" aria-labelledby="mobile-navigation-title" class="m-0 h-dvh w-full max-h-none max-w-sm border-0 bg-white p-0 text-slate-950 shadow-2xl backdrop:bg-slate-900/40 backdrop:backdrop-blur-2xs">
        <div class="flex h-full flex-col overflow-y-auto px-5 pb-[env(safe-area-inset-bottom)] pt-[env(safe-area-inset-top)]">
            <div class="flex min-h-16 shrink-0 items-center justify-between gap-3 border-b border-slate-200">
                <a href="{{ $user ? route('dashboard') : route('home') }}" id="mobile-navigation-title" class="flex items-center" aria-label="{{ config('app.name') }}" translate="no">
                    <img
                        src="{{ asset('images/logo.png') }}"
                        alt="{{ config('app.name') }}"
                        class="h-7 w-auto object-contain sm:h-8"
                    >
                </a>
                <button type="button" data-mobile-menu-close class="inline-flex size-10 cursor-pointer items-center justify-center rounded-xl border border-slate-300 text-slate-700 transition hover:border-emerald-600 hover:bg-emerald-50 hover:text-emerald-700 focus-visible:outline-offset-2 focus-visible:outline-emerald-600" aria-label="Tutup menu navigasi">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" /></svg>
                </button>
            </div>

            @if($user)
                <div class="min-h-0 flex-1 overflow-y-auto py-4">
                    <div class="space-y-4">
                        <x-navigation.items :groups="$navigationGroups" />
                    </div>
                    <div class="mt-5 border-t border-slate-200 pt-4">
                        <div class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                            LAINNYA
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="flex min-h-11 w-full cursor-pointer items-center gap-3.5 rounded-xl px-3.5 text-left text-sm font-semibold text-rose-600 transition hover:bg-rose-50">
                                <svg class="size-5 shrink-0 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                    <polyline points="16 17 21 12 16 7" />
                                    <line x1="21" y1="12" x2="9" y2="12" />
                                </svg>
                                <span>Keluar</span>
                            </button>
                        </form>
                    </div>
                </div>
            @else
                <div class="min-h-0 flex-1 overflow-y-auto py-4">
                    <div class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400">
                        UTAMA
                    </div>
                    <nav aria-label="Navigasi pasar desa">
                        <a href="{{ route('marketplace.index') }}" class="flex min-h-11 items-center gap-3.5 rounded-xl bg-emerald-600 px-3.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                            <svg class="size-5 shrink-0 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="3" width="7" height="7" rx="1.5" />
                                <rect x="14" y="3" width="7" height="7" rx="1.5" />
                                <rect x="14" y="14" width="7" height="7" rx="1.5" />
                                <rect x="3" y="14" width="7" height="7" rx="1.5" />
                            </svg>
                            <span>Pasar desa</span>
                        </a>
                    </nav>
                    <div class="mt-5 flex flex-col gap-2.5 border-t border-slate-200 pt-4">
                        <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 transition hover:border-emerald-600 hover:bg-emerald-50 hover:text-emerald-700">
                            Masuk
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 text-sm font-semibold text-white transition hover:bg-emerald-700">
                            Daftar
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </dialog>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const notificationDialog = document.getElementById('notifications-panel');
            const notificationOpenButton = document.querySelector('[data-notifications-open]');
            let notificationBadge = document.querySelector('[data-notification-badge]');
            const pendingCountElement = document.querySelector('[data-pending-account-count]');
            const notificationStatus = document.querySelector('[data-notification-status]');
            let unreadNotificationCount = Number({{ $unreadNotificationCount }});

            if (notificationDialog && notificationOpenButton) {
                notificationOpenButton.addEventListener('click', function () {
                    notificationDialog.showModal();
                });

                document.querySelectorAll('[data-notifications-close]').forEach(function (button) {
                    button.addEventListener('click', function () {
                        notificationDialog.close();
                    });
                });

                notificationDialog.addEventListener('click', function (event) {
                    if (event.target === notificationDialog) {
                        notificationDialog.close();
                    }
                });

                const updateBadge = function (unreadCount) {
                    const pendingCount = pendingCountElement ? Number(pendingCountElement.dataset.pendingAccountCount || 0) : 0;
                    const total = unreadCount + pendingCount;
                    if (total < 1) {
                        if (notificationBadge) notificationBadge.remove();
                        notificationOpenButton.setAttribute('aria-label', 'Buka pemberitahuan');
                        return;
                    }
                    const badge = notificationBadge || document.createElement('span');
                    notificationBadge = badge;
                    badge.dataset.notificationBadge = '';
                    badge.className = 'header-notif-badge';
                    badge.setAttribute('aria-hidden', 'true');
                    badge.textContent = total > 99 ? '99+' : String(total);
                    if (!badge.isConnected) notificationOpenButton.append(badge);
                    notificationOpenButton.setAttribute('aria-label', 'Buka pemberitahuan, ' + total + ' perlu ditinjau');
                };

                document.querySelectorAll('[data-async-notification-form]').forEach(function (form) {
                    form.addEventListener('submit', async function (event) {
                        event.preventDefault();
                        const button = form.querySelector('button[type="submit"], button:not([type])');
                        const feedback = form.closest('article')?.querySelector('[data-notification-action-feedback]');
                        if (button) {
                            button.disabled = true;
                            button.setAttribute('aria-busy', 'true');
                            button.insertAdjacentHTML('afterbegin', '<span class="mr-2 inline-block motion-safe:animate-spin" aria-hidden="true">⟳</span>');
                        }
                        try {
                            const response = await fetch(form.action, {
                                method: 'POST',
                                body: new FormData(form),
                                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                                credentials: 'same-origin'
                            });
                            const result = await response.json();
                            if (!response.ok) throw new Error(result.message || 'Tindakan gagal diproses.');

                            if (form.dataset.actionKind === 'read') {
                                const row = form.closest('[data-notification-row]');
                                row.dataset.read = 'true';
                                row.classList.remove('ring-1', 'ring-emerald-100');
                                row.querySelector('[data-notification-read-label]').textContent = 'Sudah dibaca';
                                form.remove();
                                unreadNotificationCount = Number(result.unread_count || 0);
                                updateBadge(unreadNotificationCount);
                                if (notificationStatus) notificationStatus.textContent = result.message;
                            } else if (form.dataset.actionKind === 'activate') {
                                const row = form.closest('[data-pending-account-row]');
                                row.remove();
                                const nextCount = Math.max(0, Number(pendingCountElement.dataset.pendingAccountCount || 0) - 1);
                                pendingCountElement.dataset.pendingAccountCount = String(nextCount);
                                pendingCountElement.textContent = nextCount + ' menunggu';
                                if (nextCount === 0) {
                                    document.querySelector('[data-pending-account-list]').innerHTML = '<p class="rounded-xl border border-dashed border-slate-300 bg-white p-4 text-sm text-slate-600">Tidak ada akun yang menunggu aktivasi.</p>';
                                }
                                updateBadge(unreadNotificationCount);
                                if (notificationStatus) notificationStatus.textContent = result.message;
                            } else if (feedback) {
                                feedback.textContent = result.message;
                                feedback.classList.remove('text-rose-800');
                                feedback.classList.add('text-emerald-800');
                                if (notificationStatus) notificationStatus.textContent = result.message;
                                form.remove();
                            }
                        } catch (error) {
                            const message = error.message || 'Tidak dapat menghubungi server. Coba lagi.';
                            if (feedback) feedback.textContent = message;
                            if (notificationStatus) notificationStatus.textContent = message;
                            if (button) {
                                button.disabled = false;
                                button.removeAttribute('aria-busy');
                                button.querySelector('span[aria-hidden="true"]')?.remove();
                            }
                        }
                    });
                });
            }



            /* 1. Dropdown Profil & Logout */
            const profileWrapper = document.getElementById('header-profile-wrapper');
            const profileTrigger = document.getElementById('header-profile-trigger');
            const profileMenu = document.getElementById('header-profile-menu');
            const profileChevron = document.getElementById('header-profile-chevron');

            if (profileWrapper && profileTrigger && profileMenu) {
                const toggleProfileMenu = function (forceState) {
                    const isCurrentlyOpen = !profileMenu.classList.contains('hidden');
                    const nextState = typeof forceState === 'boolean' ? forceState : !isCurrentlyOpen;

                    if (nextState) {
                        profileMenu.classList.remove('hidden');
                        profileTrigger.setAttribute('aria-expanded', 'true');
                        if (profileChevron) profileChevron.classList.add('rotate-180');
                    } else {
                        profileMenu.classList.add('hidden');
                        profileTrigger.setAttribute('aria-expanded', 'false');
                        if (profileChevron) profileChevron.classList.remove('rotate-180');
                    }
                };

                profileTrigger.addEventListener('click', function (e) {
                    e.stopPropagation();
                    toggleProfileMenu();
                });

                document.addEventListener('click', function (e) {
                    if (!profileWrapper.contains(e.target)) {
                        toggleProfileMenu(false);
                    }
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape' && !profileMenu.classList.contains('hidden')) {
                        toggleProfileMenu(false);
                        profileTrigger.focus();
                    }
                });
            }

            /* 2. Mobile Drawer Navigation */

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

            /* 3. Desktop Sidebar Collapse & Tooltip */
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
