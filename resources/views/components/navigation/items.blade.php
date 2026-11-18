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
                            {{-- 1. Dashboard & Beranda --}}
                            @if (\Illuminate\Support\Str::contains($item['route'], ['dashboard', 'home', 'overview']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect x="3" y="3" width="7" height="7" rx="1.5" />
                                    <rect x="14" y="3" width="7" height="7" rx="1.5" />
                                    <rect x="14" y="14" width="7" height="7" rx="1.5" />
                                    <rect x="3" y="14" width="7" height="7" rx="1.5" />
                                </svg>

                            {{-- 2. Notifikasi --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['notifications']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                                </svg>

                            {{-- 3. Keranjang Belanja (Marketplace) --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace.cart', 'cart', 'checkout']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="8" cy="21" r="1" />
                                    <circle cx="19" cy="21" r="1" />
                                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                                </svg>

                            {{-- 4. Pesanan Pembeli (Marketplace Orders) --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace.orders']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z" />
                                    <path d="M3 6h18" />
                                    <path d="M16 10a4 4 0 0 1-8 0" />
                                </svg>

                            {{-- 5. Pendaftaran Penjual (Seller Application) --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace.seller.application']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 9l2.45-4.9A2 2 0 0 1 7.24 3h9.52a2 2 0 0 1 1.8 1.1L21 9" />
                                    <path d="M3 9h18v2a3 3 0 0 1-6 0 3 3 0 0 1-6 0 3 3 0 0 1-6 0V9Z" />
                                    <path d="M5 12v7a2 2 0 0 0 2 2h5" />
                                    <path d="M19 16v6" />
                                    <path d="M16 19h6" />
                                </svg>

                            {{-- 6. Produk Penjual (Seller Products) --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace.seller.products']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="m7.5 4.27 9 5.15" />
                                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                                    <path d="m3.3 7 8.7 5 8.7-5" />
                                    <path d="M12 22V12" />
                                </svg>

                            {{-- 7. Pesanan Masuk Penjual & Pengiriman (Seller Orders) --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace.seller.orders']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2" />
                                    <path d="M15 18H9" />
                                    <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14" />
                                    <circle cx="17" cy="18" r="2" />
                                    <circle cx="7" cy="18" r="2" />
                                </svg>

                            {{-- 8. Rekening Pencairan Penjual (Payout Account) --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace.seller.payout-account']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1" />
                                    <path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4" />
                                </svg>

                            {{-- 9. Manajemen Penjual (Verifikasi Seller) --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace.management.sellers']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <polyline points="16 11 18 13 22 9" />
                                </svg>

                            {{-- 10. Manajemen Rekening Payout & Akun Xendit Seller --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace.management.payout-accounts', 'xendit-seller-accounts']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <line x1="3" y1="22" x2="21" y2="22" />
                                    <line x1="6" y1="18" x2="6" y2="11" />
                                    <line x1="10" y1="18" x2="10" y2="11" />
                                    <line x1="14" y1="18" x2="14" y2="11" />
                                    <line x1="18" y1="18" x2="18" y2="11" />
                                    <polygon points="12 2 20 7 4 7" />
                                </svg>

                            {{-- 11. Manajemen Kategori Produk --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace.management.categories']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z" />
                                    <circle cx="7.5" cy="7.5" r=".5" fill="currentColor" />
                                </svg>

                            {{-- 12. Moderasi Produk Marketplace --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace.management.products']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z" />
                                    <path d="m9 12 2 2 4-4" />
                                </svg>

                            {{-- 13. Etalase Utama Marketplace Desa --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['marketplace', 'market', 'shop', 'store']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7" />
                                    <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8" />
                                    <path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4" />
                                    <path d="M2 7h20" />
                                    <path d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7" />
                                </svg>

                            {{-- 14. Laporan Pendampingan BUMDes --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['bumdes.assistance.reports']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                                    <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                                    <path d="M8 18v-2" />
                                    <path d="M12 18v-4" />
                                    <path d="M16 18v-6" />
                                </svg>

                            {{-- 15. Layanan Pendampingan BUMDes (Assisted Service) --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['bumdes.assistance']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3" />
                                </svg>

                            {{-- 16. Staf & Pengurus BUMDes --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['bumdes.staff']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect width="20" height="14" x="2" y="7" rx="2" ry="2" />
                                    <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16" />
                                </svg>

                            {{-- 17. Laporan Keuangan & Usaha BUMDes --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['bumdes.reports', 'report', 'analytic', 'finance']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polyline points="22 7 13.5 15.5 8.5 10.5 2 17" />
                                    <polyline points="16 7 22 7 22 13" />
                                </svg>

                            {{-- 18. Jenis & Template Persuratan --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['correspondence.types']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z" />
                                    <path d="M8 10h8" />
                                    <path d="M8 14h5" />
                                </svg>

                            {{-- 19. Layanan Persuratan Desa --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['correspondence']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect width="20" height="16" x="2" y="4" rx="2" />
                                    <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7" />
                                </svg>

                            {{-- 20. Manajemen Layanan Internet & WiFi --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['services.management']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <rect width="20" height="8" x="2" y="2" rx="2" ry="2" />
                                    <rect width="20" height="8" x="2" y="14" rx="2" ry="2" />
                                    <line x1="6" x2="6.01" y1="6" y2="6" />
                                    <line x1="6" x2="6.01" y1="18" y2="18" />
                                </svg>

                            {{-- 21. Portal Layanan Internet & WiFi Warga --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['services']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 20h.01" />
                                    <path d="M2 8.82a15 15 0 0 1 20 0" />
                                    <path d="M5 12.859a10 10 0 0 1 14 0" />
                                    <path d="M8.5 16.429a5 5 0 0 1 7 0" />
                                </svg>

                            {{-- 22. Data Warga Wilayah (Petugas RT/RW) --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['officer.residents']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>

                            {{-- 23. Profil Warga / Akun Pribadi --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['profile']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10" />
                                    <circle cx="12" cy="10" r="3" />
                                    <path d="M7 20.662V19a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v1.662" />
                                </svg>

                            {{-- 24. Aktivasi Pengguna Baru (Admin Pending Users) --}}
                            @elseif ($item['route'] === 'admin.users.index')
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <line x1="19" x2="19" y1="8" y2="14" />
                                    <line x1="22" x2="16" y1="11" y2="11" />
                                </svg>

                            {{-- 25. Pergantian Kepala Desa --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['village-head']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M11.562 3.266a.5.5 0 0 1 .876 0L15.39 8.87a1 1 0 0 0 1.516.294L21.183 5.5a.5.5 0 0 1 .798.519l-2.834 10.246a1 1 0 0 1-.956.734H5.81a1 1 0 0 1-.957-.734L2.02 6.02a.5.5 0 0 1 .798-.519l4.276 3.664a1 1 0 0 0 1.516-.294z" />
                                    <path d="M5 21h14" />
                                </svg>

                            {{-- 26. Manajemen Pengguna & Hak Akses --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['admin.users']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10" />
                                    <circle cx="12" cy="10" r="2.5" />
                                    <path d="M8.5 16a4 4 0 0 1 7 0" />
                                </svg>

                            {{-- 27. Penugasan Wilayah RT/RW --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['region-assignments']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0" />
                                    <circle cx="12" cy="10" r="3" />
                                </svg>

                            {{-- 28. Verifikasi Wilayah Warga --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['resident-region-verification']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z" />
                                    <path d="m9 12 2 2 4-4" />
                                </svg>

                            {{-- 29. Katalog Wilayah Desa / RW / RT --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['admin.regions']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <polygon points="3 6 9 3 15 6 21 3 21 18 15 21 9 18 3 21" />
                                    <line x1="9" x2="9" y1="3" y2="18" />
                                    <line x1="15" x2="15" y1="6" y2="21" />
                                </svg>

                            {{-- 30. Pengaturan Profil Desa --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['village-settings']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z" />
                                    <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2" />
                                    <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2" />
                                    <path d="M10 6h4" />
                                    <path d="M10 10h4" />
                                    <path d="M10 14h4" />
                                    <path d="M10 18h4" />
                                </svg>

                            {{-- 31. Log Audit Sistem --}}
                            @elseif (\Illuminate\Support\Str::contains($item['route'], ['audit-logs']))
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" />
                                    <path d="M3 3v5h5" />
                                    <path d="M12 7v5l4 2" />
                                </svg>

                            {{-- 32. Default Fallback --}}
                            @else
                                <svg class="size-5 shrink-0 transition-colors {{ $isCurrent ? 'text-white' : 'text-slate-500 group-hover:text-emerald-600' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="3" />
                                    <path d="M12 3v3" />
                                    <path d="M12 18v3" />
                                    <path d="m4.93 4.93 2.12 2.12" />
                                    <path d="m16.95 16.95 2.12 2.12" />
                                    <path d="M3 12h3" />
                                    <path d="M18 12h3" />
                                    <path d="m4.93 19.07 2.12-2.12" />
                                    <path d="m16.95 7.05 2.12-2.12" />
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
