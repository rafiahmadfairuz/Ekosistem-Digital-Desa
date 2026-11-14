<x-layouts.app title="Pengajuan penjual">
    <main id="main" class="mx-auto max-w-4xl space-y-6 px-4 py-7 sm:px-8 sm:py-9">
        <header class="max-w-3xl">
            <p class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-600">
                <span class="size-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                <span>Pasar Desa · Mitra Usaha</span>
            </p>
            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-800 sm:text-[1.625rem]">Pengajuan penjual</h1>
            <p class="mt-2 text-sm leading-relaxed text-slate-600">
                Isi data usaha untuk diperiksa pengelola BUMDes. Pengajuan tidak otomatis memberi akses menjual.
            </p>
        </header>

        @if($seller)
            @php($applicationMessage = match($seller->verification_status) {
                'pending' => 'Pengajuan sudah diterima dan menunggu pemeriksaan. Data usaha tidak dapat diubah selama antrean ini; pengelola BUMDes akan menghubungi Anda jika ada yang perlu dilengkapi.',
                'under_review' => 'Pengajuan sedang diperiksa oleh pengelola BUMDes. Anda akan menerima pemberitahuan saat ada keputusan.',
                'approved' => 'Pengajuan disetujui. Gunakan menu Produk saya untuk mulai mengelola dagangan.',
                'rejected' => 'Pengajuan belum disetujui. Baca catatan pemeriksa di bawah, perbaiki data, lalu kirim kembali.',
                'suspended' => 'Akun penjual sedang ditangguhkan. Produk tidak dapat dijual sampai status dipulihkan oleh pengelola.',
                'inactive' => 'Status penjual tidak aktif. Perbarui data usaha dan kirim kembali pengajuan bila ingin mendaftar lagi.',
                default => 'Status pengajuan sedang diperbarui.',
            })
            @php($statusBadgeClass = match($seller->verification_status) {
                'approved' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                'pending', 'under_review' => 'border-amber-200 bg-amber-50 text-amber-800',
                'rejected', 'suspended' => 'border-red-200 bg-red-50 text-red-700',
                default => 'border-slate-200 bg-slate-50 text-slate-600',
            })
            @php($statusDotClass = match($seller->verification_status) {
                'approved' => 'bg-emerald-500',
                'pending', 'under_review' => 'bg-amber-500',
                'rejected', 'suspended' => 'bg-red-500',
                default => 'bg-slate-400',
            })

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-xs sm:p-6" aria-labelledby="seller-status-heading">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex min-w-0 items-start gap-3.5">
                        <span class="inline-flex size-11 shrink-0 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 text-sm font-bold uppercase text-emerald-700" aria-hidden="true">
                            {{ mb_substr($seller->business_name, 0, 1) }}
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-medium text-slate-500">Nama usaha terdaftar</p>
                            <h2 id="seller-status-heading" class="mt-0.5 break-words text-lg font-bold text-slate-800">
                                {{ $seller->business_name }}
                            </h2>
                        </div>
                    </div>

                    <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold {{ $statusBadgeClass }}">
                        <span class="size-1.5 rounded-full {{ $statusDotClass }}" aria-hidden="true"></span>
                        <span>Status: {{ str_replace('_',' ',ucfirst($seller->verification_status)) }}</span>
                    </span>
                </div>

                <p class="mt-4 rounded-xl border border-slate-100 bg-slate-50/80 px-4 py-3 text-sm leading-relaxed text-slate-600">
                    {{ $applicationMessage }}
                </p>

                @if($seller->rejection_reason)
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm leading-relaxed text-red-900" role="alert">
                        <p class="font-semibold text-red-800">Catatan pemeriksa:</p>
                        <p class="mt-1">{{ $seller->rejection_reason }}</p>
                    </div>
                @endif

                @if($seller->statusEvents->isNotEmpty())
                    <div class="mt-5 border-t border-slate-100 pt-5">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400">Riwayat status pengajuan</h3>
                        <ol class="mt-3.5 space-y-3 border-l-2 border-emerald-200 pl-4">
                            @foreach($seller->statusEvents as $event)
                                <li class="relative text-sm">
                                    <span class="absolute -left-[1.3125rem] top-1.5 size-2 rounded-full bg-emerald-500 ring-4 ring-white" aria-hidden="true"></span>
                                    <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                        <span class="font-semibold text-slate-800">{{ str_replace('_',' ',ucfirst($event->to_status)) }}</span>
                                        <span class="text-xs text-slate-400">·</span>
                                        <time class="text-xs text-slate-500">{{ $event->created_at->format('d M Y, H:i') }}</time>
                                    </div>
                                    @if($event->reason)
                                        <p class="mt-1 text-xs leading-relaxed text-slate-600">{{ $event->reason }}</p>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @endif
            </section>
        @endif

        @if(! $seller || in_array($seller->verification_status,['rejected','inactive'],true))
            <form method="POST" enctype="multipart/form-data" action="{{ route('marketplace.seller.application.submit') }}" data-unsaved-form class="space-y-5 rounded-2xl border border-slate-200 bg-white p-5 shadow-xs sm:p-7">
                @csrf

                <div class="border-b border-slate-100 pb-4">
                    <h2 class="text-base font-bold text-slate-800">Formulir identitas usaha</h2>
                    <p class="mt-1 text-xs text-slate-500">Lengkapi informasi di bawah ini dengan jelas agar mempermudah proses verifikasi BUMDes.</p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="business_name" class="block text-xs font-semibold text-slate-700">Nama usaha</label>
                        <input id="business_name" name="business_name" required maxlength="160" value="{{ old('business_name',$seller?->business_name) }}" placeholder="Contoh: Keripik Singkong Bu Siti" class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15">
                        @error('business_name')
                            <p class="mt-1.5 text-xs font-medium text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="business_description" class="block text-xs font-semibold text-slate-700">Produk atau jasa yang dijual</label>
                        <textarea id="business_description" name="business_description" rows="4" maxlength="5000" placeholder="Jelaskan jenis produk, olahan pangan, kerajinan, atau jasa yang akan ditawarkan…" class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm leading-relaxed text-slate-800 placeholder:text-slate-400 transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15">{{ old('business_description',$seller?->business_description) }}</textarea>
                    </div>

                    <div>
                        <label for="contact_phone" class="block text-xs font-semibold text-slate-700">
                            Nomor kontak usaha <span class="font-normal text-slate-400">(opsional)</span>
                        </label>
                        <input id="contact_phone" name="contact_phone" type="tel" inputmode="tel" autocomplete="tel" maxlength="40" value="{{ old('contact_phone',$seller?->contact_phone) }}" placeholder="Contoh: 081234567890" class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15">
                    </div>

                    <div>
                        <label for="supporting_document" class="block text-xs font-semibold text-slate-700">
                            Dokumen pendukung <span class="font-normal text-slate-400">(opsional, PDF/JPG/PNG maks. 5 MB)</span>
                        </label>
                        <input id="supporting_document" name="supporting_document" type="file" accept=".pdf,.jpg,.jpeg,.png" class="mt-1.5 block min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100 focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="business_address" class="block text-xs font-semibold text-slate-700">
                            Alamat usaha <span class="font-normal text-slate-400">(opsional)</span>
                        </label>
                        <textarea id="business_address" name="business_address" rows="2" maxlength="3000" placeholder="Dusun, RT/RW, atau patokan lokasi usaha…" class="mt-1.5 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm leading-relaxed text-slate-800 placeholder:text-slate-400 transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15">{{ old('business_address',$seller?->business_address) }}</textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end border-t border-slate-100 pt-4">
                    <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-600 px-6 text-sm font-semibold text-white shadow-xs transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                        {{ $seller ? 'Kirim ulang pengajuan' : 'Ajukan sebagai penjual' }}
                    </button>
                </div>
            </form>
        @endif
    </main>
</x-layouts.app>
