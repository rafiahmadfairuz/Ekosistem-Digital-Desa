@php
    $actionGroups = collect($navigationGroups)->except('Beranda');
    $rolesWithoutPhaseTwoDestinations = $roleNames->intersect(['BUMDes Operator', 'Seller', 'Village Head']);
@endphp

<x-layouts.app title="Ringkasan akses">
    <main id="main" class="mx-auto max-w-7xl px-shell py-7 sm:px-shell-wide sm:py-9">
        <x-ui.page-heading
            title="Akses dan tugas"
            :description="config('app.name').' menggabungkan akses warga dan layanan desa dalam satu akun. Daftar di bawah mengikuti peran dan penugasan yang aktif.'"
        />

        @if ($adminSummary)
            <section class="mt-7" aria-labelledby="village-account-summary">
                <h2 id="village-account-summary" class="mb-3 text-base font-semibold">Ikhtisar akun desa</h2>
                <dl class="grid divide-y divide-line border-y border-line bg-paper sm:grid-cols-3 sm:divide-x sm:divide-y-0">
                    <div class="px-4 py-4 sm:px-5">
                        <dt class="text-sm text-muted">Menunggu aktivasi</dt>
                        <dd class="mt-1 text-2xl font-medium tabular-nums">{{ number_format($adminSummary['pending_accounts'], 0, ',', '.') }}</dd>
                    </div>
                    <div class="px-4 py-4 sm:px-5">
                        <dt class="text-sm text-muted">Akun aktif</dt>
                        <dd class="mt-1 text-2xl font-medium tabular-nums">{{ number_format($adminSummary['active_accounts'], 0, ',', '.') }}</dd>
                    </div>
                    <div class="px-4 py-4 sm:px-5">
                        <dt class="text-sm text-muted">Penugasan RT/RW aktif</dt>
                        <dd class="mt-1 text-2xl font-medium tabular-nums">{{ number_format($adminSummary['active_assignments'], 0, ',', '.') }}</dd>
                    </div>
                </dl>
                <p class="mt-2 text-sm text-muted">Jumlah berasal dari data akun dan penugasan yang tersimpan.</p>
            </section>
        @endif

        <div class="mt-8 grid items-start gap-8 lg:grid-cols-[minmax(0,1.5fr)_minmax(17rem,0.75fr)]">
            <div class="min-w-0 space-y-8">
                @forelse ($actionGroups as $group => $items)
                    <section aria-labelledby="task-group-{{ \Illuminate\Support\Str::slug($group) }}">
                        <h2 id="task-group-{{ \Illuminate\Support\Str::slug($group) }}" class="text-lg font-semibold">{{ $group }}</h2>
                        <ul class="mt-3 divide-y divide-line border-y border-line bg-paper">
                            @foreach ($items as $item)
                                <li>
                                    <a href="{{ route($item['route']) }}" class="group flex min-h-16 items-center justify-between gap-4 px-4 py-3 hover:bg-slate-50 focus-visible:outline-offset-[-3px] sm:px-5">
                                        <span class="min-w-0 break-words font-medium">{{ $item['label'] }}</span>
                                        <span class="shrink-0 text-muted group-hover:text-ink" aria-hidden="true">→</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @empty
                    <x-ui.empty-state
                        title="Belum ada halaman layanan untuk peran ini"
                        description="Peran yang tercatat pada akun Anda belum memiliki tujuan aplikasi yang tersedia pada tahap ini. Jika akun Anda memiliki peran lain, tugasnya akan muncul di sini."
                    />
                @endforelse

                @if ($rolesWithoutPhaseTwoDestinations->isNotEmpty() && $actionGroups->isNotEmpty())
                    <x-ui.empty-state
                        title="Sebagian peran belum memiliki halaman layanan"
                        description="{{ $rolesWithoutPhaseTwoDestinations->join(', ') }} belum memiliki tujuan aplikasi yang tersedia pada tahap ini."
                    />
                @endif

                @if ($roleNames->intersect(['RT', 'RW'])->isNotEmpty())
                    <x-ui.card title="Wilayah tugas" description="Cakupan berikut berasal dari penugasan aktif pada akun Anda.">
                        @if ($assignments->isNotEmpty())
                            <ul class="divide-y divide-line border-y border-line">
                                @foreach ($assignments as $assignment)
                                    <li class="flex flex-wrap items-center justify-between gap-2 py-3 text-sm">
                                        <span class="font-medium">{{ $assignment->region->region_type === 'rt' ? $assignment->region->name.' / '.$assignment->region->parent?->name : $assignment->region->name.' · seluruh RT di bawahnya' }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="border-l-2 border-slate-500 bg-slate-100 px-4 py-3 text-sm leading-6">Belum ada wilayah yang ditugaskan. Peran RT/RW saja tidak membuka akses ke data warga.</p>
                        @endif
                    </x-ui.card>
                @endif

                @if ($roleNames->contains('Resident'))
                    <x-ui.card title="Profil warga">
                        @if ($residentProfile)
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="text-sm leading-6">Status wilayah:
                                        @if ($residentProfile->region_verified_at)
                                            <x-ui.status-badge status="Terverifikasi" tone="success" />
                                        @else
                                            <x-ui.status-badge status="Menunggu pemeriksaan" tone="warning" />
                                        @endif
                                    </p>
                                </div>
                                <a href="{{ route('profile.show') }}" class="inline-flex min-h-11 items-center font-medium underline underline-offset-4 hover:text-emerald-900">Lihat profil</a>
                            </div>
                        @else
                            <x-ui.empty-state
                                id="resident-profile-empty"
                                title="Profil belum dilengkapi"
                                description="Isi profil agar wilayah tempat tinggal dapat diperiksa oleh Village Admin."
                                action="Lengkapi profil"
                                :href="route('profile.create')"
                                class="border-0 bg-transparent p-0"
                            />
                        @endif
                    </x-ui.card>
                @endif

                @if ($roleNames->contains('Village Admin'))
                    <x-ui.card title="Pemeriksaan wilayah warga" description="Permintaan yang belum diperiksa tersimpan pada daftar verifikasi.">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <p class="text-sm text-muted">Menunggu pemeriksaan: <span class="font-medium tabular-nums text-ink">{{ number_format($regionReviewCount, 0, ',', '.') }}</span></p>
                            <a href="{{ route('admin.resident-region-verification.index') }}" class="inline-flex min-h-11 items-center font-medium underline underline-offset-4 hover:text-emerald-900">Buka daftar verifikasi</a>
                        </div>
                    </x-ui.card>
                @endif
            </div>

            <aside class="min-w-0 space-y-6 lg:sticky lg:top-8" aria-label="Ringkasan akun">
                <x-ui.card title="Akun ini">
                    <dl class="divide-y divide-line border-y border-line text-sm">
                        <div class="py-3">
                            <dt class="text-muted">Nama akun</dt>
                            <dd class="mt-1 break-words font-medium">{{ auth()->user()->username }}</dd>
                        </div>
                        <div class="py-3">
                            <dt class="text-muted">Status akun</dt>
                            <dd class="mt-1">{{ ['active' => 'Aktif', 'pending' => 'Menunggu aktivasi', 'suspended' => 'Ditangguhkan', 'disabled' => 'Dinonaktifkan'][auth()->user()->account_status] ?? auth()->user()->account_status }}</dd>
                        </div>
                        <div class="py-3">
                            <dt class="text-muted">Verifikasi email</dt>
                            <dd class="mt-1">
                                @if (auth()->user()->hasVerifiedEmail())
                                    Terverifikasi
                                @else
                                    Belum diverifikasi · <a class="font-medium underline underline-offset-4" href="{{ route('verification.notice') }}">Lihat petunjuk</a>
                                @endif
                            </dd>
                        </div>
                    </dl>
                    <h3 class="mt-5 text-sm font-medium">Peran</h3>
                    <ul class="mt-2 space-y-2 text-sm">
                        @foreach ($roles as $role)
                            <li>{{ $role->name }}</li>
                        @endforeach
                    </ul>
                    @if ($adminSummary)
                        <p class="mt-5 border-t border-line pt-4 text-sm leading-6 text-muted">Super Admin mengelola akses teknis dan tidak otomatis memiliki kewenangan hukum untuk menyetujui atau menandatangani dokumen desa.</p>
                    @endif
                </x-ui.card>
            </aside>
        </div>
    </main>
</x-layouts.app>
