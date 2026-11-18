<x-layouts.app title="Profil warga">
    <main id="main" class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        {{-- Header Halaman --}}
        <header class="flex flex-col gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">
                    Data kependudukan
                </p>
                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    Profil warga
                </h1>
                <p class="mt-1 text-sm text-slate-500">
                    Informasi identitas dan wilayah domisili yang terdaftar pada akun Anda.
                </p>
            </div>

            <div class="shrink-0">
                <a
                    href="{{ route('profile.edit') }}"
                    class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow-md hover:shadow-emerald-600/25 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                >
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z" />
                        <path d="m15 5 4 4" />
                    </svg>
                    <span>Edit profil</span>
                </a>
            </div>
        </header>

        {{-- Kartu Profil Utama --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm" aria-labelledby="resident-profile-name">
            {{-- Banner Identitas Warga --}}
            <div class="border-b border-slate-100 bg-gradient-to-br from-emerald-50/75 via-teal-50/35 to-white p-6 sm:p-8">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex min-w-0 items-center gap-4">
                        {{-- Avatar Monogram --}}
                        <span
                            class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-700 text-lg font-bold uppercase text-white shadow-sm sm:size-16 sm:text-xl"
                            aria-hidden="true"
                        >
                            {{ mb_strtoupper(mb_substr($profile['full_name'] ?? 'W', 0, 2)) }}
                        </span>

                        <div class="min-w-0 flex-1">
                            <h2 id="resident-profile-name" class="break-words text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                                {{ $profile['full_name'] }}
                            </h2>

                            <div class="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-slate-600 sm:text-sm">
                                <span class="inline-flex items-center gap-1.5 font-medium text-slate-700">
                                    <svg class="size-4 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                    <span>{{ $resident->region->name }} / {{ $resident->region->parent->name }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Badge Status Verifikasi Wilayah --}}
                    <div class="shrink-0">
                        @if ($resident->region_verified_at)
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3.5 py-1.5 text-xs font-semibold text-emerald-800 shadow-2xs">
                                <svg class="size-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M20 6 9 17l-5-5" />
                                </svg>
                                <span>Wilayah terverifikasi</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3.5 py-1.5 text-xs font-semibold text-amber-800 shadow-2xs">
                                <span class="size-1.5 rounded-full bg-amber-500" aria-hidden="true"></span>
                                <span>Menunggu verifikasi wilayah</span>
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Rincian Biodata Warga --}}
            @php
                $fields = [
                    [
                        'label' => 'Telepon',
                        'key' => 'phone',
                        'span' => 'sm:col-span-1',
                        'mono' => true,
                    ],
                    [
                        'label' => 'Tanggal lahir',
                        'key' => 'birth_date',
                        'span' => 'sm:col-span-1',
                        'mono' => false,
                    ],
                    [
                        'label' => 'Jenis kelamin',
                        'key' => 'gender',
                        'span' => 'sm:col-span-2',
                        'mono' => false,
                    ],
                    [
                        'label' => 'Alamat lengkap',
                        'key' => 'address',
                        'span' => 'sm:col-span-2',
                        'mono' => false,
                    ],
                ];
            @endphp

            <dl class="grid gap-4 p-6 sm:grid-cols-2 sm:gap-5 sm:p-8">
                @foreach ($fields as $field)
                    @php
                        $rawValue = $profile[$field['key']] ?? null;
                        $hasValue = $rawValue !== '' && $rawValue !== null;
                    @endphp
                    <div class="{{ $field['span'] }} min-w-0 rounded-xl border border-slate-200/80 bg-slate-50/60 p-4 transition hover:border-slate-300/80 hover:bg-slate-50">
                        <dt class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                            {{ $field['label'] }}
                        </dt>
                        <dd class="mt-1.5 break-words text-sm leading-relaxed sm:text-base {{ $hasValue ? 'font-semibold text-slate-900' : 'italic font-normal text-slate-400' }} {{ $field['mono'] && $hasValue ? 'font-mono' : '' }}">
                            {{ $hasValue ? $rawValue : 'Belum diisi' }}
                        </dd>
                    </div>
                @endforeach
            </dl>
        </section>

        {{-- Catatan Informasi Verifikasi --}}
        @unless ($resident->region_verified_at)
            <div class="flex items-start gap-3 rounded-2xl border border-amber-200/80 bg-amber-50/70 px-4 py-3.5 text-xs leading-relaxed text-amber-900">
                <svg class="mt-0.5 size-4 shrink-0 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" />
                    <path d="M12 16v-4" />
                    <path d="M12 8h.01" />
                </svg>
                <p>
                    Data wilayah domisili Anda sedang menunggu pemeriksaan dari petugas atau admin desa. Pastikan nama lengkap, alamat, dan nomor telepon sudah sesuai agar proses verifikasi berjalan lancar.
                </p>
            </div>
        @endunless
    </main>
</x-layouts.app>
