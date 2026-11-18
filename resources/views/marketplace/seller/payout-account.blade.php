<x-layouts.app title="Saldo dan pendapatan toko">
    <main id="main" class="mx-auto max-w-6xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">
        {{-- Header Halaman Minimalis --}}
        <header class="flex flex-col gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-center sm:justify-between">
            <div class="max-w-2xl">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">
                    Saldo dan pendapatan toko
                </h1>
                <p class="mt-1.5 text-sm leading-relaxed text-slate-500">
                    Pendapatan otomatis masuk ke saldo tersedia setelah pembayaran terkonfirmasi dan pesanan dinyatakan selesai.
                </p>
            </div>

            <div class="shrink-0">
                <a
                    href="{{ route('marketplace.seller.orders') }}"
                    class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 shadow-xs transition hover:-translate-y-0.5 hover:border-emerald-600 hover:bg-emerald-50/40 hover:text-emerald-700 hover:shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                >
                    Lihat pesanan toko
                </a>
            </div>
        </header>

        {{-- Notifikasi Status --}}
        @if (session('status'))
            <div role="status" class="flex items-start gap-2.5 rounded-2xl border border-emerald-200 bg-emerald-50/90 px-4 py-3.5 text-sm font-medium text-emerald-900 shadow-2xs">
                <svg class="mt-0.5 size-4 shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10" />
                    <path d="m9 12 2 2 4-4" />
                </svg>
                <p>{{ session('status') }}</p>
            </div>
        @endif

        {{-- Ringkasan 4 Metrik Saldo --}}
        @php
            $formatMetricSize = static function (string $text): string {
                $len = mb_strlen($text);
                return match (true) {
                    $len <= 11 => 'text-2xl',
                    $len <= 14 => 'text-xl',
                    $len <= 17 => 'text-lg',
                    default => 'text-base',
                };
            };

            $balanceText = 'Rp' . number_format((float) $balance, 0, ',', '.');
            $earnedText = 'Rp' . number_format((float) $earned, 0, ',', '.');
            $pendingText = 'Rp' . number_format((float) $pendingWithdrawals, 0, ',', '.');
            $soldText = number_format((int) $soldItems, 0, ',', '.');
        @endphp

        <section aria-label="Ringkasan saldo" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Highlight Kartu Utama: Saldo Tersedia --}}
            <article class="relative min-w-0 overflow-hidden rounded-2xl border border-emerald-200/90 bg-gradient-to-br from-emerald-50/80 via-white to-white p-5 shadow-sm transition-shadow duration-200 hover:shadow-md">
                <div class="flex items-center gap-2">
                    <span class="size-2 shrink-0 rounded-full bg-emerald-500" aria-hidden="true"></span>
                    <p class="truncate text-xs font-semibold text-emerald-800">Saldo tersedia</p>
                </div>
                <p class="mt-3 {{ $formatMetricSize($balanceText) }} font-bold leading-tight tracking-tight text-slate-900 tabular-nums break-words">
                    {{ $balanceText }}
                </p>
                <p class="mt-1.5 text-[11px] text-slate-500">Siap dicairkan ke rekening</p>
            </article>

            <article class="min-w-0 rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm transition-shadow duration-200 hover:shadow-md">
                <div class="flex items-center gap-2">
                    <span class="size-2 shrink-0 rounded-full bg-sky-500" aria-hidden="true"></span>
                    <p class="truncate text-xs font-medium text-slate-500">Pendapatan terkonfirmasi</p>
                </div>
                <p class="mt-3 {{ $formatMetricSize($earnedText) }} font-bold leading-tight tracking-tight text-slate-900 tabular-nums break-words">
                    {{ $earnedText }}
                </p>
                <p class="mt-1.5 text-[11px] text-slate-400">Akumulasi bersih penjualan</p>
            </article>

            <article class="min-w-0 rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm transition-shadow duration-200 hover:shadow-md">
                <div class="flex items-center gap-2">
                    <span class="size-2 shrink-0 rounded-full bg-amber-500" aria-hidden="true"></span>
                    <p class="truncate text-xs font-medium text-slate-500">Saldo dalam pencairan</p>
                </div>
                <p class="mt-3 {{ $formatMetricSize($pendingText) }} font-bold leading-tight tracking-tight text-slate-900 tabular-nums break-words">
                    {{ $pendingText }}
                </p>
                <p class="mt-1.5 text-[11px] text-slate-400">Sedang diproses transfer</p>
            </article>

            <article class="min-w-0 rounded-2xl border border-slate-200/90 bg-white p-5 shadow-sm transition-shadow duration-200 hover:shadow-md">
                <div class="flex items-center gap-2">
                    <span class="size-2 shrink-0 rounded-full bg-slate-400" aria-hidden="true"></span>
                    <p class="truncate text-xs font-medium text-slate-500">Barang terjual</p>
                </div>
                <p class="mt-3 {{ $formatMetricSize($soldText . ' unit') }} font-bold leading-tight tracking-tight text-slate-900 tabular-nums break-words">
                    {{ $soldText }}
                    <span class="text-xs font-normal text-slate-400">unit</span>
                </p>
                <p class="mt-1.5 text-[11px] text-slate-400">Dari pesanan berstatus selesai</p>
            </article>
        </section>

        {{-- Kolom Utama: Pencairan & Pengaturan Rekening --}}
        <section class="grid items-start gap-6 lg:grid-cols-12">
            {{-- Kolom Kiri (5 cols): Ajukan Pencairan --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm lg:col-span-5">
                <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                    <h2 class="text-base font-bold text-slate-900">Ajukan pencairan</h2>
                    <p class="mt-0.5 text-xs leading-relaxed text-slate-500">
                        Permintaan hanya dapat menggunakan saldo tersedia dan ditahan sementara hingga transfer selesai.
                    </p>
                </div>

                <div class="space-y-5 p-6">
                    @if (! $withdrawalsEnabled)
                        <div class="flex items-start gap-2.5 rounded-xl border border-amber-200/80 bg-amber-50/80 p-3.5 text-xs leading-relaxed text-amber-950">
                            <svg class="mt-0.5 size-4 shrink-0 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" x2="12" y1="8" y2="12" />
                                <line x1="12" x2="12.01" y1="16" y2="16" />
                            </svg>
                            <p>Payout Xendit belum aktif. Hubungi pengelola setelah konfigurasi Money Out tersedia.</p>
                        </div>
                    @elseif ($seller->payoutAccount?->status === 'verified')
                        <div class="rounded-xl border border-emerald-200/80 bg-emerald-50/60 p-3.5">
                            <p class="text-[11px] font-semibold uppercase tracking-wider text-emerald-700">Rekening tujuan terverifikasi</p>
                            <p class="mt-1 text-sm font-bold text-slate-900">
                                {{ $seller->payoutAccount->bank_name }}
                                <span class="mx-1.5 font-normal text-slate-300">·</span>
                                <span class="font-mono font-semibold text-slate-700">{{ $seller->payoutAccount->maskedAccountNumber() }}</span>
                            </p>
                        </div>

                        <form method="POST" action="{{ route('marketplace.seller.withdrawals.store') }}" class="space-y-4">
                            @csrf
                            <input type="hidden" name="request_key" value="{{ $requestKey }}">

                            <div>
                                <label for="withdraw-amount" class="block text-xs font-semibold text-slate-700">
                                    Jumlah pencairan <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative mt-1.5">
                                    <span class="pointer-events-none absolute top-1/2 left-3.5 -translate-y-1/2 text-xs font-semibold text-slate-400">
                                        Rp
                                    </span>
                                    <input
                                        id="withdraw-amount"
                                        name="amount"
                                        type="number"
                                        min="1"
                                        max="999999999999"
                                        step="1"
                                        required
                                        value="{{ old('amount') }}"
                                        inputmode="numeric"
                                        autocomplete="off"
                                        placeholder="Contoh: 100000"
                                        class="min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 pr-3.5 pl-9.5 text-sm font-semibold text-slate-900 tabular-nums placeholder:font-normal placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                                    >
                                </div>
                                @error('amount')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <button
                                type="submit"
                                class="inline-flex min-h-11 w-full cursor-pointer items-center justify-center rounded-xl bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm shadow-emerald-600/20 transition hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow-md hover:shadow-emerald-600/25 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                            >
                                Ajukan pencairan dana
                            </button>
                        </form>
                    @else
                        <div class="flex items-start gap-2.5 rounded-xl border border-amber-200/80 bg-amber-50/80 p-3.5 text-xs leading-relaxed text-amber-950">
                            <svg class="mt-0.5 size-4 shrink-0 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <circle cx="12" cy="12" r="10" />
                                <line x1="12" x2="12" y1="8" y2="12" />
                                <line x1="12" x2="12.01" y1="16" y2="16" />
                            </svg>
                            <p>{{ $seller->payoutAccount ? 'Rekening menunggu verifikasi pengelola atau perlu diperbarui sebelum pencairan dapat diajukan.' : 'Tambahkan rekening pencairan di samping. Pencairan baru dapat diajukan setelah pengelola memverifikasinya.' }}</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Kolom Kanan (7 cols): Form Rekening Pencairan --}}
            <form
                method="POST"
                action="{{ route('marketplace.seller.payout-account.update') }}"
                class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm lg:col-span-7"
            >
                @csrf
                @method('PUT')

                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-slate-50/60 px-6 py-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Rekening pencairan</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Nomor rekening disimpan terenkripsi. Perubahan data memerlukan verifikasi ulang.</p>
                    </div>

                    @if ($seller->payoutAccount)
                        @php
                            $accStatus = $seller->payoutAccount->status;
                            $accBadgeClass = $accStatus === 'verified'
                                ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                                : ($accStatus === 'rejected' ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-amber-200 bg-amber-50 text-amber-800');
                            $accDotClass = $accStatus === 'verified'
                                ? 'bg-emerald-500'
                                : ($accStatus === 'rejected' ? 'bg-rose-500' : 'bg-amber-500');
                        @endphp
                        <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $accBadgeClass }}">
                            <span class="size-1.5 rounded-full {{ $accDotClass }}" aria-hidden="true"></span>
                            <span class="capitalize">{{ str_replace('_', ' ', $accStatus) }} · {{ $seller->payoutAccount->maskedAccountNumber() }}</span>
                        </span>
                    @endif
                </div>

                <div class="space-y-4 p-6">
                    {{-- Baris 1: Nama Bank & Kode SWIFT/BIC --}}
                    <div class="grid gap-4 sm:grid-cols-12">
                        <div class="sm:col-span-7">
                            <label for="payout-bank-name" class="block text-xs font-semibold text-slate-700">
                                Nama bank <span class="text-rose-500">*</span>
                            </label>
                            <input
                                id="payout-bank-name"
                                name="bank_name"
                                required
                                maxlength="100"
                                value="{{ old('bank_name', $seller->payoutAccount?->bank_name) }}"
                                placeholder="Contoh: Bank Central Asia (BCA)"
                                class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                            >
                            @error('bank_name')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-5">
                            <label for="payout-swift-code" class="block text-xs font-semibold text-slate-700">
                                Kode SWIFT/BIC <span class="text-rose-500">*</span>
                            </label>
                            <input
                                id="payout-swift-code"
                                name="bank_swift_code"
                                required
                                maxlength="11"
                                placeholder="Contoh: CENAIDJA"
                                value="{{ old('bank_swift_code', $seller->payoutAccount?->bank_swift_code) }}"
                                class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 font-mono text-sm uppercase text-slate-900 placeholder:font-sans placeholder:normal-case placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                            >
                            @error('bank_swift_code')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Baris 2: Nama Pemilik & Nomor Rekening --}}
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="payout-holder-name" class="block text-xs font-semibold text-slate-700">
                                Nama pemilik rekening <span class="text-rose-500">*</span>
                            </label>
                            <input
                                id="payout-holder-name"
                                name="account_holder_name"
                                required
                                maxlength="255"
                                autocomplete="name"
                                value="{{ old('account_holder_name', $seller->payoutAccount?->account_holder_name) }}"
                                placeholder="Sesuai buku tabungan"
                                class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-sm text-slate-900 placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                            >
                            @error('account_holder_name')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="payout-account-number" class="block text-xs font-semibold text-slate-700">
                                Nomor rekening <span class="text-rose-500">*</span>
                            </label>
                            <input
                                id="payout-account-number"
                                name="account_number"
                                required
                                inputmode="numeric"
                                pattern="[0-9]{5,34}"
                                placeholder="Masukkan nomor rekening…"
                                class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 font-mono text-sm text-slate-900 placeholder:font-sans placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                            >
                            @error('account_number')
                                <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Baris 3: Jenis Penerima & Alamat --}}
                    <div>
                        <label for="payout-recipient-type" class="block text-xs font-semibold text-slate-700">
                            Jenis penerima <span class="text-rose-500">*</span>
                        </label>
                        <select
                            id="payout-recipient-type"
                            name="recipient_type"
                            required
                            class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 text-sm text-slate-900 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                        >
                            <option value="INDIVIDUAL" @selected(old('recipient_type', $seller->payoutAccount?->recipient_type ?? 'INDIVIDUAL') === 'INDIVIDUAL')>Perorangan</option>
                            <option value="BUSINESS" @selected(old('recipient_type', $seller->payoutAccount?->recipient_type) === 'BUSINESS')>Badan usaha</option>
                        </select>
                        @error('recipient_type')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="payout-recipient-address" class="block text-xs font-semibold text-slate-700">
                            Alamat penerima <span class="text-rose-500">*</span>
                        </label>
                        <textarea
                            id="payout-recipient-address"
                            name="recipient_address"
                            required
                            maxlength="500"
                            autocomplete="street-address"
                            rows="2"
                            placeholder="Alamat lengkap penerima sesuai identitas…"
                            class="mt-1.5 w-full rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-2.5 text-sm leading-relaxed text-slate-900 placeholder:text-slate-400 transition focus:border-emerald-600 focus:bg-white focus:ring-2 focus:ring-emerald-600/15 focus:outline-none"
                        >{{ old('recipient_address', $seller->payoutAccount?->recipient_address) }}</textarea>
                        @error('recipient_address')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                    <button
                        type="submit"
                        class="inline-flex min-h-11 cursor-pointer items-center justify-center rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-800 shadow-xs transition hover:-translate-y-0.5 hover:border-emerald-600 hover:bg-emerald-50/40 hover:text-emerald-700 hover:shadow-sm focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600"
                    >
                        Simpan rekening
                    </button>
                </div>
            </form>
        </section>

        {{-- Riwayat Mutasi & Pencairan dalam 2 Kolom Sejajar --}}
        <div class="grid items-start gap-6 lg:grid-cols-2">
            {{-- Mutasi Saldo --}}
            <section class="space-y-3.5" aria-labelledby="balance-history-heading">
                <div>
                    <h2 id="balance-history-heading" class="text-lg font-bold text-slate-900">
                        Mutasi saldo
                    </h2>
                    <p class="text-xs text-slate-500">Catatan dana masuk dari penjualan dan potongan pencairan.</p>
                </div>

                <div class="space-y-2.5">
                    @forelse ($entries as $entry)
                        @php
                            $isNegative = (float) $entry->amount < 0;
                        @endphp
                        <article class="flex items-center justify-between gap-4 rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs transition hover:border-slate-300 hover:shadow-sm">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ $entry->description }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $entry->created_at->format('d M Y, H:i') }}</p>
                            </div>
                            <strong class="shrink-0 text-sm font-bold tabular-nums {{ $isNegative ? 'text-rose-600' : 'text-emerald-700' }}">
                                {{ $isNegative ? '−' : '+' }}Rp{{ number_format(abs((float) $entry->amount), 0, ',', '.') }}
                            </strong>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
                            <x-ui.empty-state
                                title="Belum ada mutasi saldo"
                                description="Pendapatan dari pesanan yang selesai dan pencairan akan tercatat di sini."
                            />
                        </div>
                    @endforelse
                </div>

                <div>
                    {{ $entries->links() }}
                </div>
            </section>

            {{-- Status Permintaan Pencairan --}}
            <section class="space-y-3.5" aria-labelledby="withdrawal-history-heading">
                <div>
                    <h2 id="withdrawal-history-heading" class="text-lg font-bold text-slate-900">
                        Status permintaan pencairan
                    </h2>
                    <p class="text-xs text-slate-500">Riwayat penarikan dana ke rekening terdaftar.</p>
                </div>

                <div class="space-y-2.5">
                    @forelse ($withdrawals as $withdrawal)
                        @php
                            $wStatus = $withdrawal->status;
                            $wBadgeClass = in_array($wStatus, ['completed', 'succeeded', 'paid'])
                                ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                                : (in_array($wStatus, ['failed', 'rejected']) ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-amber-200 bg-amber-50 text-amber-800');
                            $wDotClass = in_array($wStatus, ['completed', 'succeeded', 'paid'])
                                ? 'bg-emerald-500'
                                : (in_array($wStatus, ['failed', 'rejected']) ? 'bg-rose-500' : 'bg-amber-500');
                        @endphp
                        <article class="flex items-start justify-between gap-4 rounded-2xl border border-slate-200/90 bg-white p-4 shadow-2xs transition hover:border-slate-300 hover:shadow-sm">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-mono text-xs font-bold text-slate-800">{{ $withdrawal->reference_id }}</p>
                                    <span class="inline-flex items-center gap-1.5 rounded-full border px-2 py-0.5 text-[11px] font-semibold capitalize {{ $wBadgeClass }}">
                                        <span class="size-1.5 rounded-full {{ $wDotClass }}" aria-hidden="true"></span>
                                        <span>{{ str_replace('_', ' ', $wStatus) }}</span>
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-slate-400">{{ $withdrawal->requested_at->format('d M Y, H:i') }}</p>
                                @if($withdrawal->failure_code)
                                    <p class="mt-1.5 rounded-lg bg-rose-50 px-2.5 py-1 text-xs font-medium text-rose-700">
                                        Perlu ditinjau: {{ $withdrawal->failure_code }}
                                    </p>
                                @endif
                            </div>
                            <strong class="shrink-0 text-sm font-bold text-slate-900 tabular-nums">
                                Rp{{ number_format((float) $withdrawal->amount, 0, ',', '.') }}
                            </strong>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-2xs">
                            <x-ui.empty-state
                                title="Belum ada pencairan"
                                description="Pendapatan pesanan selesai akan muncul sebagai saldo tersedia."
                            />
                        </div>
                    @endforelse
                </div>

                <div>
                    {{ $withdrawals->links() }}
                </div>
            </section>
        </div>
    </main>
</x-layouts.app>
