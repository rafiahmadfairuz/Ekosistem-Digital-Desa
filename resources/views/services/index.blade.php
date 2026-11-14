<x-layouts.app title="Internet & Wi‑Fi">
    <main id="main" class="mx-auto max-w-7xl space-y-10 px-4 py-8 sm:px-8">
        <div>
            <p class="text-xs font-semibold tracking-wide text-emerald-700">Layanan Digital · BUMDes</p>
            <div class="mt-1">
                <x-ui.page-heading title="Internet & Wi‑Fi" description="Ajukan pemasangan internet, periksa tagihan, laporkan kendala, atau minta sesi Wi‑Fi publik. Akses Wi‑Fi publik di sini masih simulasi aplikasi." />
            </div>
        </div>

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
            <section class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8" aria-labelledby="service-access-heading">
                <div class="rounded-xl border border-slate-200 bg-white p-6">
                    <h2 id="service-access-heading" class="text-lg font-semibold text-slate-900">Layanan belum tersedia untuk akun ini</h2>
                    @if ($user->hasRole('Resident'))
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Akun Anda belum memiliki profil warga. Lengkapi profil dan tunggu verifikasi wilayah agar dapat melihat paket, tagihan, dan layanan Wi‑Fi.</p>
                        <a href="{{ route('profile.create') }}" class="mt-4 inline-flex min-h-11 items-center justify-center rounded-lg bg-emerald-700 px-4 text-sm font-semibold text-white transition hover:bg-emerald-800 focus-visible:outline-offset-2">Buat profil warga</a>
                    @else
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Akun ini belum memiliki profil staf BUMDes yang aktif. Hubungi administrator desa untuk memeriksa penugasan layanan.</p>
                    @endif
                </div>
            </section>
        @endif

        @if ($resident)
            <section class="space-y-4" aria-labelledby="packages-heading">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 id="packages-heading" class="text-xl font-bold tracking-tight text-slate-900">Paket internet tersedia</h2>
                        <p class="mt-1 text-sm text-slate-600">Pilih paket konektivitas rumah atau usaha yang dikelola BUMDes.</p>
                    </div>
                    <span class="rounded-full border border-slate-900 bg-white px-3.5 py-1 text-xs font-semibold text-slate-900">
                        {{ $packages->count() }} paket
                    </span>
                </div>

                @if ($packages->isEmpty())
                    <x-ui.alert type="info">Belum ada paket internet yang tersedia. Silakan hubungi petugas BUMDes.</x-ui.alert>
                @else
                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($packages as $package)
                            <article class="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white p-6 transition hover:border-emerald-300">
                                <div>
                                    <div class="flex items-start justify-between gap-3">
                                        <h3 class="text-lg font-bold text-slate-900">{{ $package->name }}</h3>
                                        <span class="shrink-0 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">
                                            Aktif
                                        </span>
                                    </div>
                                    <p class="mt-2 text-sm leading-6 text-slate-600">{{ $package->description ?: 'Paket layanan internet BUMDes.' }}</p>

                                    <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50/50 p-4">
                                        <p class="text-2xl font-bold tracking-tight text-slate-900">
                                            Rp{{ number_format((float) $package->monthly_price, 0, ',', '.') }}
                                            <span class="text-sm font-normal text-slate-500">/ bulan</span>
                                        </p>
                                        <p class="mt-2 border-t border-slate-200/80 pt-2 text-xs text-slate-600">
                                            Unduh <span class="font-semibold text-slate-900">{{ $package->bandwidth_download_kbps ? number_format($package->bandwidth_download_kbps / 1000, 1, ',', '.') . ' Mbps' : 'sesuai konfirmasi petugas' }}</span> · Unggah <span class="font-semibold text-slate-900">{{ $package->bandwidth_upload_kbps ? number_format($package->bandwidth_upload_kbps / 1000, 1, ',', '.') . ' Mbps' : 'sesuai konfirmasi petugas' }}</span>
                                        </p>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('services.installations.store') }}" class="mt-5 space-y-3 border-t border-slate-100 pt-4">
                                    @csrf
                                    <input type="hidden" name="package_id" value="{{ $package->id }}">
                                    <div>
                                        <label for="address-{{ $package->id }}" class="block text-sm font-medium text-slate-900">Alamat pemasangan</label>
                                        <textarea id="address-{{ $package->id }}" name="installation_address" required maxlength="2000" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:outline-none" autocomplete="street-address" placeholder="Contoh: Jalan Melati, RT 04/RW 02…">{{ old('installation_address') }}</textarea>
                                        @error('installation_address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="phone-{{ $package->id }}" class="block text-sm font-medium text-slate-900">Nomor yang bisa dihubungi <span class="font-normal text-slate-500">(opsional)</span></label>
                                        <input id="phone-{{ $package->id }}" name="contact_phone" type="tel" inputmode="tel" autocomplete="tel" maxlength="30" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:outline-none" placeholder="Contoh: 08…">
                                    </div>
                                    <x-ui.button type="submit" class="w-full justify-center">Ajukan pemasangan</x-ui.button>
                                </form>
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <hr class="border-slate-200">

            <section class="space-y-4" aria-labelledby="my-service-heading">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 id="my-service-heading" class="text-xl font-bold tracking-tight text-slate-900">Permohonan dan langganan saya</h2>
                        <p class="mt-1 text-sm text-slate-600">Pantau status permohonan pemasangan baru dan riwayat tagihan langganan Anda.</p>
                    </div>
                    <span class="rounded-full border border-slate-900 bg-white px-3.5 py-1 text-xs font-semibold text-slate-900">
                        {{ $installations->count() }} permohonan
                    </span>
                </div>

                @if ($installations->isEmpty())
                    <div class="rounded-2xl border border-slate-200 bg-white p-5">
                        <div class="rounded-xl border border-slate-200 bg-white px-6 py-7">
                            <p class="text-base font-semibold text-slate-900">Belum ada permohonan pemasangan</p>
                            <p class="mt-1 text-sm text-slate-600">Permohonan pemasangan baru akan muncul di bagian ini setelah Anda mengirim formulir paket di atas.</p>
                        </div>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($installations as $item)
                            <article class="rounded-2xl border border-slate-200 bg-white p-6">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-base font-bold text-slate-900">Pemasangan {{ $item->package->name }}</h3>
                                        <p class="mt-1 text-sm text-slate-600">Diajukan {{ $item->requested_at?->format('d M Y H:i') }} · {{ $item->installation_address }}</p>
                                    </div>
                                    <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3.5 py-1 text-xs font-semibold text-emerald-800">
                                        {{ $installationStatusLabels[$item->status] ?? 'Status diperbarui' }}
                                    </span>
                                </div>
                                @if ($item->scheduled_at)
                                    <p class="mt-3 text-sm text-slate-800"><span class="font-semibold text-slate-900">Jadwal pemasangan:</span> {{ $item->scheduled_at->format('d M Y, H:i') }}</p>
                                @endif
                                @if ($item->completed_at)
                                    <p class="mt-2 text-sm text-slate-800"><span class="font-semibold text-slate-900">Pemasangan selesai:</span> {{ $item->completed_at->format('d M Y, H:i') }}</p>
                                @endif
                                <ol class="mt-4 space-y-3 border-l-2 border-emerald-200 pl-4" aria-label="Riwayat permohonan pemasangan">
                                    @foreach ($item->events->sortBy('created_at') as $event)
                                        <li class="relative text-sm">
                                            <span class="font-semibold text-slate-900">{{ $installationStatusLabels[$event->to_status] ?? 'Status diperbarui' }}</span>
                                            <span class="text-slate-500"> · {{ $event->created_at?->format('d M Y, H:i') }}</span>
                                            @if ($event->note)<p class="mt-1 text-slate-600">{{ $event->note }}</p>@endif
                                        </li>
                                    @endforeach
                                </ol>
                            </article>
                        @endforeach
                    </div>
                @endif

                @foreach ($subscriptions as $subscription)
                    <article class="rounded-2xl border border-slate-200 bg-white p-6">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 pb-3">
                            <h3 class="text-base font-bold text-slate-900">Langganan {{ $subscription->package->name }} · {{ ucfirst($subscription->status) }}</h3>
                            <span class="rounded-full border border-slate-900 bg-white px-3 py-0.5 text-xs font-semibold text-slate-900">{{ ucfirst($subscription->status) }}</span>
                        </div>
                        <div class="mt-2 divide-y divide-slate-100">
                            @forelse ($subscription->invoices as $invoice)
                                <div class="flex flex-wrap items-center justify-between gap-4 py-4 last:pb-0">
                                    <div>
                                        <p class="font-semibold text-slate-900">Tagihan {{ $invoice->invoice_number }}</p>
                                        <p class="mt-0.5 text-sm text-slate-600">Periode {{ $invoice->billing_period_start->format('d/m/Y') }}–{{ $invoice->billing_period_end->format('d/m/Y') }} · <span class="font-semibold text-slate-900">Rp{{ number_format((float) $invoice->amount, 0, ',', '.') }}</span> · {{ ucfirst($invoice->status) }}</p>
                                    </div>
                                    @if (in_array($invoice->status, ['unpaid','overdue'], true))
                                        <form method="POST" action="{{ route('services.payments.store', $invoice) }}" class="flex flex-wrap items-end gap-2.5">
                                            @csrf
                                            <label class="text-sm font-medium text-slate-800">
                                                Cara bayar
                                                <select name="payment_method" class="mt-1 block min-h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:border-emerald-600 focus:outline-none">
                                                    <option value="bank_transfer">Transfer bank</option>
                                                    <option value="cash">Tunai</option>
                                                    <option value="other">Lainnya</option>
                                                </select>
                                            </label>
                                            <label class="text-sm font-medium text-slate-800">
                                                Referensi <span class="font-normal text-slate-500">(opsional)</span>
                                                <input name="provider_reference" maxlength="180" class="mt-1 block min-h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:outline-none" placeholder="Nomor bukti…">
                                            </label>
                                            <x-ui.button type="submit">Kirim konfirmasi</x-ui.button>
                                        </form>
                                    @endif
                                </div>
                            @empty
                                <p class="py-6 text-center text-sm text-slate-500">Belum ada tagihan.</p>
                            @endforelse
                        </div>
                    </article>
                @endforeach
            </section>

            <hr class="border-slate-200">

            <section class="grid gap-6 lg:grid-cols-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="text-lg font-bold text-slate-900">Buat laporan atau keluhan</h2>
                    <p class="mt-1 text-sm text-slate-600">Sampaikan kendala teknis atau pertanyaan tagihan kepada petugas.</p>
                    <form method="POST" action="{{ route('services.requests.store') }}" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label for="service_type" class="block text-sm font-medium text-slate-900">Jenis bantuan</label>
                            <select id="service_type" name="service_type" required class="mt-1 min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:border-emerald-600 focus:outline-none">
                                <option value="complaint">Keluhan</option>
                                <option value="technical_support">Gangguan teknis</option>
                                <option value="billing_question">Pertanyaan tagihan</option>
                                <option value="wifi_access">Akses Wi‑Fi</option>
                                <option value="other">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label for="subject" class="block text-sm font-medium text-slate-900">Judul laporan</label>
                            <input id="subject" name="subject" required maxlength="180" class="mt-1 min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:outline-none" placeholder="Contoh: Koneksi internet terputus…">
                        </div>
                        <div>
                            <label for="description" class="block text-sm font-medium text-slate-900">Ceritakan kendalanya</label>
                            <textarea id="description" name="description" required maxlength="10000" rows="4" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:outline-none" placeholder="Jelaskan kapan dan apa yang terjadi…"></textarea>
                        </div>
                        <x-ui.button type="submit">Kirim laporan</x-ui.button>
                    </form>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Wi‑Fi publik</h2>
                            <p class="mt-1 text-sm leading-6 text-slate-600">Aturan penggunaan dan lama sesi mengikuti lokasi. Tombol di bawah membuat sesi simulasi di aplikasi; koneksi internet perangkat belum dikendalikan router.</p>
                        </div>
                    </div>

                    @forelse ($networks as $network)
                        <article class="mt-5 rounded-xl border border-slate-200 bg-white p-4">
                            <h3 class="font-bold text-slate-900">{{ $network->name }} · <span class="text-emerald-700">{{ $network->ssid }}</span></h3>
                            <p class="mt-0.5 text-xs text-slate-500">{{ $network->location }}</p>
                            <p class="mt-2.5 text-sm text-slate-700">{{ data_get($network->access_policy, 'terms', 'Gunakan jaringan dengan bertanggung jawab.') }}</p>
                            <form method="POST" action="{{ route('services.wifi.sessions.store', $network) }}" class="mt-3.5 border-t border-slate-100 pt-3">
                                @csrf
                                <label class="flex items-start gap-3 text-sm text-slate-700">
                                    <input type="checkbox" name="terms_accepted" value="1" required class="mt-0.5 size-4 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
                                    <span>Saya telah membaca dan menyetujui aturan penggunaan Wi‑Fi.</span>
                                </label>
                                <x-ui.button type="submit" class="mt-3">Mulai sesi simulasi</x-ui.button>
                            </form>
                        </article>
                    @empty
                        <div class="mt-5 rounded-xl border border-dashed border-slate-300 bg-white px-4 py-8 text-center">
                            <p class="text-sm font-medium text-slate-600">Belum ada hotspot publik yang aktif.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <hr class="border-slate-200">

            <div class="grid gap-6 lg:grid-cols-2">
                <section class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="text-lg font-bold text-slate-900">Riwayat sesi Wi‑Fi saya</h2>
                        <span class="rounded-full border border-slate-900 bg-white px-3 py-0.5 text-xs font-semibold text-slate-900">{{ $accessLogs->count() }} sesi</span>
                    </div>
                    @if($accessLogs->isEmpty())
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-8 text-center">
                            <p class="text-sm font-medium text-slate-600">Belum ada riwayat akses.</p>
                        </div>
                    @else
                        <div class="rounded-2xl border border-slate-200 bg-white p-5 divide-y divide-slate-100">
                            @foreach($accessLogs as $log)
                                <p class="py-2.5 first:pt-0 last:pb-0 text-sm text-slate-600">
                                    <span class="font-semibold text-slate-900">{{ $log->network->name }}</span> · <span class="text-emerald-700 font-medium">{{ ucfirst($log->authentication_status) }}</span> · {{ $log->connected_at?->format('d M Y H:i') }} · Simulasi aplikasi
                                </p>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section class="space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="text-lg font-bold text-slate-900">Laporan saya</h2>
                        <span class="rounded-full border border-slate-900 bg-white px-3 py-0.5 text-xs font-semibold text-slate-900">{{ $serviceRequests->count() }} laporan</span>
                    </div>
                    @if($serviceRequests->isEmpty())
                        <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-8 text-center">
                            <p class="text-sm font-medium text-slate-600">Belum ada laporan.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($serviceRequests as $service)
                                <article class="rounded-2xl border border-slate-200 bg-white p-5">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <h3 class="font-semibold text-slate-900">{{ $service->subject }}</h3>
                                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">{{ ucfirst(str_replace('_', ' ', $service->status)) }}</span>
                                    </div>
                                    <p class="mt-1 text-sm text-slate-600">{{ ucfirst(str_replace('_', ' ', $service->service_type)) }}</p>
                                    @if($service->resolution_note)
                                        <p class="mt-2.5 rounded-lg border border-slate-200 bg-slate-50/60 px-3 py-2 text-sm text-slate-700">Catatan petugas: {{ $service->resolution_note }}</p>
                                    @endif
                                </article>
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>
        @endif

        @if ($staff)
            <section class="space-y-8" aria-labelledby="staff-queue-heading">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 id="staff-queue-heading" class="text-xl font-bold tracking-tight text-slate-900">Antrean layanan BUMDes</h2>
                        <p class="mt-1 text-sm text-slate-600">Proses permohonan pemasangan, verifikasi pembayaran, dan tindak lanjuti laporan warga.</p>
                    </div>
                    <span class="rounded-full border border-slate-900 bg-white px-3.5 py-1 text-xs font-semibold text-slate-900">
                        {{ $staffInstallations->count() }} pemasangan
                    </span>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Permohonan pemasangan</h3>
                            <p class="mt-0.5 text-sm text-slate-600">Proses sesuai urutan: periksa, setujui, jadwalkan, lalu tandai pemasangan selesai. Penolakan dan pembatalan memerlukan alasan.</p>
                        </div>
                    </div>

                    @if($staffInstallations->isEmpty())
                        <div class="rounded-xl border border-slate-200 bg-white px-6 py-7">
                            <p class="text-base font-semibold text-slate-900">Tidak ada permohonan yang menunggu tindakan</p>
                            <p class="mt-1 text-sm text-slate-600">Permohonan baru akan muncul di bagian ini setelah warga mengirim formulir pemasangan.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($staffInstallations as $item)
                                @php
                                    $nextInstallationActions = match ($item->status) {
                                        'submitted' => ['reviewing' => 'Mulai pemeriksaan', 'rejected' => 'Tolak permohonan', 'cancelled' => 'Batalkan permohonan'],
                                        'reviewing' => ['approved' => 'Setujui permohonan', 'rejected' => 'Tolak permohonan', 'cancelled' => 'Batalkan permohonan'],
                                        'approved' => ['scheduled' => 'Jadwalkan pemasangan', 'cancelled' => 'Batalkan permohonan'],
                                        'scheduled' => ['installed' => 'Tandai pemasangan selesai', 'cancelled' => 'Batalkan permohonan'],
                                        default => [],
                                    };
                                @endphp
                                <article class="rounded-xl border border-slate-200 bg-white p-5">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div>
                                            <h4 class="font-bold text-slate-900">{{ $item->resident->user->username }} · <span class="text-emerald-700">{{ $item->package->name }}</span></h4>
                                            <p class="mt-1 text-sm text-slate-600">Diajukan {{ $item->requested_at?->format('d M Y, H:i') }} · {{ $item->installation_address }}</p>
                                            <p class="mt-1 text-sm text-slate-600">Kontak: {{ $item->contact_phone ?: 'Tidak dicantumkan' }}</p>
                                        </div>
                                        <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3.5 py-1 text-xs font-semibold text-emerald-800">{{ $installationStatusLabels[$item->status] ?? 'Status diperbarui' }}</span>
                                    </div>
                                    @if ($item->scheduled_at)
                                        <p class="mt-3 text-sm text-slate-800"><span class="font-semibold text-slate-900">Jadwal:</span> {{ $item->scheduled_at->format('d M Y, H:i') }}</p>
                                    @endif
                                    <form method="POST" action="{{ route('services.installations.review', $item) }}" class="mt-4 grid gap-3 border-t border-slate-200 pt-4 sm:grid-cols-2">
                                        @csrf
                                        <label class="text-sm font-medium text-slate-900">
                                            Langkah berikutnya
                                            <select name="status" required class="mt-1 min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:border-emerald-600 focus:outline-none">
                                                <option value="">Pilih tindakan…</option>
                                                @foreach($nextInstallationActions as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label class="text-sm font-medium text-slate-900">
                                            Catatan untuk warga
                                            <textarea name="review_note" rows="2" maxlength="2000" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:border-emerald-600 focus:outline-none" placeholder="Wajib untuk penolakan atau pembatalan…"></textarea>
                                            <span class="mt-1 block text-xs font-normal text-slate-500">Alasan wajib diisi jika menolak atau membatalkan.</span>
                                        </label>
                                        <label class="text-sm font-medium text-slate-900 sm:col-span-2">
                                            Tanggal dan waktu pemasangan
                                            <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="mt-1 min-h-11 w-full rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:border-emerald-600 focus:outline-none">
                                            <span class="mt-1 block text-xs font-normal text-slate-500">Wajib diisi saat memilih “Jadwalkan pemasangan”.</span>
                                        </label>
                                        <div class="sm:col-span-2"><x-ui.button type="submit">Simpan langkah</x-ui.button></div>
                                    </form>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                <hr class="border-slate-200">

                <div class="space-y-3">
                    <div class="flex flex-wrap items-end justify-between gap-2">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Verifikasi pembayaran manual</h3>
                            <p class="mt-0.5 text-sm text-slate-600">Daftar bukti pembayaran warga yang memerlukan konfirmasi lunas atau penolakan.</p>
                        </div>
                        <span class="rounded-full border border-slate-900 bg-white px-3.5 py-1 text-xs font-semibold text-slate-900">
                            {{ $staffPayments->count() }} pembayaran
                        </span>
                    </div>

                    @if($staffPayments->isEmpty())
                        <div class="flex min-h-28 items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                            <p class="text-sm font-medium text-slate-600">Tidak ada pembayaran menunggu.</p>
                        </div>
                    @else
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 divide-y divide-slate-100">
                            @foreach($staffPayments as $payment)
                                <article class="flex flex-wrap items-center justify-between gap-3 py-4 first:pt-0 last:pb-0">
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ $payment->invoice->invoice_number }} · <span class="text-emerald-700">{{ $payment->invoice->subscription->resident->user->username }}</span></p>
                                        <p class="mt-0.5 text-sm text-slate-600">Rp{{ number_format((float)$payment->amount,0,',','.') }} · {{ $payment->payment_method }} · Referensi: {{ $payment->provider_reference ?: '—' }}</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <form method="POST" action="{{ route('services.payments.review',$payment) }}">@csrf<input type="hidden" name="decision" value="paid"><x-ui.button type="submit">Konfirmasi lunas</x-ui.button></form>
                                        <form method="POST" action="{{ route('services.payments.review',$payment) }}">@csrf<input type="hidden" name="decision" value="rejected"><input type="hidden" name="note" value="Bukti pembayaran belum dapat diverifikasi"><x-ui.button type="submit" variant="secondary">Tolak</x-ui.button></form>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>

                <hr class="border-slate-200">

                <div class="space-y-3">
                    <div class="flex flex-wrap items-end justify-between gap-2">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Laporan warga</h3>
                            <p class="mt-0.5 text-sm text-slate-600">Kelola laporan kendala jaringan, keluhan layanan, dan pertanyaan tagihan warga.</p>
                        </div>
                        <span class="rounded-full border border-slate-900 bg-white px-3.5 py-1 text-xs font-semibold text-slate-900">
                            {{ $staffRequests->count() }} laporan
                        </span>
                    </div>

                    @if($staffRequests->isEmpty())
                        <div class="flex min-h-28 items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
                            <p class="text-sm font-medium text-slate-600">Tidak ada laporan terbuka.</p>
                        </div>
                    @else
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 divide-y divide-slate-100">
                            @foreach($staffRequests as $service)
                                <article class="py-5 first:pt-0 last:pb-0">
                                    <h4 class="font-bold text-slate-900">{{ $service->subject }} · <span class="text-emerald-700">{{ $service->resident->user->username }}</span></h4>
                                    <p class="mt-1 text-sm leading-6 text-slate-600">{{ $service->description }}</p>
                                    <form method="POST" action="{{ route('services.requests.update',$service) }}" class="mt-4 flex flex-wrap items-end gap-3">
                                        @csrf
                                        <label class="text-sm font-medium text-slate-900">
                                            Status
                                            <select name="status" class="mt-1 min-h-11 rounded-lg border border-slate-300 bg-white px-3 text-sm text-slate-900 focus:border-emerald-600 focus:outline-none">
                                                <option value="in_progress">Sedang ditangani</option>
                                                <option value="waiting_resident">Menunggu jawaban warga</option>
                                                <option value="resolved">Selesai</option>
                                                <option value="rejected">Ditolak</option>
                                                <option value="closed">Ditutup</option>
                                            </select>
                                        </label>
                                        <label class="min-w-48 flex-1 text-sm font-medium text-slate-900">
                                            Catatan penyelesaian
                                            <textarea name="resolution_note" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 focus:border-emerald-600 focus:outline-none"></textarea>
                                        </label>
                                        <x-ui.button type="submit">Perbarui laporan</x-ui.button>
                                    </form>
                                </article>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        @endif

    </main>
</x-layouts.app>
