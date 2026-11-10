<x-layouts.app title="Pengajuan penjual">
    <main id="main" class="mx-auto max-w-4xl space-y-6 px-4 py-8 sm:px-8">
        <x-ui.page-heading title="Pengajuan penjual" description="Isi data usaha untuk diperiksa pengelola BUMDes. Pengajuan tidak otomatis memberi akses menjual." />
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
            <section class="rounded-card border border-line bg-paper p-5"><h2 class="text-lg font-semibold">Status: {{ str_replace('_',' ',ucfirst($seller->verification_status)) }}</h2><p class="mt-2">{{ $seller->business_name }}</p><p class="mt-2 text-sm text-muted">{{ $applicationMessage }}</p>@if($seller->rejection_reason)<p class="mt-3 rounded-control border border-red-200 bg-red-50 p-3 text-sm text-danger"><span class="font-semibold">Catatan pemeriksa:</span> {{ $seller->rejection_reason }}</p>@endif
                <ol class="mt-4 space-y-2 border-l-2 border-line pl-4">@foreach($seller->statusEvents as $event)<li class="text-sm"><span class="font-medium">{{ str_replace('_',' ',ucfirst($event->to_status)) }}</span> · {{ $event->created_at->format('d M Y, H:i') }} @if($event->reason) <p class="text-muted">{{ $event->reason }}</p>@endif</li>@endforeach</ol>
            </section>
        @endif
        @if(! $seller || in_array($seller->verification_status,['rejected','inactive'],true))
            <form method="POST" enctype="multipart/form-data" action="{{ route('marketplace.seller.application.submit') }}" data-unsaved-form class="space-y-4 rounded-card border border-line bg-paper p-5">@csrf
                <div><label for="business_name" class="font-medium">Nama usaha</label><input id="business_name" name="business_name" required maxlength="160" value="{{ old('business_name',$seller?->business_name) }}" class="mt-1 min-h-11 w-full rounded-control border border-line px-3">@error('business_name')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror</div>
                <div><label for="business_description" class="font-medium">Produk atau jasa yang dijual</label><textarea id="business_description" name="business_description" rows="4" maxlength="5000" class="mt-1 w-full rounded-control border border-line px-3 py-2">{{ old('business_description',$seller?->business_description) }}</textarea></div>
                <div><label for="business_address" class="font-medium">Alamat usaha <span class="text-muted">(opsional)</span></label><textarea id="business_address" name="business_address" rows="2" maxlength="3000" class="mt-1 w-full rounded-control border border-line px-3 py-2">{{ old('business_address',$seller?->business_address) }}</textarea></div>
                <div><label for="contact_phone" class="font-medium">Nomor kontak usaha <span class="text-muted">(opsional)</span></label><input id="contact_phone" name="contact_phone" type="tel" inputmode="tel" autocomplete="tel" maxlength="40" value="{{ old('contact_phone',$seller?->contact_phone) }}" class="mt-1 min-h-11 w-full rounded-control border border-line px-3"></div>
                <div><label for="supporting_document" class="font-medium">Dokumen pendukung <span class="text-muted">(opsional, PDF/JPG/PNG maks. 5 MB)</span></label><input id="supporting_document" name="supporting_document" type="file" accept=".pdf,.jpg,.jpeg,.png" class="mt-1 block min-h-11 w-full rounded-control border border-line p-2"></div>
                <x-ui.button type="submit">{{ $seller ? 'Kirim ulang pengajuan' : 'Ajukan sebagai penjual' }}</x-ui.button>
            </form>
        @endif
    </main>
</x-layouts.app>
