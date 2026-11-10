<x-layouts.app title="Internet & Wi‑Fi">
    <main id="main" class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-8">
        <x-ui.page-heading title="Internet & Wi‑Fi" description="Ajukan pemasangan internet, periksa tagihan, laporkan kendala, atau minta sesi Wi‑Fi publik. Akses Wi‑Fi publik di sini masih simulasi aplikasi." />

        @php
            $installationStatusLabels = [
                'submitted' => 'Menunggu pemeriksaan',
                'reviewing' => 'Sedang diperiksa',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
                'scheduled' => 'Pemasangan dijadwalkan',
                'installed' => 'Pemasangan selesai',
                'cancelled' => 'Dibatalkan',
            ];
        @endphp

        @if (! $resident && ! $staff)
            <section class="rounded-card border border-line bg-paper p-6" aria-labelledby="service-access-heading">
                <h2 id="service-access-heading" class="text-lg font-semibold">Layanan belum tersedia untuk akun ini</h2>
                @if ($user->hasRole('Resident'))
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-muted">Akun Anda belum memiliki profil warga. Lengkapi profil dan tunggu verifikasi wilayah agar dapat melihat paket, tagihan, dan layanan Wi‑Fi.</p>
                    <a href="{{ route('profile.create') }}" class="mt-4 inline-flex min-h-11 items-center justify-center rounded-control bg-brand-strong px-4 text-sm font-semibold text-white hover:bg-emerald-900 focus-visible:outline-offset-2">Buat profil warga</a>
                @else
                    <p class="mt-2 max-w-2xl text-sm leading-6 text-muted">Akun ini belum memiliki profil staf BUMDes yang aktif. Hubungi administrator desa untuk memeriksa penugasan layanan.</p>
                @endif
            </section>
        @endif

        @if ($resident)
            <section class="space-y-4" aria-labelledby="packages-heading">
                <h2 id="packages-heading" class="text-xl font-semibold">Paket internet tersedia</h2>
                @if ($packages->isEmpty())
                    <x-ui.alert type="info">Belum ada paket internet yang tersedia. Silakan hubungi petugas BUMDes.</x-ui.alert>
                @else
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($packages as $package)
                            <article class="rounded-card border border-line bg-paper p-5 shadow-sm">
                                <h3 class="text-lg font-semibold">{{ $package->name }}</h3>
                                <p class="mt-2 text-sm leading-6 text-muted">{{ $package->description ?: 'Paket layanan internet BUMDes.' }}</p>
                                <p class="mt-4 text-xl font-semibold">Rp{{ number_format((float) $package->monthly_price, 0, ',', '.') }} <span class="text-sm font-normal text-muted">/ bulan</span></p>
                                <p class="mt-1 text-sm text-muted">Unduh {{ $package->bandwidth_download_kbps ? number_format($package->bandwidth_download_kbps / 1000, 1, ',', '.') . ' Mbps' : 'sesuai konfirmasi petugas' }} · Unggah {{ $package->bandwidth_upload_kbps ? number_format($package->bandwidth_upload_kbps / 1000, 1, ',', '.') . ' Mbps' : 'sesuai konfirmasi petugas' }}</p>
                                <form method="POST" action="{{ route('services.installations.store') }}" class="mt-5 space-y-3">
                                    @csrf
                                    <input type="hidden" name="package_id" value="{{ $package->id }}">
                                    <div><label for="address-{{ $package->id }}" class="block text-sm font-medium">Alamat pemasangan</label><textarea id="address-{{ $package->id }}" name="installation_address" required maxlength="2000" rows="2" class="mt-1 w-full rounded-control border border-line bg-paper px-3 py-2" autocomplete="street-address" placeholder="Contoh: Jalan Melati, RT 04/RW 02…">{{ old('installation_address') }}</textarea>@error('installation_address')<p class="mt-1 text-sm text-danger">{{ $message }}</p>@enderror</div>
                                    <div><label for="phone-{{ $package->id }}" class="block text-sm font-medium">Nomor yang bisa dihubungi <span class="font-normal text-muted">(opsional)</span></label><input id="phone-{{ $package->id }}" name="contact_phone" type="tel" inputmode="tel" autocomplete="tel" maxlength="30" class="mt-1 w-full rounded-control border border-line bg-paper px-3 py-2" placeholder="Contoh: 08…"></div>
                                    <x-ui.button type="submit">Ajukan pemasangan</x-ui.button>
                                </form>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="space-y-4" aria-labelledby="my-service-heading">
                <h2 id="my-service-heading" class="text-xl font-semibold">Permohonan dan langganan saya</h2>
                @forelse ($installations as $item)
                    <article class="rounded-card border border-line bg-paper p-5">
                        <div class="flex flex-wrap items-start justify-between gap-3"><div><h3 class="font-semibold">Pemasangan {{ $item->package->name }}</h3><p class="mt-1 text-sm text-muted">Diajukan {{ $item->requested_at?->format('d M Y H:i') }} · {{ $item->installation_address }}</p></div><span class="rounded-full border border-line px-3 py-1 text-sm">{{ $installationStatusLabels[$item->status] ?? 'Status diperbarui' }}</span></div>
                        @if ($item->scheduled_at)<p class="mt-3 text-sm"><span class="font-medium">Jadwal pemasangan:</span> {{ $item->scheduled_at->format('d M Y, H:i') }}</p>@endif
                        @if ($item->completed_at)<p class="mt-2 text-sm"><span class="font-medium">Pemasangan selesai:</span> {{ $item->completed_at->format('d M Y, H:i') }}</p>@endif
                        <ol class="mt-4 space-y-3 border-l-2 border-line pl-4" aria-label="Riwayat permohonan pemasangan">
                            @foreach ($item->events->sortBy('created_at') as $event)
                                <li class="relative text-sm"><span class="font-medium">{{ $installationStatusLabels[$event->to_status] ?? 'Status diperbarui' }}</span><span class="text-muted"> · {{ $event->created_at?->format('d M Y, H:i') }}</span>@if ($event->note)<p class="mt-1 text-muted">{{ $event->note }}</p>@endif</li>
                            @endforeach
                        </ol>
                    </article>
                @empty
                    <p class="rounded-card border border-line bg-paper p-5 text-muted">Belum ada permohonan pemasangan.</p>
                @endforelse
                @foreach ($subscriptions as $subscription)
                    <article class="rounded-card border border-line bg-paper p-5">
                        <h3 class="font-semibold">Langganan {{ $subscription->package->name }} · {{ ucfirst($subscription->status) }}</h3>
                        <div class="mt-3 divide-y divide-line">
                            @forelse ($subscription->invoices as $invoice)
                                <div class="flex flex-wrap items-center justify-between gap-3 py-3"><div><p class="font-medium">Tagihan {{ $invoice->invoice_number }}</p><p class="text-sm text-muted">Periode {{ $invoice->billing_period_start->format('d/m/Y') }}–{{ $invoice->billing_period_end->format('d/m/Y') }} · Rp{{ number_format((float) $invoice->amount, 0, ',', '.') }} · {{ ucfirst($invoice->status) }}</p></div>
                                    @if (in_array($invoice->status, ['unpaid','overdue'], true))<form method="POST" action="{{ route('services.payments.store', $invoice) }}" class="flex flex-wrap items-end gap-2">@csrf<label class="text-sm">Cara bayar<select name="payment_method" class="mt-1 block min-h-11 rounded-control border border-line bg-paper px-3 text-base"><option value="bank_transfer">Transfer bank</option><option value="cash">Tunai</option><option value="other">Lainnya</option></select></label><label class="text-sm">Referensi <span class="text-muted">(opsional)</span><input name="provider_reference" maxlength="180" class="mt-1 block min-h-11 rounded-control border border-line bg-paper px-3 text-base" placeholder="Nomor bukti…"></label><x-ui.button type="submit">Kirim konfirmasi</x-ui.button></form>@endif
                                </div>
                            @empty<p class="py-3 text-sm text-muted">Belum ada tagihan.</p>@endforelse
                        </div>
                    </article>
                @endforeach
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-card border border-line bg-paper p-5"><h2 class="text-lg font-semibold">Buat laporan atau keluhan</h2><form method="POST" action="{{ route('services.requests.store') }}" class="mt-4 space-y-3">@csrf
                    <div><label for="service_type" class="block text-sm font-medium">Jenis bantuan</label><select id="service_type" name="service_type" required class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"><option value="complaint">Keluhan</option><option value="technical_support">Gangguan teknis</option><option value="billing_question">Pertanyaan tagihan</option><option value="wifi_access">Akses Wi‑Fi</option><option value="other">Lainnya</option></select></div>
                    <div><label for="subject" class="block text-sm font-medium">Judul laporan</label><input id="subject" name="subject" required maxlength="180" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base" placeholder="Contoh: Koneksi internet terputus…"></div>
                    <div><label for="description" class="block text-sm font-medium">Ceritakan kendalanya</label><textarea id="description" name="description" required maxlength="10000" rows="4" class="mt-1 w-full rounded-control border border-line bg-paper px-3 py-2" placeholder="Jelaskan kapan dan apa yang terjadi…"></textarea></div><x-ui.button type="submit">Kirim laporan</x-ui.button>
                </form></div>
                <div class="rounded-card border border-line bg-paper p-5"><h2 class="text-lg font-semibold">Wi‑Fi publik</h2><p class="mt-2 text-sm leading-6 text-muted">Aturan penggunaan dan lama sesi mengikuti lokasi. Tombol di bawah membuat sesi simulasi di aplikasi; koneksi internet perangkat belum dikendalikan router.</p>
                    @forelse ($networks as $network)<article class="mt-4 border-t border-line pt-4"><h3 class="font-medium">{{ $network->name }} · {{ $network->ssid }}</h3><p class="mt-1 text-sm text-muted">{{ $network->location }}</p><p class="mt-2 text-sm">{{ data_get($network->access_policy, 'terms', 'Gunakan jaringan dengan bertanggung jawab.') }}</p><form method="POST" action="{{ route('services.wifi.sessions.store', $network) }}" class="mt-3">@csrf<label class="flex items-start gap-3 text-sm"><input type="checkbox" name="terms_accepted" value="1" required class="mt-1 size-5"><span>Saya telah membaca dan menyetujui aturan penggunaan Wi‑Fi.</span></label><x-ui.button type="submit" class="mt-3">Mulai sesi simulasi</x-ui.button></form></article>@empty<p class="mt-3 text-sm text-muted">Belum ada hotspot publik yang aktif.</p>@endforelse
                </div>
            </section>
            <section><h2 class="text-lg font-semibold">Riwayat sesi Wi‑Fi saya</h2>@forelse($accessLogs as $log)<p class="mt-2 text-sm text-muted">{{ $log->network->name }} · {{ ucfirst($log->authentication_status) }} · {{ $log->connected_at?->format('d M Y H:i') }} · Simulasi aplikasi</p>@empty<p class="mt-2 text-sm text-muted">Belum ada riwayat akses.</p>@endforelse</section>
            <section><h2 class="text-lg font-semibold">Laporan saya</h2>@forelse($serviceRequests as $service)<article class="mt-3 rounded-card border border-line bg-paper p-4"><h3 class="font-medium">{{ $service->subject }}</h3><p class="mt-1 text-sm text-muted">{{ ucfirst(str_replace('_', ' ', $service->service_type)) }} · {{ ucfirst(str_replace('_', ' ', $service->status)) }}</p>@if($service->resolution_note)<p class="mt-2 text-sm">Catatan petugas: {{ $service->resolution_note }}</p>@endif</article>@empty<p class="mt-2 text-sm text-muted">Belum ada laporan.</p>@endforelse</section>
        @endif

        @if ($staff)
            <section class="space-y-4" aria-labelledby="staff-queue-heading"><h2 id="staff-queue-heading" class="text-xl font-semibold">Antrean layanan BUMDes</h2>
                <div class="rounded-card border border-line bg-paper p-5"><h3 class="text-lg font-semibold">Permohonan pemasangan</h3><p class="mt-1 text-sm text-muted">Proses sesuai urutan: periksa, setujui, jadwalkan, lalu tandai pemasangan selesai. Penolakan dan pembatalan memerlukan alasan.</p>@forelse($staffInstallations as $item)
                    @php
                        $nextInstallationActions = match ($item->status) {
                            'submitted' => ['reviewing' => 'Mulai pemeriksaan', 'rejected' => 'Tolak permohonan', 'cancelled' => 'Batalkan permohonan'],
                            'reviewing' => ['approved' => 'Setujui permohonan', 'rejected' => 'Tolak permohonan', 'cancelled' => 'Batalkan permohonan'],
                            'approved' => ['scheduled' => 'Jadwalkan pemasangan', 'cancelled' => 'Batalkan permohonan'],
                            'scheduled' => ['installed' => 'Tandai pemasangan selesai', 'cancelled' => 'Batalkan permohonan'],
                            default => [],
                        };
                    @endphp
                    <article class="mt-4 rounded-control border border-line p-4"><div class="flex flex-wrap items-start justify-between gap-3"><div><h4 class="font-semibold">{{ $item->resident->user->username }} · {{ $item->package->name }}</h4><p class="mt-1 text-sm text-muted">Diajukan {{ $item->requested_at?->format('d M Y, H:i') }} · {{ $item->installation_address }}</p><p class="mt-1 text-sm text-muted">Kontak: {{ $item->contact_phone ?: 'Tidak dicantumkan' }}</p></div><span class="rounded-full border border-line px-3 py-1 text-sm">{{ $installationStatusLabels[$item->status] ?? 'Status diperbarui' }}</span></div>
                        @if ($item->scheduled_at)<p class="mt-3 text-sm"><span class="font-medium">Jadwal:</span> {{ $item->scheduled_at->format('d M Y, H:i') }}</p>@endif
                        <form method="POST" action="{{ route('services.installations.review', $item) }}" class="mt-4 grid gap-3 border-t border-line pt-4 sm:grid-cols-2">@csrf<label class="text-sm">Langkah berikutnya<select name="status" required class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"><option value="">Pilih tindakan…</option>@foreach($nextInstallationActions as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></label><label class="text-sm">Catatan untuk warga<textarea name="review_note" rows="2" maxlength="2000" class="mt-1 w-full rounded-control border border-line bg-paper px-3 py-2" placeholder="Wajib untuk penolakan atau pembatalan…"></textarea><span class="mt-1 block text-xs text-muted">Alasan wajib diisi jika menolak atau membatalkan.</span></label><label class="text-sm sm:col-span-2">Tanggal dan waktu pemasangan<input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"><span class="mt-1 block text-xs text-muted">Wajib diisi saat memilih “Jadwalkan pemasangan”.</span></label><div class="sm:col-span-2"><x-ui.button type="submit">Simpan langkah</x-ui.button></div></form>
                    </article>
                @empty<p class="mt-3 text-sm text-muted">Tidak ada permohonan yang menunggu tindakan.</p>@endforelse</div>
                <div class="rounded-card border border-line bg-paper p-5"><h3 class="text-lg font-semibold">Verifikasi pembayaran manual</h3>@forelse($staffPayments as $payment)<article class="flex flex-wrap items-center justify-between gap-3 border-t border-line py-4"><div><p class="font-medium">{{ $payment->invoice->invoice_number }} · {{ $payment->invoice->subscription->resident->user->username }}</p><p class="text-sm text-muted">Rp{{ number_format((float)$payment->amount,0,',','.') }} · {{ $payment->payment_method }} · Referensi: {{ $payment->provider_reference ?: '—' }}</p></div><div class="flex gap-2"><form method="POST" action="{{ route('services.payments.review',$payment) }}">@csrf<input type="hidden" name="decision" value="paid"><x-ui.button type="submit">Konfirmasi lunas</x-ui.button></form><form method="POST" action="{{ route('services.payments.review',$payment) }}">@csrf<input type="hidden" name="decision" value="rejected"><input type="hidden" name="note" value="Bukti pembayaran belum dapat diverifikasi"><x-ui.button type="submit" variant="secondary">Tolak</x-ui.button></form></div></article>@empty<p class="mt-3 text-sm text-muted">Tidak ada pembayaran menunggu.</p>@endforelse</div>
                <div class="rounded-card border border-line bg-paper p-5"><h3 class="text-lg font-semibold">Laporan warga</h3>@forelse($staffRequests as $service)<article class="border-t border-line py-4"><h4 class="font-medium">{{ $service->subject }} · {{ $service->resident->user->username }}</h4><p class="mt-1 text-sm text-muted">{{ $service->description }}</p><form method="POST" action="{{ route('services.requests.update',$service) }}" class="mt-3 flex flex-wrap items-end gap-2">@csrf<label class="text-sm">Status<select name="status" class="mt-1 min-h-11 rounded-control border border-line bg-paper px-3 text-base"><option value="in_progress">Sedang ditangani</option><option value="waiting_resident">Menunggu jawaban warga</option><option value="resolved">Selesai</option><option value="rejected">Ditolak</option><option value="closed">Ditutup</option></select></label><label class="min-w-48 flex-1 text-sm">Catatan penyelesaian<textarea name="resolution_note" rows="2" class="mt-1 w-full rounded-control border border-line bg-paper px-3 py-2"></textarea></label><x-ui.button type="submit">Perbarui laporan</x-ui.button></form></article>@empty<p class="mt-3 text-sm text-muted">Tidak ada laporan terbuka.</p>@endforelse</div>
            </section>
        @endif

    </main>
</x-layouts.app>
