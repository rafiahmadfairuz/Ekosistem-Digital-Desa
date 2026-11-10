<x-layouts.app title="Pengaturan Internet & Wi‑Fi">
    <main id="main" class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-8">
        <x-ui.page-heading title="Pengaturan Internet & Wi‑Fi" description="Kelola paket berlangganan, status pelanggan, dan hotspot Wi‑Fi publik dari satu tempat." >
            <x-slot:actions>
                <a href="{{ route('services.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-control border border-line bg-paper px-4 text-sm font-semibold text-ink hover:bg-canvas focus-visible:outline-2 focus-visible:outline-offset-2">Lihat layanan</a>
            </x-slot:actions>
        </x-ui.page-heading>

        @if ($errors->any())
            <x-ui.alert type="error">
                <p class="font-semibold">Periksa kembali isian berikut.</p>
                <ul class="mt-1 list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </x-ui.alert>
        @endif

        <section class="grid gap-4 sm:grid-cols-3" aria-label="Ringkasan pengelolaan">
            <article class="rounded-card border border-line bg-paper p-5"><p class="text-sm text-muted">Paket tersedia</p><p class="mt-2 text-2xl font-semibold tabular-nums">{{ $packages->where('is_active', true)->count() }}</p></article>
            <article class="rounded-card border border-line bg-paper p-5"><p class="text-sm text-muted">Langganan aktif</p><p class="mt-2 text-2xl font-semibold tabular-nums">{{ $subscriptions->where('status', 'active')->count() }}</p></article>
            <article class="rounded-card border border-line bg-paper p-5"><p class="text-sm text-muted">Hotspot aktif</p><p class="mt-2 text-2xl font-semibold tabular-nums">{{ $networks->where('status', 'active')->count() }}</p></article>
        </section>

        <nav aria-label="Bagian pengaturan" class="flex flex-wrap gap-2">
            <a href="#subscriptions" class="inline-flex min-h-11 items-center rounded-full border border-line bg-paper px-4 text-sm font-medium text-ink hover:border-brand focus-visible:outline-2 focus-visible:outline-offset-2">Langganan</a>
            <a href="#packages" class="inline-flex min-h-11 items-center rounded-full border border-line bg-paper px-4 text-sm font-medium text-ink hover:border-brand focus-visible:outline-2 focus-visible:outline-offset-2">Paket internet</a>
            <a href="#wifi" class="inline-flex min-h-11 items-center rounded-full border border-line bg-paper px-4 text-sm font-medium text-ink hover:border-brand focus-visible:outline-2 focus-visible:outline-offset-2">Wi‑Fi publik</a>
        </nav>

        <section id="subscriptions" class="scroll-mt-6 space-y-4" aria-labelledby="subscriptions-heading">
            <header><h2 id="subscriptions-heading" class="text-xl font-semibold">Langganan warga</h2><p class="mt-1 text-sm leading-6 text-muted">Perubahan status tidak menghapus tagihan atau riwayat. Langganan yang dibatalkan bersifat permanen.</p></header>
            @forelse ($subscriptions as $subscription)
                <article class="grid gap-5 rounded-card border border-line bg-paper p-5 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,0.8fr)] lg:items-center">
                    <div class="min-w-0">
                        <h3 class="break-words font-semibold">{{ $subscription->resident->user->username }} <span class="text-muted">·</span> {{ $subscription->package->name }}</h3>
                        <dl class="mt-4 grid grid-cols-2 gap-4 text-sm sm:grid-cols-3">
                            <div><dt class="text-xs text-muted">Status</dt><dd class="mt-1 font-medium">{{ match ($subscription->status) { 'active' => 'Aktif', 'suspended' => 'Ditangguhkan', 'pending' => 'Menunggu aktivasi', 'cancelled' => 'Dibatalkan permanen', 'expired' => 'Berakhir', default => ucfirst($subscription->status) } }}</dd></div>
                            <div><dt class="text-xs text-muted">Mulai berlangganan</dt><dd class="mt-1 font-medium tabular-nums">{{ $subscription->start_date->format('d/m/Y') }}</dd></div>
                            <div><dt class="text-xs text-muted">Riwayat tagihan</dt><dd class="mt-1 font-medium tabular-nums">{{ $subscription->invoices->count() }} tagihan</dd></div>
                        </dl>
                    </div>
                    @if (in_array($subscription->status, ['active', 'suspended', 'pending'], true))
                        <form method="POST" action="{{ route('services.subscriptions.status', $subscription) }}" data-confirm="Status langganan akan diubah. Tagihan dan riwayat tetap tersimpan. Lanjutkan?" class="grid gap-3 rounded-control border border-line bg-canvas p-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end">
                            @csrf @method('PUT')
                            <label class="min-w-0 text-sm font-medium">Ubah status
                                <select name="status" required class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base text-ink">
                                    <option value="" selected disabled>Pilih status…</option>
                                    @if (in_array($subscription->status, ['suspended', 'pending'], true))<option value="active">Aktif</option>@endif
                                    @if (in_array($subscription->status, ['active', 'pending'], true))<option value="suspended">Ditangguhkan</option>@endif
                                    <option value="cancelled">Dibatalkan permanen</option>
                                </select>
                            </label>
                            <x-ui.button type="submit" variant="secondary" class="w-full sm:w-auto">Simpan status</x-ui.button>
                        </form>
                    @else
                        <p class="rounded-control border border-line bg-canvas p-4 text-sm text-muted">Langganan ini sudah berakhir dan tidak dapat diaktifkan kembali.</p>
                    @endif
                </article>
            @empty
                <x-ui.empty-state title="Belum ada langganan" description="Langganan warga akan muncul di sini setelah pemasangan selesai." />
            @endforelse
        </section>

        <section id="packages" class="scroll-mt-6 space-y-4" aria-labelledby="packages-heading">
            <header><h2 id="packages-heading" class="text-xl font-semibold">Paket internet</h2><p class="mt-1 text-sm leading-6 text-muted">Kecepatan ditulis dalam Mbps. 1 Mbps = 1.000 Kbps pada penyimpanan sistem.</p></header>
            <div class="grid items-start gap-5 xl:grid-cols-2">
                <article class="rounded-card border border-line bg-paper p-5">
                    <h3 class="text-lg font-semibold">Tambah paket</h3>
                    <form method="POST" action="{{ route('services.packages.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2">@csrf
                        <label class="text-sm font-medium">Nama paket<input name="name" required maxlength="120" value="{{ old('name') }}" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"></label>
                        <label class="text-sm font-medium">Harga per bulan (Rp)<input name="monthly_price" type="number" min="0" step="0.01" required value="{{ old('monthly_price') }}" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"></label>
                        <label class="text-sm font-medium">Kecepatan unduh (Mbps)<input name="bandwidth_download_mbps" type="number" min="0.001" step="0.001" value="{{ old('bandwidth_download_mbps') }}" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"></label>
                        <label class="text-sm font-medium">Kecepatan unggah (Mbps)<input name="bandwidth_upload_mbps" type="number" min="0.001" step="0.001" value="{{ old('bandwidth_upload_mbps') }}" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"></label>
                        <label class="text-sm font-medium">Masa aktif (hari)<input name="validity_days" type="number" min="1" max="365" value="{{ old('validity_days', 30) }}" required class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"></label>
                        <label class="flex min-h-11 items-center gap-3 self-end text-sm"><input name="is_active" value="1" type="checkbox" checked class="size-5 rounded border-line text-brand">Aktif untuk pendaftaran</label>
                        <label class="text-sm font-medium sm:col-span-2">Keterangan<textarea name="description" rows="3" maxlength="5000" class="mt-1 w-full rounded-control border border-line bg-paper px-3 py-2 text-base">{{ old('description') }}</textarea></label>
                        <div class="sm:col-span-2"><x-ui.button type="submit">Simpan paket</x-ui.button></div>
                    </form>
                </article>

                <div class="space-y-4">
                    @forelse ($packages as $package)
                        <article class="rounded-card border border-line bg-paper p-5">
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-3"><div><h3 class="font-semibold">{{ $package->name }}</h3><p class="mt-1 text-sm text-muted">{{ $package->is_active ? 'Menerima pendaftaran' : 'Tidak ditampilkan ke warga' }}</p></div><span class="rounded-full border border-line px-3 py-1 text-xs font-semibold">{{ $package->is_active ? 'Aktif' : 'Nonaktif' }}</span></div>
                            <form method="POST" action="{{ route('services.packages.update', $package) }}" class="grid gap-4 sm:grid-cols-2">@csrf @method('PUT')
                                <label class="text-sm font-medium">Nama paket<input name="name" value="{{ $package->name }}" required maxlength="120" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"></label>
                                <label class="text-sm font-medium">Harga per bulan (Rp)<input name="monthly_price" type="number" min="0" step="0.01" value="{{ $package->monthly_price }}" required class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"></label>
                                <label class="text-sm font-medium">Kecepatan unduh (Mbps)<input name="bandwidth_download_mbps" type="number" min="0.001" step="0.001" value="{{ $package->bandwidth_download_kbps !== null ? $package->bandwidth_download_kbps / 1000 : '' }}" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"></label>
                                <label class="text-sm font-medium">Kecepatan unggah (Mbps)<input name="bandwidth_upload_mbps" type="number" min="0.001" step="0.001" value="{{ $package->bandwidth_upload_kbps !== null ? $package->bandwidth_upload_kbps / 1000 : '' }}" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"></label>
                                <label class="text-sm font-medium">Masa aktif (hari)<input name="validity_days" type="number" min="1" max="365" value="{{ $package->validity_days }}" required class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"></label>
                                <label class="flex min-h-11 items-center gap-3 self-end text-sm"><input name="is_active" value="1" type="checkbox" @checked($package->is_active) class="size-5 rounded border-line text-brand">Tersedia untuk pendaftaran</label>
                                <label class="text-sm font-medium sm:col-span-2">Keterangan<textarea name="description" rows="2" maxlength="5000" class="mt-1 w-full rounded-control border border-line bg-paper px-3 py-2 text-base">{{ $package->description }}</textarea></label>
                                <div class="sm:col-span-2"><x-ui.button type="submit" variant="secondary">Simpan perubahan</x-ui.button></div>
                            </form>
                        </article>
                    @empty
                        <x-ui.empty-state title="Belum ada paket" description="Tambahkan paket pertama melalui formulir ini." />
                    @endforelse
                </div>
            </div>
        </section>

        <section id="wifi" class="scroll-mt-6 space-y-4" aria-labelledby="wifi-heading">
            <header><h2 id="wifi-heading" class="text-xl font-semibold">Wi‑Fi publik</h2><p class="mt-1 max-w-3xl text-sm leading-6 text-muted">Pengaturan ini mengelola kebijakan dan data hotspot. Akses masih simulasi aplikasi dan belum mengendalikan router.</p></header>
            <div class="grid items-start gap-5 xl:grid-cols-2">
                <article class="rounded-card border border-line bg-paper p-5">
                    <h3 class="text-lg font-semibold">Tambah lokasi hotspot</h3>
                    <form method="POST" action="{{ route('services.wifi.networks.store') }}" class="mt-4 space-y-4">@csrf
                        <label class="block text-sm font-medium">Nama lokasi<input name="name" required maxlength="120" value="{{ old('name') }}" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"></label>
                        <label class="block text-sm font-medium">Alamat atau keterangan lokasi<textarea name="location" required maxlength="255" rows="2" class="mt-1 w-full rounded-control border border-line bg-paper px-3 py-2 text-base">{{ old('location') }}</textarea></label>
                        <label class="block text-sm font-medium">Nama jaringan (SSID)<input name="ssid" required maxlength="100" value="{{ old('ssid') }}" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"></label>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="text-sm font-medium">Status<select name="status" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"><option value="active">Aktif</option><option value="inactive">Nonaktif</option><option value="maintenance">Pemeliharaan</option></select></label>
                            <label class="text-sm font-medium">Lama sesi simulasi (menit)<input name="session_minutes" type="number" min="5" max="1440" value="60" required class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"></label>
                        </div>
                        <label class="block text-sm font-medium">Siapa yang boleh menggunakan<select name="eligibility" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"><option value="authenticated_residents">Warga dengan profil terdaftar</option><option value="verified_residents">Warga dengan wilayah terverifikasi</option></select></label>
                        <label class="block text-sm font-medium">Aturan penggunaan<textarea name="terms" required maxlength="3000" rows="3" class="mt-1 w-full rounded-control border border-line bg-paper px-3 py-2 text-base">Gunakan jaringan dengan bertanggung jawab.</textarea></label>
                        <x-ui.button type="submit">Simpan hotspot</x-ui.button>
                    </form>
                </article>

                <div class="space-y-4">
                    @forelse ($networks as $network)
                        <article class="rounded-card border border-line bg-paper p-5">
                            <div class="mb-4 flex flex-wrap items-center justify-between gap-3"><div><h3 class="font-semibold">{{ $network->name }}</h3><p class="mt-1 text-sm text-muted">{{ $network->ssid }} · {{ $network->location }}</p></div><span class="rounded-full border border-line px-3 py-1 text-xs font-semibold">{{ match ($network->status) { 'active' => 'Aktif', 'maintenance' => 'Pemeliharaan', default => 'Nonaktif' } }}</span></div>
                            <details class="border-t border-line pt-4">
                                <summary class="min-h-11 cursor-pointer font-medium focus-visible:outline-2 focus-visible:outline-offset-2">Ubah pengaturan hotspot</summary>
                                <form method="POST" action="{{ route('services.wifi.networks.update', $network) }}" class="mt-4 space-y-4">@csrf @method('PUT')
                                    <label class="block text-sm font-medium">Nama lokasi<input name="name" value="{{ $network->name }}" required maxlength="120" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"></label>
                                    <label class="block text-sm font-medium">Alamat atau keterangan lokasi<input name="location" value="{{ $network->location }}" required maxlength="255" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"></label>
                                    <label class="block text-sm font-medium">Nama jaringan (SSID)<input name="ssid" value="{{ $network->ssid }}" required maxlength="100" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"></label>
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <label class="text-sm font-medium">Status<select name="status" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"><option value="active" @selected($network->status === 'active')>Aktif</option><option value="inactive" @selected($network->status === 'inactive')>Nonaktif</option><option value="maintenance" @selected($network->status === 'maintenance')>Pemeliharaan</option></select></label>
                                        <label class="text-sm font-medium">Lama sesi simulasi (menit)<input name="session_minutes" type="number" min="5" max="1440" value="{{ data_get($network->access_policy, 'session_minutes', 60) }}" required class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"></label>
                                    </div>
                                    <label class="block text-sm font-medium">Siapa yang boleh menggunakan<select name="eligibility" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"><option value="authenticated_residents" @selected(data_get($network->access_policy, 'eligibility') === 'authenticated_residents')>Warga dengan profil terdaftar</option><option value="verified_residents" @selected(data_get($network->access_policy, 'eligibility') === 'verified_residents')>Warga dengan wilayah terverifikasi</option></select></label>
                                    <label class="block text-sm font-medium">Aturan penggunaan<textarea name="terms" required maxlength="3000" rows="3" class="mt-1 w-full rounded-control border border-line bg-paper px-3 py-2 text-base">{{ data_get($network->access_policy, 'terms') }}</textarea></label>
                                    <x-ui.button type="submit" variant="secondary">Simpan pengaturan</x-ui.button>
                                    <p class="text-xs leading-5 text-muted">Tipe koneksi tercatat: {{ $network->devices->pluck('integration_type')->unique()->join(', ') ?: 'belum ada perangkat' }}. Pengaturan ini belum diterapkan ke router.</p>
                                </form>
                            </details>
                        </article>
                    @empty
                        <x-ui.empty-state title="Belum ada hotspot" description="Tambahkan lokasi Wi‑Fi publik untuk mulai mengatur kebijakannya." />
                    @endforelse
                </div>
            </div>
        </section>
    </main>
</x-layouts.app>
