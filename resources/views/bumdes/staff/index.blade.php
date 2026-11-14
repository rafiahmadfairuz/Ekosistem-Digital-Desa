<x-layouts.app title="Staf BUMDes">
    <main id="main" class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-8 sm:py-10">
        <!-- Header Halaman -->
        <header class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-3xl">
                <p class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-700">
                    <span class="size-2 rounded-full bg-emerald-600" aria-hidden="true"></span>
                    <span>Manajemen BUMDes · Pengelola Kepegawaian</span>
                </p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Profil staf BUMDes</h1>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">
                    Kelola data kepegawaian staf BUMDes. Halaman ini murni untuk keperluan administrasi internal dan tidak memberikan hak persuratan, tanda tangan, impersonasi, atau perubahan peran sistem.
                </p>
            </div>
            <div class="shrink-0">
                <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3.5 py-1.5 text-xs font-semibold text-emerald-800">
                    <span class="size-1.5 rounded-full bg-emerald-600" aria-hidden="true"></span>
                    <span>Total Staf: {{ method_exists($staff, 'total') ? $staff->total() : count($staff) }}</span>
                </span>
            </div>
        </header>

        <!-- Kartu Tambah Staf Baru -->
        <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-xs transition hover:border-slate-300" aria-labelledby="add-staff-heading">
            <details class="group p-6 sm:p-7">
                <summary id="add-staff-heading" class="flex min-h-12 cursor-pointer list-none items-center justify-between gap-4 text-base font-bold text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 [&::-webkit-details-marker]:hidden">
                    <span class="flex items-center gap-3">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-emerald-100 text-emerald-800 ring-1 ring-emerald-300/80 font-bold" aria-hidden="true">+</span>
                        <span>Tambah profil staf baru</span>
                    </span>
                    <span class="inline-flex size-9 items-center justify-center rounded-xl border border-slate-200 bg-slate-50 text-slate-500 transition-transform duration-200 group-open:rotate-180">
                        <svg class="size-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 7 5 5 5-5" /></svg>
                    </span>
                </summary>

                <form class="mt-6 grid gap-5 border-t border-slate-100 pt-6 md:grid-cols-2" data-unsaved-form method="POST" action="{{ route('bumdes.staff.store') }}">
                    @csrf
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600" for="user_id">Akun staf <span class="text-emerald-600">*</span></label>
                        <select class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-900 shadow-2xs transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15" name="user_id" id="user_id" required>
                            <option value="">Pilih akun pengguna yang sudah memiliki peran BUMDes…</option>
                            @foreach($eligibleUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->username }} · {{ $user->roles->pluck('name')->intersect(['BUMDes Manager','BUMDes Operator'])->implode(', ') }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1.5 text-xs text-slate-500">Hanya akun dengan peran BUMDes Manager atau BUMDes Operator yang dapat didaftarkan sebagai staf.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600" for="department">Bagian / Divisi</label>
                        <input class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-2xs transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15" name="department" id="department" maxlength="100" placeholder="Contoh: Operasional &amp; Logistik…">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600" for="position">Jabatan kepegawaian</label>
                        <input class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-2xs transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15" name="position" id="position" maxlength="100" placeholder="Contoh: Staf Gudang &amp; Inventaris…">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600" for="joined_at">Tanggal bergabung</label>
                        <input class="mt-2 min-h-12 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-900 shadow-2xs transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15" name="joined_at" id="joined_at" type="date">
                    </div>

                    <div class="md:col-span-2 flex items-center justify-end gap-3 pt-3">
                        <button type="submit" class="inline-flex min-h-12 items-center justify-center rounded-xl bg-emerald-600 px-7 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                            Simpan profil staf baru
                        </button>
                    </div>
                </form>
            </details>
        </section>

        <!-- Daftar Profil Staf -->
        <section class="space-y-5" aria-label="Daftar profil staf">
            @forelse($staff as $person)
                @php
                    $statusBadgeClass = match($person->employment_status) {
                        'active' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                        'suspended' => 'border-amber-200 bg-amber-50 text-amber-900',
                        default => 'border-slate-200 bg-slate-100 text-slate-700',
                    };
                    $statusDotClass = match($person->employment_status) {
                        'active' => 'bg-emerald-600',
                        'suspended' => 'bg-amber-600',
                        default => 'bg-slate-500',
                    };
                @endphp
                <article class="grid gap-6 rounded-2xl border border-slate-200/90 bg-white p-6 shadow-xs transition hover:border-slate-300 sm:p-7 lg:grid-cols-[minmax(14rem,1.1fr)_minmax(0,1.9fr)] lg:items-start">
                    <!-- Info Pengguna -->
                    <div class="flex items-start gap-4 border-b border-slate-100 pb-5 lg:border-b-0 lg:pb-0">
                        <span class="inline-flex size-12 shrink-0 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-100 text-base font-bold uppercase text-emerald-900 shadow-2xs" aria-hidden="true">
                            {{ mb_substr($person->user->username, 0, 1) }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <h2 class="break-words text-lg font-bold text-slate-900">{{ $person->user->username }}</h2>
                            <p class="mt-1 text-xs font-medium text-slate-500">Peran: <span class="text-slate-700 font-semibold">{{ $person->user->roles->pluck('name')->implode(', ') }}</span></p>
                            @if($person->joined_at)
                                <p class="mt-0.5 text-xs text-slate-500">Bergabung: <span class="text-slate-700 font-medium">{{ \Illuminate\Support\Carbon::parse($person->joined_at)->format('d M Y') }}</span></p>
                            @endif
                            <div class="mt-3">
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold {{ $statusBadgeClass }}">
                                    <span class="size-2 rounded-full {{ $statusDotClass }}" aria-hidden="true"></span>
                                    <span>Status: {{ str_replace('_', ' ', ucfirst($person->employment_status)) }}</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Formulir Update & Aksi -->
                    <div class="space-y-5 lg:border-l lg:border-slate-100 lg:pl-7">
                        <form class="space-y-4 rounded-xl border border-slate-200/80 bg-slate-50/70 p-5" data-unsaved-form method="POST" action="{{ route('bumdes.staff.update', $person) }}">
                            @csrf
                            @method('PUT')
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Pembaruan data kepegawaian</p>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600" for="department-{{ $person->id }}">Bagian</label>
                                    <input class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-900 shadow-2xs transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15" id="department-{{ $person->id }}" name="department" value="{{ $person->department }}" maxlength="100">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600" for="position-{{ $person->id }}">Jabatan</label>
                                    <input class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-900 shadow-2xs transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15" id="position-{{ $person->id }}" name="position" value="{{ $person->position }}" maxlength="100">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600" for="employment-{{ $person->id }}">Status pekerjaan</label>
                                <select class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-900 shadow-2xs transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15" name="employment_status" id="employment-{{ $person->id }}">
                                    <option value="active" @selected($person->employment_status==='active')>Aktif</option>
                                    <option value="inactive" @selected($person->employment_status==='inactive')>Tidak aktif</option>
                                    <option value="suspended" @selected($person->employment_status==='suspended')>Ditangguhkan</option>
                                </select>
                            </div>

                            <div class="flex items-center justify-end pt-2">
                                <button type="submit" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-emerald-300 bg-emerald-50 px-5 text-xs font-semibold text-emerald-800 transition hover:border-emerald-600 hover:bg-emerald-600 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                                    Simpan perubahan staf
                                </button>
                            </div>
                        </form>

                        <div class="flex items-center justify-end pt-1">
                            <form method="POST" action="{{ route('bumdes.staff.destroy', $person) }}" data-confirm="Hapus profil staf {{ $person->user->username }}? Akun dan perannya tidak akan dihapus.">
                                @csrf
                                @method('DELETE')
                                <button class="inline-flex min-h-10 items-center justify-center rounded-xl border border-red-200 bg-white px-4 text-xs font-semibold text-red-700 shadow-2xs transition hover:border-red-300 hover:bg-red-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600" type="submit">
                                    Hapus profil staf
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-xs">
                    <x-ui.empty-state title="Belum ada profil staf" description="Profil staf BUMDes yang ditambahkan akan muncul di sini." />
                </div>
            @endforelse
        </section>

        <!-- Paginasi -->
        @if(method_exists($staff, 'links'))
            <div class="mt-6">{{ $staff->links() }}</div>
        @endif
    </main>
</x-layouts.app>
