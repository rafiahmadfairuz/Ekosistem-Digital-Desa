<x-layouts.app title="Jenis surat">
    <main id="main" class="mx-auto max-w-6xl space-y-8 px-4 py-7 sm:px-8 sm:py-9">
        <!-- Jejak Navigasi & Header Halaman -->
        <div class="space-y-4">
            <nav aria-label="Jejak navigasi" class="flex flex-wrap items-center gap-2 text-xs font-medium text-slate-500">
                <a href="{{ route('correspondence.index') }}" class="transition hover:text-emerald-700">Persuratan</a>
                <span class="text-slate-300" aria-hidden="true">/</span>
                <span class="font-semibold text-slate-700" aria-current="page">Jenis surat</span>
            </nav>

            <header class="flex flex-col gap-4 border-b border-slate-200/80 pb-6 sm:flex-row sm:items-end sm:justify-between">
                <div class="max-w-3xl">
                    <p class="inline-flex items-center gap-2 rounded-full border border-emerald-200/80 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                        <span class="size-1.5 rounded-full bg-emerald-600" aria-hidden="true"></span>
                        <span>Administrasi Persuratan Desa</span>
                    </p>
                    <h1 class="mt-2.5 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Jenis surat</h1>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">
                        Atur informasi yang perlu diisi warga dan dokumen yang perlu disiapkan. Tidak perlu menulis kode atau JSON.
                    </p>
                </div>

                <div class="shrink-0">
                    <a href="{{ route('correspondence.index') }}" class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-slate-300 bg-white px-4 text-sm font-semibold text-slate-700 shadow-2xs transition hover:border-emerald-600 hover:bg-emerald-50 hover:text-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                        <span aria-hidden="true">←</span>
                        <span>Kembali</span>
                    </a>
                </div>
            </header>
        </div>

        <!-- Notifikasi Status & Validasi -->
        @if(session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50/90 px-4 py-3.5 text-sm font-medium text-emerald-900 shadow-2xs" role="status">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl border border-red-200 bg-red-50/90 p-4 text-sm text-red-900 shadow-2xs" role="alert" tabindex="-1">
                <p class="font-semibold text-red-800">Periksa kembali pengaturan surat.</p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-red-700">
                    @foreach($errors->all() as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Kartu Formulir Tambah Jenis Surat -->
        <section class="rounded-2xl border border-slate-200/90 bg-white p-5 shadow-xs sm:p-7" aria-labelledby="new-type-heading">
            <div class="flex items-start gap-3.5 border-b border-slate-100 pb-5">
                <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 text-base font-bold text-emerald-700" aria-hidden="true">+</span>
                <div>
                    <h2 id="new-type-heading" class="text-lg font-bold text-slate-900">Tambah jenis surat</h2>
                    <p class="mt-1 text-sm leading-relaxed text-slate-500">
                        Isi nama dan keterangan surat, lalu tambahkan pertanyaan serta dokumen yang diperlukan. Isian yang baru dibuat belum dapat dipilih warga sampai diaktifkan.
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ route('correspondence.types.store') }}" class="mt-6 grid gap-5 sm:grid-cols-2" data-unsaved-form>
                @csrf
                <div>
                    <label for="new-code" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Kode surat untuk administrasi</label>
                    <input id="new-code" name="code" required maxlength="40" pattern="[A-Z0-9_-]+" value="{{ old('code') }}" placeholder="Contoh: SURAT_DOMISILI" aria-describedby="new-code-help" class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 font-mono text-sm text-slate-900 placeholder:font-sans placeholder:text-slate-400 shadow-2xs transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15">
                    <p id="new-code-help" class="mt-1.5 text-xs text-slate-500">Kode ini membantu petugas membedakan jenis surat.</p>
                    @error('code')
                        <p class="mt-1 text-xs font-medium text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="new-name" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Nama jenis surat</label>
                    <input id="new-name" name="name" required maxlength="150" value="{{ old('name') }}" placeholder="Contoh: Surat Keterangan Domisili" class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-900 placeholder:text-slate-400 shadow-2xs transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15">
                </div>

                <div class="sm:col-span-2">
                    <label for="new-description" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Keterangan</label>
                    <textarea id="new-description" name="description" rows="2" maxlength="4000" placeholder="Jelaskan kegunaan atau syarat umum pengajuan jenis surat ini…" class="mt-2 min-h-20 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm leading-relaxed text-slate-900 placeholder:text-slate-400 shadow-2xs transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15">{{ old('description') }}</textarea>
                </div>

                <section class="space-y-3 rounded-xl border border-slate-200/80 bg-slate-50/60 p-4 sm:col-span-2 sm:p-5" aria-labelledby="new-fields-heading">
                    <div>
                        <h3 id="new-fields-heading" class="text-sm font-bold text-slate-800">Informasi yang perlu diisi warga</h3>
                        <p class="mt-1 text-xs text-slate-500">Contoh: keperluan surat, tanggal kegiatan, atau pilihan jenis usaha.</p>
                    </div>
                    <x-correspondence.config-builder kind="fields" id-prefix="new" :items="old('required_fields', [])" />
                </section>

                <section class="space-y-3 rounded-xl border border-slate-200/80 bg-slate-50/60 p-4 sm:col-span-2 sm:p-5" aria-labelledby="new-attachments-heading">
                    <div>
                        <h3 id="new-attachments-heading" class="text-sm font-bold text-slate-800">Dokumen yang perlu disiapkan warga</h3>
                        <p class="mt-1 text-xs text-slate-500">Warga dapat mengunggah PDF, JPG, JPEG, atau PNG.</p>
                    </div>
                    <x-correspondence.config-builder kind="attachments" id-prefix="new" :items="old('required_attachments', [])" />
                </section>

                <div class="sm:col-span-2">
                    <label class="inline-flex min-h-11 cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-slate-50/80 px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50/50">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active')) class="size-4 rounded border-slate-300 accent-emerald-600">
                        <span>Langsung aktif dan dapat dipilih warga</span>
                    </label>
                </div>

                <div class="flex items-center justify-end border-t border-slate-100 pt-4 sm:col-span-2">
                    <button type="submit" data-submit-button class="inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-600 px-6 text-sm font-semibold text-white shadow-xs transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                        Simpan jenis surat
                    </button>
                </div>
            </form>
        </section>

        <!-- Daftar Jenis Surat yang Tersedia -->
        <section class="space-y-4" aria-labelledby="types-list-heading">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 id="types-list-heading" class="text-lg font-bold tracking-tight text-slate-900">Jenis surat yang tersedia</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Klik salah satu jenis surat di bawah untuk mengubah rincian isian, lampiran, atau status aktifnya.</p>
                </div>
            </div>

            <div class="space-y-3.5">
                @forelse ($types as $type)
                    <details class="group overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-xs transition hover:border-slate-300" @if((string) old('_letter_type_id') === (string) $type->id) open @endif>
                        <summary class="flex min-h-16 cursor-pointer list-none items-center justify-between gap-4 p-4 sm:px-6 [&::-webkit-details-marker]:hidden">
                            <div class="flex min-w-0 items-center gap-3.5">
                                <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 text-xs font-bold uppercase text-emerald-700" aria-hidden="true">
                                    {{ mb_substr($type->name, 0, 2) }}
                                </span>
                                <div class="min-w-0">
                                    <span class="block break-words text-sm font-bold text-slate-800 group-hover:text-emerald-700 sm:text-base">{{ $type->name }}</span>
                                    <span class="mt-0.5 inline-block rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 font-mono text-[11px] font-semibold text-slate-600">{{ $type->code }}</span>
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-3">
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold {{ $type->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-100 text-slate-600' }}">
                                    <span class="size-1.5 rounded-full {{ $type->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}" aria-hidden="true"></span>
                                    <span>{{ $type->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </span>
                                <span class="inline-flex size-8 items-center justify-center rounded-lg border border-slate-200 bg-slate-50 text-slate-500 transition-transform duration-200 group-open:rotate-180">
                                    <svg class="size-4" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m5 7 5 5 5-5" /></svg>
                                </span>
                            </div>
                        </summary>

                        <form method="POST" action="{{ route('correspondence.types.update', $type) }}" class="grid gap-5 border-t border-slate-100 bg-slate-50/40 p-5 sm:grid-cols-2 sm:p-6" data-unsaved-form>
                            @csrf @method('PUT')
                            <input type="hidden" name="_letter_type_id" value="{{ $type->id }}">
                            @php($editingThisType = (string) old('_letter_type_id') === (string) $type->id)

                            <div>
                                <label for="code-{{ $type->id }}" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Kode internal</label>
                                <input id="code-{{ $type->id }}" name="code" required maxlength="40" value="{{ $editingThisType ? old('code', $type->code) : $type->code }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 font-mono text-sm text-slate-900 shadow-2xs transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15">
                            </div>

                            <div>
                                <label for="name-{{ $type->id }}" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Nama jenis surat</label>
                                <input id="name-{{ $type->id }}" name="name" required maxlength="150" value="{{ $editingThisType ? old('name', $type->name) : $type->name }}" class="mt-2 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3.5 text-sm text-slate-900 shadow-2xs transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15">
                            </div>

                            <div class="sm:col-span-2">
                                <label for="description-{{ $type->id }}" class="block text-xs font-bold uppercase tracking-wider text-slate-600">Keterangan</label>
                                <textarea id="description-{{ $type->id }}" name="description" rows="2" maxlength="4000" class="mt-2 min-h-20 w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm leading-relaxed text-slate-900 shadow-2xs transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15">{{ $editingThisType ? old('description', $type->description) : $type->description }}</textarea>
                            </div>

                            <section class="space-y-3 rounded-xl border border-slate-200/80 bg-white p-4 sm:col-span-2 sm:p-5" aria-labelledby="fields-heading-{{ $type->id }}">
                                <div>
                                    <h3 id="fields-heading-{{ $type->id }}" class="text-sm font-bold text-slate-800">Informasi yang perlu diisi warga</h3>
                                    <p class="mt-1 text-xs text-slate-500">Tambahkan pertanyaan dan pilih cara warga menjawab.</p>
                                </div>
                                <x-correspondence.config-builder kind="fields" id-prefix="edit-{{ $type->id }}" :items="$editingThisType ? old('required_fields', $type->required_fields ?? []) : ($type->required_fields ?? [])" />
                            </section>

                            <section class="space-y-3 rounded-xl border border-slate-200/80 bg-white p-4 sm:col-span-2 sm:p-5" aria-labelledby="attachments-heading-{{ $type->id }}">
                                <div>
                                    <h3 id="attachments-heading-{{ $type->id }}" class="text-sm font-bold text-slate-800">Dokumen yang perlu disiapkan warga</h3>
                                    <p class="mt-1 text-xs text-slate-500">Unggahan yang diterima: PDF, JPG, JPEG, PNG. Ukuran maksimal 10 MB per file.</p>
                                </div>
                                <x-correspondence.config-builder kind="attachments" id-prefix="edit-{{ $type->id }}" :items="$editingThisType ? old('required_attachments', $type->required_attachments ?? []) : ($type->required_attachments ?? [])" />
                            </section>

                            <div class="sm:col-span-2">
                                <label class="inline-flex min-h-11 cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50/50">
                                    <input type="checkbox" name="is_active" value="1" @checked($editingThisType ? old('is_active', $type->is_active) : $type->is_active) class="size-4 rounded border-slate-300 accent-emerald-600">
                                    <span>Aktif dan dapat diajukan warga</span>
                                </label>
                            </div>

                            <div class="flex items-center justify-end border-t border-slate-200/70 pt-4 sm:col-span-2">
                                <button type="submit" data-submit-button class="inline-flex min-h-11 items-center justify-center rounded-xl bg-emerald-600 px-6 text-sm font-semibold text-white shadow-xs transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                                    Simpan perubahan
                                </button>
                            </div>
                        </form>
                    </details>
                @empty
                    <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-xs">
                        <x-ui.empty-state title="Belum ada jenis surat" description="Tambahkan jenis surat sesuai prosedur resmi desa." />
                    </div>
                @endforelse
            </div>

            <div class="mt-5">{{ $types->links() }}</div>
        </section>
    </main>
</x-layouts.app>
