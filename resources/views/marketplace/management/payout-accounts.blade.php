<x-layouts.app title="Rekening dan pencairan penjual">
    <main id="main" class="mx-auto max-w-6xl space-y-5 px-4 py-8 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-slate-900">Rekening dan pencairan penjual</h1>
        @if (session('status'))<p role="status" class="rounded-md bg-emerald-50 p-4 text-emerald-800">{{ session('status') }}</p>@endif
        <p class="text-slate-600">Verifikasi tujuan rekening. Pendapatan baru tersedia setelah pesanan selesai dan pembayarannya terkonfirmasi.</p>
        @forelse ($accounts as $account)
            <article class="space-y-4 rounded-lg border border-slate-200 bg-white p-5">
                <div class="grid gap-2 sm:grid-cols-2">
                    <p><strong>{{ $account->seller->business_name }}</strong> · {{ $account->seller->user->name }}</p>
                    <p>Status: <span class="capitalize">{{ str_replace('_', ' ', $account->status) }}</span></p>
                    <p>Bank: {{ $account->bank_name }} ({{ $account->bank_swift_code }})</p>
                    <p>Jenis penerima: {{ $account->recipient_type === 'BUSINESS' ? 'Badan usaha' : 'Perorangan' }}</p>
                    <p>Nama rekening: {{ $account->account_holder_name }}</p>
                    <p>Nomor rekening: {{ $account->account_number }}</p>
                    <p class="sm:col-span-2">Alamat penerima: {{ $account->recipient_address }}</p>
                </div>
                @if ($account->status !== 'verified')
                    <div class="flex flex-wrap gap-3 border-t border-slate-100 pt-4">
                        <form method="POST" action="{{ route('marketplace.management.payout-accounts.review', $account) }}">
                            @csrf
                            <input type="hidden" name="status" value="verified">
                            <button class="min-h-11 rounded-md bg-emerald-700 px-4 py-2 font-semibold text-white hover:bg-emerald-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700">Verifikasi rekening</button>
                        </form>
                        <form method="POST" action="{{ route('marketplace.management.payout-accounts.review', $account) }}" class="flex flex-1 flex-wrap gap-2">
                            @csrf
                            <input type="hidden" name="status" value="rejected">
                            <label class="sr-only" for="reason-{{ $account->id }}">Catatan penolakan rekening</label>
                            <input id="reason-{{ $account->id }}" name="reason" required maxlength="500" placeholder="Alasan penolakan…" class="min-h-11 min-w-64 flex-1 rounded-md border-slate-300 text-base focus:border-rose-600 focus:ring-rose-600">
                            <button class="min-h-11 rounded-md border border-rose-700 px-4 py-2 font-semibold text-rose-800 hover:bg-rose-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-rose-700">Tolak</button>
                        </form>
                    </div>
                @endif
            </article>
        @empty
            <p class="rounded-lg border border-slate-200 bg-white p-5 text-slate-600">Belum ada rekening payout yang dikirim untuk ditinjau.</p>
        @endforelse
        {{ $accounts->links() }}

        <section class="space-y-4 border-t border-line pt-6" aria-labelledby="payout-review-heading">
            <h2 id="payout-review-heading" class="text-xl font-semibold">Pencairan yang perlu dipantau</h2>
            @forelse ($withdrawals as $withdrawal)
                <article class="rounded-lg border border-slate-200 bg-white p-4">
                    <div class="flex flex-wrap justify-between gap-2">
                        <p><strong>{{ $withdrawal->seller->business_name }}</strong> · {{ $withdrawal->payoutAccount?->bank_name }} {{ $withdrawal->payoutAccount?->maskedAccountNumber() }}</p>
                        <span class="font-semibold">{{ str_replace('_', ' ', $withdrawal->status) }}</span>
                    </div>
                    <p class="mt-1 text-sm text-slate-600">Rp{{ number_format((float) $withdrawal->amount, 0, ',', '.') }} · Referensi {{ $withdrawal->reference_id }}@if($withdrawal->xendit_payout_id) · Xendit {{ $withdrawal->xendit_payout_id }}@endif</p>
                    @if ($withdrawal->failure_code)<p class="mt-1 text-sm text-rose-700">Kode kendala: {{ $withdrawal->failure_code }}</p>@endif
                </article>
            @empty
                <p class="rounded-lg border border-slate-200 bg-white p-4 text-slate-600">Tidak ada pencairan yang memerlukan pemantauan saat ini.</p>
            @endforelse
            {{ $withdrawals->links() }}
        </section>
    </main>
</x-layouts.app>
