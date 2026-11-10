<x-layouts.app title="Peran dan akun">
<main id="main" class="mx-auto max-w-6xl px-4 py-8 sm:px-8">
    <p class="text-sm text-muted">Akses teknis · Super Admin</p><h1 class="mt-2 text-2xl font-semibold">Akun dan peran</h1><p class="mt-2 max-w-3xl text-base leading-7 text-muted">Tetapkan hanya peran yang telah disetujui. Peran Kepala Desa hanya dapat diberikan kepada akun aktif dengan email terverifikasi, dan hanya satu akun dapat memegangnya. Peran RT/RW belum memberi akses wilayah tanpa penugasan aktif.</p>
    <p class="mt-3 max-w-4xl text-sm leading-6 text-muted">Profil warga yang belum dipakai dalam permohonan surat akan ikut dihapus bersama akun. Riwayat penugasan wilayah tetap disimpan dengan referensi akun yang sudah dihapus. Akun yang terkait riwayat surat atau data staf BUMDes harus dinonaktifkan agar data layanan tetap terjaga.</p>
    <a href="{{ route('admin.users.village-head.replacement') }}" class="mt-4 inline-flex min-h-11 items-center border border-amber-800 px-4 font-semibold text-amber-950 underline underline-offset-4 hover:bg-amber-50 focus-visible:outline-2 focus-visible:outline-offset-2">Buka pergantian Kepala Desa</a>
    @if(session('status'))<p class="mt-4 border-l-4 border-emerald-700 bg-emerald-50 p-4 text-sm text-emerald-950" role="status">{{ session('status') }}</p>@endif
    @error('role_ids')<p class="mt-4 border-l-4 border-red-700 bg-red-50 p-4 text-sm text-red-900" role="alert">{{ $message }}</p>@enderror
    @error('account_delete')<p class="mt-4 border-l-4 border-red-700 bg-red-50 p-4 text-sm text-red-900" role="alert">{{ $message }}</p>@enderror
    @error('account_status')<p class="mt-4 border-l-4 border-red-700 bg-red-50 p-4 text-sm text-red-900" role="alert">{{ $message }}</p>@enderror
    <div class="mt-7 divide-y divide-slate-200 border-y border-slate-300">
    @foreach ($users as $user)
        <article class="grid gap-4 py-5 lg:grid-cols-[minmax(12rem,1fr)_minmax(18rem,1fr)_minmax(15rem,0.8fr)] lg:items-start">
            <div class="min-w-0" data-unsaved-form data-confirm="Simpan perubahan peran untuk akun ini?"><h2 class="break-all text-base font-semibold">{{ $user->username }}</h2><p class="mt-1 break-all text-sm text-muted">{{ $user->email ?? 'Email tidak tersedia' }}</p><p class="mt-2 text-sm">Status: <span class="font-medium">{{ match($user->account_status) { 'active' => 'Aktif', 'suspended' => 'Ditangguhkan', 'disabled' => 'Dinonaktifkan', 'pending' => 'Menunggu aktivasi', default => 'Tidak diketahui' } }}</span></p>@if($user->hasVerifiedEmail())<p class="text-sm text-emerald-800">Email terverifikasi</p>@else<p class="text-sm text-red-800">Email belum terverifikasi</p>@endif</div>
            @if($user->roles->contains(fn ($role) => in_array($role->name, ['Super Admin', 'Village Head'], true)))
                <div class="min-w-0"><p class="text-sm font-semibold">Peran terlindungi</p><p class="mt-2 text-sm leading-6">{{ $user->roles->pluck('name')->intersect(['Super Admin', 'Village Head'])->implode(', ') }} · perubahan peran dilakukan melalui proses pergantian khusus.</p></div>
            @else
                <form method="POST" action="{{ route('admin.users.roles.update', $user) }}" class="min-w-0">@csrf @method('PUT')<fieldset><legend class="text-sm font-semibold">Peran</legend><div class="mt-2 grid gap-x-3 gap-y-1 sm:grid-cols-2">@foreach($roles as $role)<label class="flex min-h-11 min-w-0 items-center gap-2 text-sm"><input class="size-4 shrink-0" type="checkbox" name="role_ids[]" value="{{ $role->id }}" @checked($user->roles->contains('id', $role->id))><span class="break-words">{{ $role->name }}</span></label>@endforeach</div></fieldset><p class="mt-2 text-xs leading-5 text-muted">Village Head hanya dapat diberikan jika akun aktif dan emailnya sudah diverifikasi.</p><button class="mt-3 min-h-11 bg-brand px-4 font-semibold text-ink">Simpan peran</button></form>
            @endif
            @if($user->account_status === 'pending')
                <div class="text-sm leading-6 text-muted"><p>Akun menunggu aktivasi; perubahan status tersedia setelah email diverifikasi.</p><a class="mt-1 inline-flex min-h-11 items-center font-medium text-emerald-800 underline" href="{{ route('admin.users.index') }}">Buka aktivasi akun</a></div>
            @else
                <form method="POST" action="{{ route('admin.users.status.update', $user) }}" class="flex flex-wrap items-end gap-2" data-unsaved-form data-confirm="Perbarui status akun ini?">@csrf @method('PUT')<div class="min-w-0 flex-1"><label class="block text-sm font-semibold" for="status-{{ $user->id }}">Status akun</label><select id="status-{{ $user->id }}" name="account_status" class="mt-2 min-h-11 w-full border border-slate-400 bg-white px-2 text-base text-ink"><option value="active" @selected($user->account_status==='active')>Aktif</option><option value="suspended" @selected($user->account_status==='suspended')>Ditangguhkan sementara</option><option value="disabled" @selected($user->account_status==='disabled')>Dinonaktifkan</option></select><p class="mt-2 text-xs leading-5 text-muted">Ditangguhkan: login dihentikan sementara, penugasan RT/RW dipertahankan. Dinonaktifkan: sesi dicabut dan penugasan aktif diakhiri.</p></div><button class="min-h-11 border border-slate-500 px-3 font-medium">Perbarui</button></form>
            @endif
            <div class="lg:col-span-3">
                @if((int) $user->id === (int) auth()->id())
                    <p class="text-sm text-muted">Akun yang sedang digunakan tidak dapat dihapus.</p>
                @elseif($user->roles->contains(fn ($role) => in_array($role->name, ['Super Admin', 'Village Head'], true)))
                    <p class="text-sm text-muted">Akun dengan peran Super Admin atau Village Head dilindungi dari penghapusan.</p>
                @else
                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" data-confirm="Hapus akun {{ $user->username }} secara permanen? Data yang sudah terhubung dengan profil atau riwayat layanan tidak dapat dihapus.">@csrf @method('DELETE')<button class="min-h-11 border border-red-700 px-4 font-medium text-red-800 focus-visible:outline-2">Hapus akun</button></form>
                @endif
            </div>
        </article>
    @endforeach
    </div><div class="mt-5">{{ $users->links() }}</div>
</main>
</x-layouts.app>
