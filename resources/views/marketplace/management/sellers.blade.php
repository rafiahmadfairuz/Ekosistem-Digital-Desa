<x-layouts.app title="Tinjau penjual">
    <main id="main" class="mx-auto max-w-6xl space-y-8 px-4 py-8 sm:px-8">
        <x-ui.page-heading title="Pengajuan penjual" description="Periksa data usaha dan dokumen, lalu catat keputusan. Pemohon tidak dapat meninjau pengajuannya sendiri." />
        <a href="{{ route('marketplace.management.payout-accounts') }}" class="inline-flex min-h-11 items-center rounded-control border border-line px-4 underline underline-offset-4">Verifikasi rekening payout</a>

        <section aria-labelledby="applications-heading" class="space-y-4">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="applications-heading" class="text-xl font-semibold">Perlu ditinjau</h2>
                    <p class="mt-1 text-sm text-muted">Pengajuan baru menunggu pemeriksaan. Tandai “Mulai pemeriksaan” sebelum memberi keputusan.</p>
                </div>
                <span class="rounded-full border border-amber-300 bg-amber-50 px-3 py-1 text-sm font-medium text-amber-950">{{ $applications->total() }} pengajuan</span>
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
                @endphp
                <article class="space-y-4 rounded-card border border-line bg-paper p-5">
                    <header class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h3 class="break-words text-lg font-semibold">{{ $seller->business_name }}</h3>
                            <p class="mt-1 text-sm text-muted">Pemohon: {{ $seller->user->username }} · <span class="font-medium text-ink">{{ $statusLabel }}</span></p>
                        </div>
                        <span class="shrink-0 rounded-full border border-line bg-slate-50 px-3 py-1 text-sm">{{ $seller->created_at?->format('d M Y') }}</span>
                    </header>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><h4 class="text-sm font-semibold">Tentang usaha</h4><p class="mt-1 whitespace-pre-line break-words text-sm text-muted">{{ $seller->business_description ?: 'Pemohon belum menambahkan keterangan usaha.' }}</p></div>
                        <div class="space-y-2 text-sm">
                            <p><span class="font-medium">Alamat:</span> {{ $seller->business_address ?: 'Belum dicantumkan' }}</p>
                            <p><span class="font-medium">Kontak:</span> {{ $seller->contact_phone ?: 'Belum dicantumkan' }}</p>
                        </div>
                    </div>

                    @if($seller->documents->isNotEmpty())
                        <div><h4 class="text-sm font-semibold">Dokumen pendukung</h4><ul class="mt-1 space-y-1">@foreach($seller->documents as $document)<li><a class="inline-flex min-h-11 items-center break-all text-primary underline underline-offset-2 focus-visible:outline-2 focus-visible:outline-offset-2" href="{{ route('marketplace.management.sellers.documents.show',[$seller,$document]) }}">Buka dokumen pendukung ({{ number_format($document->file_size / 1024, 0) }} KB)</a></li>@endforeach</ul></div>
                    @else
                        <p class="text-sm text-muted">Tidak ada dokumen pendukung yang dilampirkan.</p>
                    @endif

                    @if($sellerTransitions)
                        <form method="POST" action="{{ route('marketplace.management.sellers.review',$seller) }}" class="grid gap-3 border-t border-line pt-4 md:grid-cols-[minmax(12rem,1fr)_minmax(14rem,2fr)_auto]">
                            @csrf
                            <label class="text-sm font-medium" for="status-{{ $seller->id }}">Tindakan
                                <select id="status-{{ $seller->id }}" name="status" required class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base">
                                    <option value="" selected disabled>Pilih tindakan…</option>
                                    @foreach($sellerTransitions as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                                </select>
                            </label>
                            <label class="text-sm font-medium" for="reason-{{ $seller->id }}">Alasan penolakan <span class="font-normal text-muted">(wajib jika menolak)</span>
                                <input id="reason-{{ $seller->id }}" name="reason" maxlength="2000" autocomplete="off" placeholder="Jelaskan hal yang perlu diperbaiki…" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base">
                            </label>
                            <div class="flex items-end"><x-ui.button type="submit">Simpan tindakan</x-ui.button></div>
                        </form>
                    @endif
                </article>
            @empty
                <x-ui.empty-state title="Tidak ada pengajuan untuk ditinjau" description="Pengajuan baru akan muncul di bagian ini setelah warga mengirim formulir." />
            @endforelse
            {{ $applications->links() }}
        </section>

        <section aria-labelledby="seller-status-heading" class="space-y-4 border-t border-line pt-7">
            <div>
                <h2 id="seller-status-heading" class="text-xl font-semibold">Penjual terdaftar dan status lainnya</h2>
                <p class="mt-1 text-sm text-muted">Daftar ini terpisah dari antrean pengajuan. Perubahan status di sini dapat menghentikan penjualan atau mengaktifkan kembali penjual.</p>
            </div>
            @forelse($otherSellers as $seller)
                @php($sellerTransitions = match($seller->verification_status) {
                    'approved' => ['suspended' => 'Tangguhkan sementara', 'inactive' => 'Nonaktifkan'],
                    'suspended', 'inactive' => ['approved' => 'Aktifkan kembali', 'inactive' => 'Nonaktifkan'],
                    default => [],
                })
                <article class="grid gap-4 rounded-card border border-line bg-paper p-5 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,1fr)] lg:items-center">
                    <div class="min-w-0"><h3 class="break-words font-semibold">{{ $seller->business_name }}</h3><p class="mt-1 text-sm text-muted">{{ $seller->user->username }} · {{ str_replace('_', ' ', ucfirst($seller->verification_status)) }}</p><p class="mt-1 text-sm text-muted">Terakhir diperbarui: {{ $seller->reviewed_at?->format('d M Y, H:i') ?? 'Belum ada keputusan' }}</p></div>
                    @if($sellerTransitions)
                        <form method="POST" action="{{ route('marketplace.management.sellers.review',$seller) }}" class="flex flex-wrap items-end gap-3">@csrf<label class="min-w-48 flex-1 text-sm font-medium" for="status-existing-{{ $seller->id }}">Tindakan status<select id="status-existing-{{ $seller->id }}" name="status" required class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"><option value="" selected disabled>Pilih tindakan…</option>@foreach($sellerTransitions as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label><x-ui.button type="submit">Simpan</x-ui.button></form>
                    @endif
                </article>
            @empty
                <p class="rounded-card border border-line bg-paper p-5 text-sm text-muted">Belum ada penjual yang disetujui atau ditangguhkan.</p>
            @endforelse
            {{ $otherSellers->links() }}
        </section>
    </main>
</x-layouts.app>
