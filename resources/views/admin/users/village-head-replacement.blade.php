<x-layouts.app title="Pergantian Kepala Desa">
    <main id="main" class="mx-auto max-w-3xl px-4 py-8 sm:px-8">
        <x-ui.breadcrumbs :items="[['label' => 'Akun dan peran', 'url' => route('admin.users.manage')], ['label' => 'Pergantian Kepala Desa']]" />
        <x-ui.page-heading class="mt-5" title="Pergantian Kepala Desa" description="Alihkan peran secara tercatat ketika masa jabatan berganti. Akun lama dan riwayatnya tetap disimpan.">
            <x-slot:actions><a class="inline-flex min-h-11 items-center underline underline-offset-4" href="{{ route('admin.users.manage') }}">Kembali ke akun dan peran</a></x-slot:actions>
        </x-ui.page-heading>

        @if (session('status'))<p class="mt-5 border-l-4 border-emerald-700 bg-emerald-50 p-4 text-sm text-emerald-950" role="status">{{ session('status') }}</p>@endif

        <section class="mt-6 rounded-card border border-line bg-paper p-5 sm:p-6">
            <h2 class="text-lg font-semibold">Pejabat saat ini</h2>
            @if ($currentVillageHead)
                <p class="mt-2 text-sm leading-6"><span class="font-semibold">{{ $currentVillageHead->username }}</span> · {{ match($currentVillageHead->account_status) { 'active' => 'Aktif', 'suspended' => 'Ditangguhkan', 'disabled' => 'Dinonaktifkan', default => 'Status tidak diketahui' } }}</p>
            @else
                <p class="mt-2 text-sm leading-6 text-muted">Belum ada akun yang memegang peran Village Head. Untuk penetapan pertama, pilih peran tersebut di halaman Akun dan peran.</p>
            @endif
        </section>

        @if ($currentVillageHead)
            <section class="mt-5 rounded-card border border-amber-700/40 bg-amber-50 p-5 sm:p-6" aria-labelledby="replacement-form-title">
                <h2 id="replacement-form-title" class="text-lg font-semibold">Tetapkan pejabat pengganti</h2>
                <p class="mt-2 text-sm leading-6">Peran Village Head akan dicabut dari akun saat ini dan diberikan kepada akun tujuan dalam satu transaksi. Peran lain pada kedua akun tidak berubah. Kata sandi Super Admin dan alasan pergantian diperlukan; keduanya dicatat sesuai kebijakan audit.</p>

                @if ($villageHeadCandidates->isEmpty())
                    <p class="mt-4 border-l-4 border-amber-800 bg-white/70 p-3 text-sm text-amber-950">Belum ada calon yang memenuhi syarat. Calon harus memiliki akun aktif dan email terverifikasi, serta tidak memegang peran Super Admin atau Village Head.</p>
                @else
                    <form method="POST" action="{{ route('admin.users.village-head.replace') }}" class="mt-5 space-y-4" data-unsaved-form data-confirm="Alihkan peran Kepala Desa kepada akun yang dipilih? Tindakan ini akan dicatat di log audit.">
                        @csrf
                        <div>
                            <label for="target_user_id" class="block text-sm font-semibold">Akun pengganti</label>
                            <select id="target_user_id" name="target_user_id" required aria-invalid="{{ $errors->has('target_user_id') ? 'true' : 'false' }}" class="mt-2 min-h-11 w-full border border-slate-400 bg-white px-3 text-base text-ink focus-visible:outline-2 focus-visible:outline-offset-2">
                                <option value="">Pilih akun aktif dan terverifikasi…</option>
                                @foreach ($villageHeadCandidates as $candidate)<option value="{{ $candidate->id }}" @selected((string) old('target_user_id') === (string) $candidate->id)>{{ $candidate->username }}</option>@endforeach
                            </select>
                            @error('target_user_id')<p class="mt-1 text-sm text-red-800" role="alert">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="head-transfer-password" class="block text-sm font-semibold">Konfirmasi kata sandi Super Admin</label>
                            <input id="head-transfer-password" type="password" name="current_password" required autocomplete="current-password" aria-invalid="{{ $errors->has('current_password') ? 'true' : 'false' }}" class="mt-2 min-h-11 w-full border border-slate-400 bg-white px-3 text-base text-ink focus-visible:outline-2 focus-visible:outline-offset-2">
                            @error('current_password')<p class="mt-1 text-sm text-red-800" role="alert">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="head-transfer-reason" class="block text-sm font-semibold">Alasan pergantian</label>
                            <textarea id="head-transfer-reason" name="reason" required minlength="10" maxlength="1000" rows="4" aria-invalid="{{ $errors->has('reason') ? 'true' : 'false' }}" class="mt-2 min-h-28 w-full border border-slate-400 bg-white px-3 py-2 text-base text-ink focus-visible:outline-2 focus-visible:outline-offset-2">{{ old('reason') }}</textarea>
                            @error('reason')<p class="mt-1 text-sm text-red-800" role="alert">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="min-h-11 border border-amber-900 bg-amber-900 px-4 font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2">Alihkan peran Kepala Desa</button>
                    </form>
                @endif
            </section>
        @endif
    </main>
</x-layouts.app>
