<x-layouts.app title="Saldo dan pendapatan toko">
    <main id="main" class="mx-auto max-w-6xl space-y-7 px-4 py-8 sm:px-6 lg:px-8">
        <x-ui.page-heading title="Saldo dan pendapatan toko" description="Pendapatan masuk ke saldo tersedia setelah pembayaran terkonfirmasi dan pesanan selesai." />
        @if (session('status'))<p role="status" class="rounded-md bg-emerald-50 p-4 text-emerald-800">{{ session('status') }}</p>@endif

        <section aria-label="Ringkasan saldo" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <article class="rounded-card border border-line bg-paper p-5"><p class="text-sm text-muted">Saldo tersedia</p><p class="mt-2 text-2xl font-semibold tabular-nums">Rp{{ number_format((float) $balance, 0, ',', '.') }}</p></article>
            <article class="rounded-card border border-line bg-paper p-5"><p class="text-sm text-muted">Pendapatan terkonfirmasi</p><p class="mt-2 text-2xl font-semibold tabular-nums">Rp{{ number_format((float) $earned, 0, ',', '.') }}</p></article>
            <article class="rounded-card border border-line bg-paper p-5"><p class="text-sm text-muted">Saldo dalam pencairan</p><p class="mt-2 text-2xl font-semibold tabular-nums">Rp{{ number_format((float) $pendingWithdrawals, 0, ',', '.') }}</p></article>
            <article class="rounded-card border border-line bg-paper p-5"><p class="text-sm text-muted">Barang terjual, pesanan selesai</p><p class="mt-2 text-2xl font-semibold tabular-nums">{{ number_format((int) $soldItems, 0, ',', '.') }}</p></article>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-4 rounded-card border border-line bg-paper p-5">
                <div><h2 class="text-lg font-semibold">Ajukan pencairan</h2><p class="mt-1 text-sm text-muted">Permintaan hanya dapat menggunakan saldo tersedia. Saldo ditahan sebelum payout dikirim untuk mencegah pencairan ganda.</p></div>
                @if (! $withdrawalsEnabled)
                    <p class="rounded-md bg-amber-50 p-3 text-sm text-amber-950">Payout Xendit belum aktif. Hubungi pengelola setelah konfigurasi Money Out tersedia.</p>
                @elseif ($seller->payoutAccount?->status === 'verified')
                    <p class="rounded-md bg-emerald-50 p-3 text-sm text-emerald-900">Rekening terverifikasi: {{ $seller->payoutAccount->bank_name }} · {{ $seller->payoutAccount->maskedAccountNumber() }}</p>
                    <form method="POST" action="{{ route('marketplace.seller.withdrawals.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="request_key" value="{{ $requestKey }}">
                        <label class="block text-sm font-medium">Jumlah pencairan (Rp)<input name="amount" type="number" min="1" max="999999999999" step="1" required inputmode="numeric" autocomplete="off" placeholder="Contoh: 100000…" class="mt-1 min-h-11 w-full rounded-control border border-line px-3 text-base"></label>
                        @error('amount')<p class="text-sm text-danger">{{ $message }}</p>@enderror
                        <x-ui.button type="submit">Ajukan pencairan</x-ui.button>
                    </form>
                @else
                    <p class="rounded-md bg-amber-50 p-3 text-sm text-amber-950">{{ $seller->payoutAccount ? 'Rekening menunggu verifikasi atau perlu diperbarui.' : 'Tambahkan rekening di bawah. Pencairan baru dapat diajukan setelah pengelola memverifikasinya.' }}</p>
                @endif
            </div>

            <form method="POST" action="{{ route('marketplace.seller.payout-account.update') }}" class="space-y-4 rounded-card border border-line bg-paper p-5">
                @csrf @method('PUT')
                <div><h2 class="text-lg font-semibold">Rekening pencairan</h2><p class="mt-1 text-sm text-muted">Nomor rekening dienkripsi. Perubahan akan memerlukan verifikasi ulang.</p></div>
                @if ($seller->payoutAccount)<p class="text-sm">Status: <strong class="capitalize">{{ str_replace('_', ' ', $seller->payoutAccount->status) }}</strong> · {{ $seller->payoutAccount->maskedAccountNumber() }}</p>@endif
                <label class="block text-sm font-medium">Nama bank<input name="bank_name" required maxlength="100" value="{{ old('bank_name', $seller->payoutAccount?->bank_name) }}" class="mt-1 min-h-11 w-full rounded-control border border-line px-3 text-base"></label>
                <label class="block text-sm font-medium">Kode SWIFT/BIC<input name="bank_swift_code" required maxlength="11" placeholder="Contoh: CENAIDJA" value="{{ old('bank_swift_code', $seller->payoutAccount?->bank_swift_code) }}" class="mt-1 min-h-11 w-full rounded-control border border-line px-3 text-base"></label>
                <label class="block text-sm font-medium">Nama pemilik rekening<input name="account_holder_name" required maxlength="255" autocomplete="name" value="{{ old('account_holder_name', $seller->payoutAccount?->account_holder_name) }}" class="mt-1 min-h-11 w-full rounded-control border border-line px-3 text-base"></label>
                <label class="block text-sm font-medium">Nomor rekening<input name="account_number" required inputmode="numeric" pattern="[0-9]{5,34}" placeholder="Masukkan nomor rekening…" class="mt-1 min-h-11 w-full rounded-control border border-line px-3 text-base"></label>
                <label class="block text-sm font-medium">Alamat penerima<textarea name="recipient_address" required maxlength="500" autocomplete="street-address" rows="2" class="mt-1 w-full rounded-control border border-line px-3 py-2 text-base">{{ old('recipient_address', $seller->payoutAccount?->recipient_address) }}</textarea></label>
                <label class="block text-sm font-medium">Jenis penerima<select name="recipient_type" required class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base"><option value="INDIVIDUAL" @selected(old('recipient_type', $seller->payoutAccount?->recipient_type ?? 'INDIVIDUAL') === 'INDIVIDUAL')>Perorangan</option><option value="BUSINESS" @selected(old('recipient_type', $seller->payoutAccount?->recipient_type) === 'BUSINESS')>Badan usaha</option></select></label>
                @foreach(['bank_name', 'bank_swift_code', 'account_holder_name', 'account_number', 'recipient_address', 'recipient_type'] as $field)@error($field)<p class="text-sm text-danger">{{ $message }}</p>@enderror @endforeach
                <x-ui.button type="submit">Simpan rekening</x-ui.button>
            </form>
        </section>

        <section class="space-y-4" aria-labelledby="balance-history-heading">
            <h2 id="balance-history-heading" class="text-xl font-semibold">Mutasi saldo</h2>
            @forelse ($entries as $entry)
                <article class="flex flex-wrap justify-between gap-3 rounded-card border border-line bg-paper p-4">
                    <div><p class="font-medium">{{ $entry->description }}</p><p class="mt-1 text-sm text-muted">{{ $entry->created_at->format('d M Y H:i') }}</p></div>
                    <strong class="tabular-nums {{ (float) $entry->amount < 0 ? 'text-danger' : 'text-emerald-800' }}">{{ (float) $entry->amount < 0 ? '−' : '+' }}Rp{{ number_format(abs((float) $entry->amount), 0, ',', '.') }}</strong>
                </article>
            @empty
                <x-ui.empty-state title="Belum ada mutasi saldo" description="Pendapatan dari pesanan yang selesai dan pencairan akan tercatat di sini." />
            @endforelse
            {{ $entries->links() }}
        </section>

        <section class="space-y-4" aria-labelledby="withdrawal-history-heading">
            <h2 id="withdrawal-history-heading" class="text-xl font-semibold">Status permintaan pencairan</h2>
            @forelse ($withdrawals as $withdrawal)
                <article class="flex flex-wrap justify-between gap-3 rounded-card border border-line bg-paper p-4">
                    <div><p class="font-medium">Pencairan {{ $withdrawal->reference_id }}</p><p class="mt-1 text-sm text-muted">{{ $withdrawal->requested_at->format('d M Y H:i') }} · {{ str_replace('_', ' ', $withdrawal->status) }}</p>@if($withdrawal->failure_code)<p class="mt-1 text-sm text-danger">Perlu ditinjau: {{ $withdrawal->failure_code }}</p>@endif</div>
                    <strong class="tabular-nums">Rp{{ number_format((float) $withdrawal->amount, 0, ',', '.') }}</strong>
                </article>
            @empty
                <x-ui.empty-state title="Belum ada pencairan" description="Pendapatan pesanan selesai akan muncul sebagai saldo tersedia." />
            @endforelse
            {{ $withdrawals->links() }}
        </section>
    </main>
</x-layouts.app>
