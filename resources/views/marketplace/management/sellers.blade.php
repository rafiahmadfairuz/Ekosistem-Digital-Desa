
<x-layouts.app title="Tinjau penjual">
    <main id="main" class="mx-auto max-w-6xl space-y-8 px-4 py-7 sm:px-8 sm:py-9">
        <header class="max-w-3xl">
            <p class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-600">
                <span class="size-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                <span>Manajemen Pasar Desa · BUMDes</span>
            </p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-800 sm:text-[1.625rem]">Pengajuan penjual</h1>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">
                Periksa data usaha dan dokumen, lalu catat keputusan. Pemohon tidak dapat meninjau pengajuannya sendiri.
            </p>
        </header>

        <a href="{{ route('marketplace.management.payout-accounts') }}"
           class="inline-flex min-h-11 items-center rounded-xl border border-emerald-200 bg-emerald-50 px-4 text-sm font-semibold text-emerald-700 transition hover:border-emerald-400 hover:bg-emerald-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
            Verifikasi rekening payout
        </a>

        <section aria-labelledby="applications-heading" class="space-y-4">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="applications-heading" class="text-lg font-bold tracking-tight text-slate-800">Perlu ditinjau</h2>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500">Pengajuan baru menunggu pemeriksaan. Tandai “Mulai pemeriksaan” sebelum memberi keputusan.</p>
                </div>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800">
                    <span class="size-1.5 rounded-full bg-amber-500" aria-hidden="true"></span>
                    <span>{{ $applications->total() }} pengajuan</span>
                </span>
            </div>

            @forelse($applications as $seller)
                @php
                    $sellerTransitions = match($seller->verification_status) {
                        'pending' => ['under_review' => 'Mulai pemeriksaan', 'approved' => 'Setujui', 'rejected' => 'Tolak'],
                        'under_review' => ['approved' => 'Setujui', 'rejected' => 'Tolak'],
                        'rejected' => ['under_review' => 'Tinjau ulang'],
                        default => [],
                    };
                    $statusLabel = match($seller->verification_status) {
                        'pending' => 'Menunggu pemeriksaan',
                        'under_review' => 'Sedang diperiksa',
                        'rejected' => 'Ditolak · dapat ditinjau ulang',
                        default => str_replace('_', ' ', ucfirst($seller->verification_status)),
                    };
                    $statusBadgeClass = match($seller->verification_status) {
                        'under_review' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                        'rejected' => 'border-red-200 bg-red-50 text-red-700',
                        default => 'border-amber-200 bg-amber-50 text-amber-800',
                    };
                    $statusDotClass = match($seller->verification_status) {
                        'under_review' => 'bg-emerald-500',
                        'rejected' => 'bg-red-500',
                        default => 'bg-amber-500',
                    };
                @endphp

                <article class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-xs transition hover:border-slate-300 sm:p-6">
                    <header class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-4">
                        <div class="flex min-w-0 items-start gap-3.5">
                            <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 text-sm font-bold uppercase text-emerald-700" aria-hidden="true">
                                {{ mb_substr($seller->business_name, 0, 1) }}
                            </span>
                            <div class="min-w-0">
                                <h3 class="break-words text-base font-bold text-slate-800 sm:text-lg">{{ $seller->business_name }}</h3>
                                <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                    <span class="text-xs text-slate-500">Pemohon: <strong class="font-semibold text-slate-700">{{ $seller->user->username }}</strong></span>
                                    <span class="text-xs text-slate-300" aria-hidden="true">·</span>
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $statusBadgeClass }}">
                                        <span class="size-1.5 rounded-full {{ $statusDotClass }}" aria-hidden="true"></span>
                                        <span>{{ $statusLabel }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <span class="shrink-0 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600">
                            {{ $seller->created_at?->format('d M Y') }}
                        </span>
                    </header>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-4">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Tentang usaha</h4>
                            <p class="mt-2 whitespace-pre-line break-words text-sm leading-relaxed text-slate-700">{{ $seller->business_description ?: 'Pemohon belum menambahkan keterangan usaha.' }}</p>
                        </div>

                        <div class="space-y-3 rounded-xl border border-slate-100 bg-slate-50/70 p-4 text-sm">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Alamat usaha</p>
                                <p class="mt-1 break-words text-slate-700">{{ $seller->business_address ?: 'Belum dicantumkan' }}</p>
                            </div>
                            <div class="border-t border-slate-200/70 pt-2.5">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Kontak</p>
                                <p class="mt-1 font-medium text-slate-700">{{ $seller->contact_phone ?: 'Belum dicantumkan' }}</p>
                            </div>
                        </div>
                    </div>

                    @if($seller->documents->isNotEmpty())
                        <div>
                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Dokumen pendukung</h4>
                            <ul class="mt-2 flex flex-wrap gap-2">
                                @foreach($seller->documents as $document)
                                    <li>
                                        <a class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50/70 px-3.5 text-xs font-semibold text-emerald-700 transition hover:border-emerald-300 hover:bg-emerald-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                                           href="{{ route('marketplace.management.sellers.documents.show',[$seller,$document]) }}">
                                            <span>Buka dokumen pendukung ({{ number_format($document->file_size / 1024, 0) }} KB)</span>
                                            <span aria-hidden="true">↗</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <p class="text-xs text-slate-400">Tidak ada dokumen pendukung yang dilampirkan.</p>
                    @endif

                    @if($sellerTransitions)
                        <form method="POST" action="{{ route('marketplace.management.sellers.review',$seller) }}" class="grid gap-3.5 border-t border-slate-100 pt-4 md:grid-cols-[minmax(12rem,1fr)_minmax(14rem,2fr)_auto] md:items-end">
                            @csrf
                            <label class="block text-xs font-semibold text-slate-700" for="status-{{ $seller->id }}">
                                <span>Tindakan</span>
                                <select id="status-{{ $seller->id }}" name="status" required class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15">
                                    <option value="" selected disabled>Pilih tindakan…</option>
                                    @foreach($sellerTransitions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="block text-xs font-semibold text-slate-700" for="reason-{{ $seller->id }}">
                                <span>Alasan penolakan <span class="font-normal text-slate-400">(wajib jika menolak)</span></span>
                                <input id="reason-{{ $seller->id }}" name="reason" maxlength="2000" autocomplete="off" placeholder="Jelaskan hal yang perlu diperbaiki…" class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15">
                            </label>

                            <div class="flex items-end">
                                <button type="submit" class="inline-flex min-h-11 w-full items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-xs transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 md:w-auto">
                                    Simpan tindakan
                                </button>
                            </div>
                        </form>
                    @endif
                </article>
            @empty
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xs">
                    <x-ui.empty-state title="Tidak ada pengajuan untuk ditinjau" description="Pengajuan baru akan muncul di bagian ini setelah warga mengirim formulir." />
                </div>
            @endforelse

            {{ $applications->links() }}
        </section>

        <section aria-labelledby="seller-status-heading" class="space-y-4 border-t border-slate-200 pt-8">
            <div>
                <h2 id="seller-status-heading" class="text-lg font-bold tracking-tight text-slate-800">Penjual terdaftar dan status lainnya</h2>
                <p class="mt-1 text-xs leading-relaxed text-slate-500">Daftar ini terpisah dari antrean pengajuan. Perubahan status di sini dapat menghentikan penjualan atau mengaktifkan kembali penjual.</p>
            </div>

            @forelse($otherSellers as $seller)
                @php
                    $sellerTransitions = match($seller->verification_status) {
                        'approved' => ['suspended' => 'Tangguhkan sementara', 'inactive' => 'Nonaktifkan'],
                        'suspended', 'inactive' => ['approved' => 'Aktifkan kembali', 'inactive' => 'Nonaktifkan'],
                        default => [],
                    };
                    $otherBadgeClass = match($seller->verification_status) {
                        'approved' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                        'suspended' => 'border-amber-200 bg-amber-50 text-amber-800',
                        default => 'border-slate-200 bg-slate-100 text-slate-600',
                    };
                @endphp

                <article class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-xs transition hover:border-slate-300 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,1fr)] lg:items-center">
                    <div class="flex min-w-0 items-start gap-3.5">
                        <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 text-xs font-bold uppercase text-emerald-700" aria-hidden="true">
                            {{ mb_substr($seller->business_name, 0, 1) }}
                        </span>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="break-words text-sm font-bold text-slate-800 sm:text-base">{{ $seller->business_name }}</h3>
                                <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $otherBadgeClass }}">
                                    {{ str_replace('_', ' ', ucfirst($seller->verification_status)) }}
                                </span>
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Pemohon: <span class="font-medium text-slate-700">{{ $seller->user->username }}</span></p>
                            <p class="mt-0.5 text-xs text-slate-400">Terakhir diperbarui: {{ $seller->reviewed_at?->format('d M Y, H:i') ?? 'Belum ada keputusan' }}</p>
                        </div>
                    </div>

                    @if($sellerTransitions)
                        <form method="POST" action="{{ route('marketplace.management.sellers.review',$seller) }}" class="flex flex-wrap items-end gap-2.5 border-t border-slate-100 pt-3.5 lg:border-t-0 lg:pt-0">
                            @csrf
                            <label class="min-w-48 flex-1 text-xs font-semibold text-slate-700" for="status-existing-{{ $seller->id }}">
                                <span>Tindakan status</span>
                                <select id="status-existing-{{ $seller->id }}" name="status" required class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15">
                                    <option value="" selected disabled>Pilih tindakan…</option>
                                    @foreach($sellerTransitions as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </label>

                            <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 px-5 text-sm font-semibold text-emerald-700 transition hover:border-emerald-600 hover:bg-emerald-600 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                                Simpan
                            </button>
                        </form>
                    @endif
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-8 text-center">
                    <p class="text-sm font-medium text-slate-500">Belum ada penjual yang disetujui atau ditangguhkan.</p>
                </div>
            @endforelse

            {{ $otherSellers->links() }}
        </section>
    </main>
</x-layouts.app>
