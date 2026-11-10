<x-layouts.app title="Akun Xendit penjual">
    <main id="main" class="mx-auto max-w-5xl space-y-6 px-4 py-8 sm:px-8">
        <x-ui.breadcrumbs :items="[['label' => 'Akun dan akses', 'url' => route('admin.users.manage')], ['label' => 'Akun Xendit penjual']]" />
        <x-ui.page-heading title="Akun Xendit penjual" description="Hubungkan penjual yang disetujui ke Business ID sub-account XenPlatform. Hanya Super Admin dapat mengubah tujuan pencairan." />
        <x-ui.alert type="warning">
            Masukkan Business ID dari sub-account XenPlatform yang sudah diverifikasi dan terhubung ke platform ini. ID ini bukan API key. Jangan masukkan secret key. Pembayaran tetap gagal tertutup jika XenPlatform, izin API, atau sub-account belum aktif.
        </x-ui.alert>
        <section class="space-y-3" aria-label="Pemetaan sub-account penjual">
            @forelse($sellers as $seller)
                <article class="grid gap-4 rounded-card border border-line bg-paper p-5 lg:grid-cols-[minmax(12rem,1fr)_minmax(16rem,1.2fr)] lg:items-center">
                    <div class="min-w-0">
                        <h2 class="break-words font-semibold">{{ $seller->business_name }}</h2>
                        <p class="mt-1 text-sm text-muted">{{ $seller->user->username }} · Disetujui</p>
                        <p class="mt-1 text-sm {{ $seller->xendit_account_id ? 'text-success-ink' : 'text-warning-ink' }}">{{ $seller->xendit_account_id ? 'Sub-account terhubung' : 'Belum terhubung' }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.marketplace.xendit-seller-accounts.update', $seller) }}" class="flex flex-wrap items-end gap-3">
                        @csrf @method('PUT')
                        <label class="min-w-0 flex-1 text-sm font-medium" for="xendit-account-{{ $seller->id }}">Business ID sub-account
                            <input id="xendit-account-{{ $seller->id }}" name="xendit_account_id" value="{{ old('xendit_account_id', $seller->xendit_account_id) }}" maxlength="80" pattern="[A-Za-z0-9_-]+" autocomplete="off" spellcheck="false" class="mt-1 min-h-11 w-full rounded-control border border-line bg-paper px-3" placeholder="Contoh: sub-account Business ID…">
                        </label>
                        <x-ui.button type="submit" variant="secondary">Simpan tujuan</x-ui.button>
                    </form>
                </article>
            @empty
                <x-ui.empty-state title="Belum ada penjual disetujui" description="Penjual akan muncul di sini setelah status pengajuannya disetujui." />
            @endforelse
        </section>
        {{ $sellers->links() }}
    </main>
</x-layouts.app>
