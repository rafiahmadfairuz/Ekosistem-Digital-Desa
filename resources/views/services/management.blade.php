<x-layouts.app title="Pengaturan Internet & Wi‑Fi">
    <main id="main" class="mx-auto max-w-6xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        {{-- Header Halaman Minimalis --}}
        <header class="flex flex-col gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="max-w-2xl">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    Pengaturan Internet & Wi‑Fi
                </h1>
                <p class="mt-1.5 text-sm leading-relaxed text-slate-500">
                    Kelola paket berlangganan internet warga, status pelanggan aktif, serta kebijakan hotspot Wi‑Fi publik dari satu tempat.
                </p>
            </div>

            <div class="shrink-0">
                <a
                    href="{{ route('services.index') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs transition hover:-translate-y-0.5 hover:border-emerald-600 hover:bg-emerald-50/40 hover:text-emerald-700 hover:shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                >
                    Lihat layanan
                </a>
            </div>
        </header>

        {{-- Validasi Error --}}
        @if ($errors->any())
            <div role="alert" class="rounded-2xl border border-rose-200 bg-rose-50/90 p-4 text-sm text-rose-900 shadow-2xs">
                <p class="font-bold">Periksa kembali isian berikut:</p>
                <ul class="mt-1.5 list-inside list-disc space-y-0.5 text-xs sm:text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Ringkasan 3 Metrik Pengelolaan --}}
        @php
            $summaryCards = [
                ['label' => 'Paket tersedia', 'value' => $packages->where('is_active', true)->count(), 'dot' => 'bg-emerald-500', 'note' => 'Aktif untuk pendaftaran'],
                ['label' => 'Langganan aktif', 'value' => $subscriptions->where('status', 'active')->count(), 'dot' => 'bg-sky-500', 'note' => 'Pelanggan berjalan'],
                ['label' => 'Hotspot aktif', 'value' => $networks->where('status', 'active')->count(), 'dot' => 'bg-indigo-500', 'note' => 'Titik Wi‑Fi publik'],
            ];
        @endphp

        <section class="grid gap-4 sm:grid-cols-3" aria-label="Ringkasan pengelolaan">
            @foreach ($summaryCards as $card)
                @php
                    $formatted = number_format($card['value'], 0, ',', '.');
                    $len = mb_strlen($formatted);
                    $sizeClass = match (true) {
                        $len <= 6 => 'text-2xl',
                        $len <= 10 => 'text-xl',
                        default => 'text-lg',
                    };
                @endphp
                <article class="min-w-0 rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm transition-shadow duration-200 hover:shadow-md">
                    <div class="flex items-center gap-2">
                        <span class="size-2 shrink-0 rounded-full {{ $card['dot'] }}" aria-hidden="true"></span>
                        <p class="truncate text-xs font-medium text-slate-500">{{ $card['label'] }}</p>
                    </div>
                    <p class="mt-3 {{ $sizeClass }} font-bold leading-tight tracking-tight text-slate-900 tabular-nums break-words">
                        {{ $formatted }}
                    </p>
                    <p class="mt-1.5 text-[11px] text-slate-400">{{ $card['note'] }}</p>
                </article>
            @endforeach
        </section>

        {{-- Navigasi Lompat Cepat (Sticky Anchor Pills) --}}
        <nav aria-label="Bagian pengaturan" class="flex flex-wrap items-center gap-2 rounded-2xl border border-slate-200/80 bg-slate-50/80 p-2 shadow-2xs">
            <a href="#subscriptions" class="inline-flex min-h-10 items-center gap-2 rounded-xl bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs transition hover:text-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:text-sm">
                <span>Langganan warga</span>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-600 tabular-nums">{{ $subscriptions->count() }}</span>
            </a>
            <a href="#packages" class="inline-flex min-h-10 items-center gap-2 rounded-xl bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs transition hover:text-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:text-sm">
                <span>Paket internet</span>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-600 tabular-nums">{{ $packages->count() }}</span>
            </a>
            <a href="#wifi" class="inline-flex min-h-10 items-center gap-2 rounded-xl bg-white px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs transition hover:text-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:text-sm">
                <span>Wi‑Fi publik</span>
                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-600 tabular-nums">{{ $networks->count() }}</span>
            </a>
        </nav>

        {{-- Bagian 1: Langganan Warga --}}
        <section id="subscriptions" class="scroll-mt-6 space-y-4" aria-labelledby="subscriptions-heading">
            <header>
                <h2 id="subscriptions-heading" class="text-lg font-bold text-slate-900 sm:text-xl">Langganan warga</h2>
                <p class="mt-1 text-xs leading-relaxed text-slate-500 sm:text-sm">
                    Perubahan status tidak menghapus tagihan atau riwayat transaksi. Langganan yang dibatalkan bersifat permanen.
                </p>
            </header>

            <div class="space-y-3.5">
                @forelse ($subscriptions as $subscription)
                    @php
                        $subStatusLabels = [
                            'active' => 'Aktif',
                            'suspended' => 'Ditangguhkan',
                            'pending' => 'Menunggu aktivasi',
                            'cancelled' => 'Dibatalkan permanen',
                            'expired' => 'Berakhir',
                        ];
                        $subStatusStyles = [
                            'active' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                            'suspended' => 'border-amber-200 bg-amber-50 text-amber-800',
                            'pending' => 'border-sky-200 bg-sky-50 text-sky-800',
                            'cancelled' => 'border-rose-200 bg-rose-50 text-rose-700',
                            'expired' => 'border-slate-200 bg-slate-100 text-slate-600',
                        ];
                        $subDotStyles = [
                            'active' => 'bg-emerald-500',
                            'suspended' => 'bg-amber-500',
                            'pending' => 'bg-sky-500',
                            'cancelled' => 'bg-rose-500',
                            'expired' => 'bg-slate-400',
                        ];
                        $subLabel = $subStatusLabels[$subscription->status] ?? ucfirst($subscription->status);
                        $subBadge = $subStatusStyles[$subscription->status] ?? 'border-slate-200 bg-slate-50 text-slate-700';
                        $subDot = $subDotStyles[$subscription->status] ?? 'bg-slate-400';
                    @endphp

                    <article class="grid gap-5 rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm transition-shadow duration-200 hover:shadow-md lg:grid-cols-[minmax(0,1fr)_minmax(20rem,0.85fr)] lg:items-center">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <h3 class="break-words text-base font-bold text-slate-900">
                                    {{ $subscription->resident->user->username }}
                                </h3>
                                <span class="rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">
                                    {{ $subscription->package->name }}
                                </span>
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $subBadge }}">
                                    <span class="size-1.5 rounded-full {{ $subDot }}" aria-hidden="true"></span>
                                    <span>{{ $subLabel }}</span>
                                </span>
                            </div>

                            <dl class="mt-4 grid grid-cols-2 gap-4 border-t border-slate-100 pt-3.5 text-xs sm:grid-cols-3 sm:text-sm">
                                <div>
                                    <dt class="text-[11px] font-medium text-slate-400">Mulai berlangganan</dt>
                                    <dd class="mt-0.5 font-semibold text-slate-800 tabular-nums">{{ $subscription->start_date->format('d/m/Y') }}</dd>
                                </div>
                                <div>
                                    <dt class="text-[11px] font-medium text-slate-400">Riwayat tagihan</dt>
                                    <dd class="mt-0.5 font-semibold text-slate-800 tabular-nums">{{ $subscription->invoices->count() }} tagihan</dd>
                                </div>
                                <div class="col-span-2 sm:col-span-1">
                                    <dt class="text-[11px] font-medium text-slate-400">Tarif paket</dt>
                                    <dd class="mt-0.5 font-semibold text-slate-800 tabular-nums">Rp{{ number_format((float) $subscription->package->monthly_price, 0, ',', '.') }}/bln</dd>
                                </div>
                            </dl>
                        </div>

                        @if (in_array($subscription->status, ['active', 'suspended', 'pending'], true))
                            <form
                                method="POST"
                                action="{{ route('services.subscriptions.status', $subscription) }}"
                                data-confirm="Status langganan akan diubah. Tagihan dan riwayat tetap tersimpan. Lanjutkan?"
                                class="grid gap-3 rounded-xl border border-slate-200/80 bg-slate-50/70 p-4 sm:grid-cols-[minmax(0,1fr)_auto] sm:items-end"
                            >
                                @csrf
                                @method('PUT')
                                <div class="min-w-0">
                                    <label for="sub-status-{{ $subscription->id }}" class="block text-xs font-semibold text-slate-700">
                                        Ubah status langganan
                                    </label>
                                    <select
                                        id="sub-status-{{ $subscription->id }}"
                                        name="status"
                                        required
                                        class="mt-1.5 min-h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-xs font-medium text-slate-900 transition focus:border-emerald-600 focus:ring-2 focus:ring-emerald-600/15 focus:outline-none sm:text-sm"
                                    >
                                        <option value="" selected disabled>Pilih status baru…</option>
                                        @if (in_array($subscription->status, ['suspended', 'pending'], true))
                                            <option value="active">Aktif</option>
                                        @endif
                                        @if (in_array($subscription->status, ['active', 'pending'], true))
                                            <option value="suspended">Ditangguhkan</option>
                                        @endif
                                        <option value="cancelled">Dibatalkan permanen</option>
                                    </select>
                                </div>
                                <button
                                    type="submit"
                                    class="inline-flex min-h-10 w-full cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-800 shadow-2xs transition hover:border-emerald-600 hover:bg-emerald-50/50 hover:text-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:w-auto"
                                >
                                    Simpan status
                                </button>
                            </form>
                        @else
                            <div class="rounded-xl border border-slate-200/70 bg-slate-50/70 p-4 text-xs leading-relaxed text-slate-500">
                                Langganan ini sudah berakhir atau dibatalkan permanen dan tidak dapat diaktifkan kembali.
                            </div>
                        @endif
                    </article>
                @empty
                    <div class="rounded-2xl border border-slate-200/90 bg-white p-8 shadow-sm">
                        <x-ui.empty-state title="Belum ada langganan" description="Langganan warga akan muncul di sini setelah pemasangan selesai." />
                    </div>
                @endforelse
            </div>
        </section>

        {{-- Bagian 2: Paket Internet --}}
        <section id="packages" class="scroll-mt-6 space-y-4 pt-2" aria-labelledby="packages-heading">
            <header>
                <h2 id="packages-heading" class="text-lg font-bold text-slate-900 sm:text-xl">Paket internet</h2>
                <p class="mt-1 text-xs leading-relaxed text-slate-500 sm:text-sm">
                    Kecepatan ditulis dalam satuan Mbps (1 Mbps = 1.000 Kbps pada penyimpanan sistem).
                </p>
            </header>

            <div class="grid items-start gap-6 xl:grid-cols-12">
                {{-- Form Tambah Paket (5 cols) --}}
                <form
                    method="POST"
                    action="{{ route('services.packages.store') }}"
                    class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm xl:col-span-5"
                >
                    @csrf
                    <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <h3 class="text-base font-bold text-slate-900">Tambah paket baru</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Buat pilihan paket langganan internet untuk warga.</p>
                    </div>

                    <div class="space-y-4 p-6">
                        <div>
                            <label for="pkg-name" class="block text-xs font-semibold text-slate-700">
                                Nama paket <span class="text-rose-500">*</span>
                            </label>
                            <input
                                id="pkg-name"
                                name="name"
                                required
                                maxlength="120"
                                value="{{ old('name') }}"
                                placeholder="Contoh: Paket Rumah 20 Mbps"
                                class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                            >
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="pkg-price" class="block text-xs font-semibold text-slate-700">
                                    Harga per bulan <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative mt-1.5">
                                    <span class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-xs font-semibold text-slate-400">Rp</span>
                                    <input
                                        id="pkg-price"
                                        name="monthly_price"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        required
                                        value="{{ old('monthly_price') }}"
                                        placeholder="0"
                                        class="min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 pr-3.5 pl-9.5 text-sm font-medium text-slate-900 tabular-nums placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                    >
                                </div>
                            </div>

                            <div>
                                <label for="pkg-validity" class="block text-xs font-semibold text-slate-700">
                                    Masa aktif <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative mt-1.5">
                                    <input
                                        id="pkg-validity"
                                        name="validity_days"
                                        type="number"
                                        min="1"
                                        max="365"
                                        value="{{ old('validity_days', 30) }}"
                                        required
                                        class="min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 pr-12 pl-3.5 text-sm font-medium text-slate-900 tabular-nums transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                    >
                                    <span class="pointer-events-none absolute top-1/2 right-3.5 -translate-y-1/2 text-xs text-slate-400">hari</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="pkg-down" class="block text-xs font-semibold text-slate-700">Kecepatan unduh</label>
                                <div class="relative mt-1.5">
                                    <input
                                        id="pkg-down"
                                        name="bandwidth_download_mbps"
                                        type="number"
                                        min="0.001"
                                        step="0.001"
                                        value="{{ old('bandwidth_download_mbps') }}"
                                        placeholder="Contoh: 20"
                                        class="min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 pr-14 pl-3.5 text-sm text-slate-900 tabular-nums placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                    >
                                    <span class="pointer-events-none absolute top-1/2 right-3.5 -translate-y-1/2 text-xs font-medium text-slate-400">Mbps</span>
                                </div>
                            </div>

                            <div>
                                <label for="pkg-up" class="block text-xs font-semibold text-slate-700">Kecepatan unggah</label>
                                <div class="relative mt-1.5">
                                    <input
                                        id="pkg-up"
                                        name="bandwidth_upload_mbps"
                                        type="number"
                                        min="0.001"
                                        step="0.001"
                                        value="{{ old('bandwidth_upload_mbps') }}"
                                        placeholder="Contoh: 10"
                                        class="min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 pr-14 pl-3.5 text-sm text-slate-900 tabular-nums placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                    >
                                    <span class="pointer-events-none absolute top-1/2 right-3.5 -translate-y-1/2 text-xs font-medium text-slate-400">Mbps</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="pkg-desc" class="block text-xs font-semibold text-slate-700">Keterangan paket</label>
                            <textarea
                                id="pkg-desc"
                                name="description"
                                rows="3"
                                maxlength="5000"
                                placeholder="Informasi tambahan untuk calon pelanggan…"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm leading-relaxed text-slate-900 placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                            >{{ old('description') }}</textarea>
                        </div>

                        <label class="flex min-h-10 cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200/80 bg-slate-50/60 px-3.5 py-2 text-xs font-medium text-slate-700">
                            <input name="is_active" value="1" type="checkbox" checked class="size-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600">
                            <span>Aktif dan tampilkan untuk pendaftaran warga</span>
                        </label>
                    </div>

                    <div class="flex justify-end border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <button
                            type="submit"
                            class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow-md hover:shadow-emerald-600/25 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:w-auto"
                        >
                            Simpan paket
                        </button>
                    </div>
                </form>

                {{-- Daftar Paket Tersimpan (7 cols) --}}
                <div class="space-y-4 xl:col-span-7">
                    @forelse ($packages as $package)
                        <article class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm transition-shadow duration-200 hover:shadow-md">
                            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                                <div>
                                    <h3 class="text-base font-bold text-slate-900">{{ $package->name }}</h3>
                                    <p class="mt-0.5 text-xs text-slate-500">
                                        {{ $package->is_active ? 'Menerima pendaftaran warga' : 'Disembunyikan dari halaman warga' }}
                                    </p>
                                </div>
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold {{ $package->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-slate-200 bg-slate-100 text-slate-600' }}">
                                    <span class="size-1.5 rounded-full {{ $package->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}" aria-hidden="true"></span>
                                    <span>{{ $package->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </span>
                            </div>

                            <form method="POST" action="{{ route('services.packages.update', $package) }}" class="space-y-4 p-6">
                                @csrf
                                @method('PUT')

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700">Nama paket</label>
                                        <input
                                            name="name"
                                            value="{{ $package->name }}"
                                            required
                                            maxlength="120"
                                            class="mt-1.5 min-h-10 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 text-sm text-slate-900 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700">Harga per bulan</label>
                                        <div class="relative mt-1.5">
                                            <span class="pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-xs font-semibold text-slate-400">Rp</span>
                                            <input
                                                name="monthly_price"
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                value="{{ $package->monthly_price }}"
                                                required
                                                class="min-h-10 w-full rounded-xl border border-slate-200 bg-slate-50/60 pr-3 pl-9 text-sm font-medium text-slate-900 tabular-nums transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                            >
                                        </div>
                                    </div>
                                </div>

                                <div class="grid gap-4 sm:grid-cols-3">
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700">Unduh (Mbps)</label>
                                        <input
                                            name="bandwidth_download_mbps"
                                            type="number"
                                            min="0.001"
                                            step="0.001"
                                            value="{{ $package->bandwidth_download_kbps !== null ? $package->bandwidth_download_kbps / 1000 : '' }}"
                                            class="mt-1.5 min-h-10 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 text-sm text-slate-900 tabular-nums transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700">Unggah (Mbps)</label>
                                        <input
                                            name="bandwidth_upload_mbps"
                                            type="number"
                                            min="0.001"
                                            step="0.001"
                                            value="{{ $package->bandwidth_upload_kbps !== null ? $package->bandwidth_upload_kbps / 1000 : '' }}"
                                            class="mt-1.5 min-h-10 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 text-sm text-slate-900 tabular-nums transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700">Masa aktif (hari)</label>
                                        <input
                                            name="validity_days"
                                            type="number"
                                            min="1"
                                            max="365"
                                            value="{{ $package->validity_days }}"
                                            required
                                            class="mt-1.5 min-h-10 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 text-sm text-slate-900 tabular-nums transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                        >
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700">Keterangan</label>
                                    <textarea
                                        name="description"
                                        rows="2"
                                        maxlength="5000"
                                        class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-sm text-slate-900 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                    >{{ $package->description }}</textarea>
                                </div>

                                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                                    <label class="flex cursor-pointer items-center gap-2.5 text-xs font-medium text-slate-700">
                                        <input name="is_active" value="1" type="checkbox" @checked($package->is_active) class="size-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-600">
                                        <span>Tersedia untuk pendaftaran</span>
                                    </label>

                                    <button
                                        type="submit"
                                        class="inline-flex min-h-10 cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-800 shadow-2xs transition hover:border-emerald-600 hover:bg-emerald-50/50 hover:text-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                                    >
                                        Simpan perubahan
                                    </button>
                                </div>
                            </form>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-slate-200/90 bg-white p-8 shadow-sm">
                            <x-ui.empty-state title="Belum ada paket" description="Tambahkan paket pertama melalui formulir di samping." />
                        </div>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- Bagian 3: Wi-Fi Publik --}}
        <section id="wifi" class="scroll-mt-6 space-y-4 pt-2" aria-labelledby="wifi-heading">
            <header>
                <h2 id="wifi-heading" class="text-lg font-bold text-slate-900 sm:text-xl">Wi‑Fi publik</h2>
                <p class="mt-1 max-w-3xl text-xs leading-relaxed text-slate-500 sm:text-sm">
                    Pengaturan ini mengelola kebijakan dan informasi hotspot. Akses masih berupa simulasi aplikasi dan belum mengendalikan perangkat router secara langsung.
                </p>
            </header>

            <div class="grid items-start gap-6 xl:grid-cols-12">
                {{-- Form Tambah Hotspot (5 cols) --}}
                <form
                    method="POST"
                    action="{{ route('services.wifi.networks.store') }}"
                    class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm xl:col-span-5"
                >
                    @csrf
                    <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                        <h3 class="text-base font-bold text-slate-900">Tambah lokasi hotspot</h3>
                        <p class="mt-0.5 text-xs text-slate-500">Daftarkan titik akses Wi‑Fi publik baru di wilayah desa.</p>
                    </div>

                    <div class="space-y-4 p-6">
                        <div>
                            <label for="wifi-name" class="block text-xs font-semibold text-slate-700">
                                Nama lokasi <span class="text-rose-500">*</span>
                            </label>
                            <input
                                id="wifi-name"
                                name="name"
                                required
                                maxlength="120"
                                value="{{ old('name') }}"
                                placeholder="Contoh: Balai Desa Utama"
                                class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                            >
                        </div>

                        <div>
                            <label for="wifi-ssid" class="block text-xs font-semibold text-slate-700">
                                Nama jaringan (SSID) <span class="text-rose-500">*</span>
                            </label>
                            <input
                                id="wifi-ssid"
                                name="ssid"
                                required
                                maxlength="100"
                                value="{{ old('ssid') }}"
                                placeholder="Contoh: DesaDigital-FreeWiFi"
                                class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 font-mono text-sm text-slate-900 placeholder:font-sans placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                            >
                        </div>

                        <div>
                            <label for="wifi-location" class="block text-xs font-semibold text-slate-700">
                                Alamat atau keterangan lokasi <span class="text-rose-500">*</span>
                            </label>
                            <textarea
                                id="wifi-location"
                                name="location"
                                required
                                maxlength="255"
                                rows="2"
                                placeholder="Detail posisi titik hotspot…"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                            >{{ old('location') }}</textarea>
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="wifi-status" class="block text-xs font-semibold text-slate-700">Status</label>
                                <select
                                    id="wifi-status"
                                    name="status"
                                    class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 text-sm text-slate-900 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                >
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                    <option value="maintenance">Pemeliharaan</option>
                                </select>
                            </div>

                            <div>
                                <label for="wifi-minutes" class="block text-xs font-semibold text-slate-700">Lama sesi (menit)</label>
                                <input
                                    id="wifi-minutes"
                                    name="session_minutes"
                                    type="number"
                                    min="5"
                                    max="1440"
                                    value="60"
                                    required
                                    class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-sm font-medium text-slate-900 tabular-nums transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                >
                            </div>
                        </div>

                        <div>
                            <label for="wifi-eligibility" class="block text-xs font-semibold text-slate-700">Syarat pengguna</label>
                            <select
                                id="wifi-eligibility"
                                name="eligibility"
                                class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-sm text-slate-900 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                            >
                                <option value="authenticated_residents">Warga dengan profil terdaftar</option>
                                <option value="verified_residents">Warga dengan wilayah terverifikasi</option>
                            </select>
                        </div>

                        <div>
                            <label for="wifi-terms" class="block text-xs font-semibold text-slate-700">Aturan penggunaan</label>
                            <textarea
                                id="wifi-terms"
                                name="terms"
                                required
                                maxlength="3000"
                                rows="3"
                                class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2 text-sm leading-relaxed text-slate-900 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                            >Gunakan jaringan dengan bertanggung jawab.</textarea>
                        </div>
                    </div>

                    <div class="flex justify-end border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                        <button
                            type="submit"
                            class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow-md hover:shadow-emerald-600/25 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 sm:w-auto"
                        >
                            Simpan hotspot
                        </button>
                    </div>
                </form>

                {{-- Daftar Hotspot Tersimpan (7 cols) --}}
                <div class="space-y-4 xl:col-span-7">
                    @forelse ($networks as $network)
                        @php
                            $netBadge = match ($network->status) {
                                'active' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                                'maintenance' => 'border-amber-200 bg-amber-50 text-amber-800',
                                default => 'border-slate-200 bg-slate-100 text-slate-600',
                            };
                            $netDot = match ($network->status) {
                                'active' => 'bg-emerald-500',
                                'maintenance' => 'bg-amber-500',
                                default => 'bg-slate-400',
                            };
                            $netLabel = match ($network->status) {
                                'active' => 'Aktif',
                                'maintenance' => 'Pemeliharaan',
                                default => 'Nonaktif',
                            };
                        @endphp

                        <article class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm transition-shadow duration-200 hover:shadow-md">
                            <div class="flex flex-wrap items-start justify-between gap-3 p-5 sm:px-6">
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="text-base font-bold text-slate-900">{{ $network->name }}</h3>
                                        <span class="rounded-md bg-slate-100 px-2 py-0.5 font-mono text-xs font-medium text-slate-700">
                                            {{ $network->ssid }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">{{ $network->location }}</p>
                                </div>

                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold {{ $netBadge }}">
                                    <span class="size-1.5 rounded-full {{ $netDot }}" aria-hidden="true"></span>
                                    <span>{{ $netLabel }}</span>
                                </span>
                            </div>

                            <details class="group border-t border-slate-100 bg-slate-50/50">
                                <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between px-6 py-3 text-xs font-semibold text-emerald-700 transition hover:bg-slate-100/60 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                                    <span>Ubah pengaturan hotspot</span>
                                    <svg class="size-4 text-slate-400 transition-transform duration-200 group-open:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                        <path d="m6 9 6 6 6-6" />
                                    </svg>
                                </summary>

                                <form method="POST" action="{{ route('services.wifi.networks.update', $network) }}" class="space-y-4 border-t border-slate-100 bg-white p-6">
                                    @csrf
                                    @method('PUT')

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700">Nama lokasi</label>
                                            <input
                                                name="name"
                                                value="{{ $network->name }}"
                                                required
                                                maxlength="120"
                                                class="mt-1.5 min-h-10 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 text-sm text-slate-900 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                            >
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700">Nama jaringan (SSID)</label>
                                            <input
                                                name="ssid"
                                                value="{{ $network->ssid }}"
                                                required
                                                maxlength="100"
                                                class="mt-1.5 min-h-10 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 font-mono text-sm text-slate-900 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                            >
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700">Alamat atau keterangan lokasi</label>
                                        <input
                                            name="location"
                                            value="{{ $network->location }}"
                                            required
                                            maxlength="255"
                                            class="mt-1.5 min-h-10 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 text-sm text-slate-900 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                        >
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700">Status</label>
                                            <select
                                                name="status"
                                                class="mt-1.5 min-h-10 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 text-sm text-slate-900 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                            >
                                                <option value="active" @selected($network->status === 'active')>Aktif</option>
                                                <option value="inactive" @selected($network->status === 'inactive')>Nonaktif</option>
                                                <option value="maintenance" @selected($network->status === 'maintenance')>Pemeliharaan</option>
                                            </select>
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-slate-700">Lama sesi simulasi (menit)</label>
                                            <input
                                                name="session_minutes"
                                                type="number"
                                                min="5"
                                                max="1440"
                                                value="{{ data_get($network->access_policy, 'session_minutes', 60) }}"
                                                required
                                                class="mt-1.5 min-h-10 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 text-sm text-slate-900 tabular-nums transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                            >
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700">Siapa yang boleh menggunakan</label>
                                        <select
                                            name="eligibility"
                                            class="mt-1.5 min-h-10 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 text-sm text-slate-900 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                        >
                                            <option value="authenticated_residents" @selected(data_get($network->access_policy, 'eligibility') === 'authenticated_residents')>Warga dengan profil terdaftar</option>
                                            <option value="verified_residents" @selected(data_get($network->access_policy, 'eligibility') === 'verified_residents')>Warga dengan wilayah terverifikasi</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700">Aturan penggunaan</label>
                                        <textarea
                                            name="terms"
                                            required
                                            maxlength="3000"
                                            rows="3"
                                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3 py-2 text-sm text-slate-900 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                        >{{ data_get($network->access_policy, 'terms') }}</textarea>
                                    </div>

                                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                                        <p class="max-w-md text-[11px] leading-relaxed text-slate-400">
                                            Tipe koneksi tercatat: <span class="font-medium text-slate-600">{{ $network->devices->pluck('integration_type')->unique()->join(', ') ?: 'belum ada perangkat' }}</span>. Pengaturan ini belum diterapkan ke router.
                                        </p>

                                        <button
                                            type="submit"
                                            class="inline-flex min-h-10 cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-800 shadow-2xs transition hover:border-emerald-600 hover:bg-emerald-50/50 hover:text-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                                        >
                                            Simpan pengaturan
                                        </button>
                                    </div>
                                </form>
                            </details>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-slate-200/90 bg-white p-8 shadow-sm">
                            <x-ui.empty-state title="Belum ada hotspot" description="Tambahkan lokasi Wi‑Fi publik untuk mulai mengatur kebijakannya." />
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </main>
</x-layouts.app>
