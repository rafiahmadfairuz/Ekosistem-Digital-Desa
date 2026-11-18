<x-layouts.app title="Warga wilayah tugas">
    <main id="main" class="mx-auto max-w-6xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">
        {{-- Header Halaman --}}
        <header class="border-b border-slate-200/80 pb-6">
            <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200/80 bg-emerald-50/80 px-3 py-1 text-xs font-semibold text-emerald-800">
                <span class="size-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                <span>RT/RW · wilayah tugas aktif</span>
            </div>

            <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                Warga dalam cakupan Anda
            </h1>

            <p class="mt-2 max-w-3xl text-sm leading-relaxed text-slate-600">
                Daftar ini hanya memuat wilayah yang ditugaskan dan sudah diverifikasi. NIK, nomor telepon, tanggal lahir, dan alamat tidak ditampilkan.
            </p>
        </header>

        {{-- Daftar Warga Terverifikasi --}}
        <section aria-label="Daftar warga terverifikasi">
            <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm">
                <div class="divide-y divide-slate-100">
                    @forelse($residents as $resident)
                        <a
                            href="{{ route('officer.residents.show', $resident) }}"
                            class="group flex min-h-14 flex-col justify-between gap-3 px-5 py-4 transition-colors duration-150 hover:bg-emerald-50/40 focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-emerald-600 sm:flex-row sm:items-center sm:px-6"
                        >
                            <div class="flex min-w-0 items-center gap-3.5">
                                {{-- Monogram Inisial Warga --}}
                                <span
                                    class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-slate-200/80 bg-slate-50 text-xs font-bold uppercase text-slate-600 transition-colors group-hover:border-emerald-200 group-hover:bg-emerald-600 group-hover:text-white"
                                    aria-hidden="true"
                                >
                                    {{ mb_strtoupper(mb_substr($resident->display_name, 0, 2)) }}
                                </span>

                                <span class="min-w-0 break-words text-sm font-semibold text-slate-900 transition-colors group-hover:text-emerald-700 sm:text-base">
                                    {{ $resident->display_name }}
                                </span>
                            </div>

                            <div class="flex shrink-0 items-center justify-between gap-3 pl-13.5 sm:justify-end sm:pl-0">
                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200/80 bg-slate-50/80 px-2.5 py-1 text-xs font-medium text-slate-600">
                                    <svg class="size-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0" />
                                        <circle cx="12" cy="10" r="3" />
                                    </svg>
                                    <span>{{ $resident->region->name }} / {{ $resident->region->parent->name }}</span>
                                </span>

                                <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-400 transition-all group-hover:translate-x-0.5 group-hover:text-emerald-700">
                                    <span class="hidden md:inline">Detail</span>
                                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="m9 18 6-6-6-6" />
                                    </svg>
                                </span>
                            </div>
                        </a>
                    @empty
                        <div class="flex flex-col items-center justify-center px-6 py-12 text-center">
                            <div class="flex size-11 items-center justify-center rounded-2xl border border-slate-200/80 bg-slate-50 text-slate-400" aria-hidden="true">
                                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                                    <circle cx="9" cy="7" r="4" />
                                    <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                                </svg>
                            </div>
                            <p class="mt-3 text-sm font-medium text-slate-600">
                                Belum ada profil warga terverifikasi di wilayah tugas ini.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="mt-5">
                {{ $residents->links() }}
            </div>
        </section>
    </main>
</x-layouts.app>
