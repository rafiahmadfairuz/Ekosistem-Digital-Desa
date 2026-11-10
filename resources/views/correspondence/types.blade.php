<x-layouts.app title="Jenis surat">
    <main id="main" class="mx-auto max-w-5xl px-4 py-8 sm:px-8">
        <x-ui.breadcrumbs :items="[['label' => 'Persuratan', 'url' => route('correspondence.index')], ['label' => 'Jenis surat']]" />
        <x-ui.page-heading class="mt-5" title="Jenis surat" description="Atur informasi yang perlu diisi warga dan dokumen yang perlu disiapkan. Tidak perlu menulis kode atau JSON.">
            <x-slot:actions><a href="{{ route('correspondence.index') }}" class="inline-flex min-h-11 items-center underline underline-offset-4">Kembali</a></x-slot:actions>
        </x-ui.page-heading>

        @if(session('status'))<p class="mt-5 border-l-4 border-emerald-700 bg-emerald-50 p-4 text-sm text-emerald-950" role="status">{{ session('status') }}</p>@endif
        @if($errors->any())<div class="mt-5 border-l-4 border-red-700 bg-red-50 p-4 text-sm text-red-900" role="alert" tabindex="-1"><p class="font-semibold">Periksa kembali pengaturan surat.</p><ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $message)<li>{{ $message }}</li>@endforeach</ul></div>@endif

        <section class="mt-6 rounded-card border border-line bg-paper p-5" aria-labelledby="new-type-heading">
            <h2 id="new-type-heading" class="text-base font-semibold">Tambah jenis surat</h2>
            <p class="mt-2 text-sm leading-6 text-muted">Isi nama dan keterangan surat, lalu tambahkan pertanyaan serta dokumen yang diperlukan. Isian yang baru dibuat belum dapat dipilih warga sampai diaktifkan.</p>
            <form method="POST" action="{{ route('correspondence.types.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2" data-unsaved-form>
                @csrf
                <div><label for="new-code" class="block text-sm font-medium">Kode surat untuk administrasi</label><input id="new-code" name="code" required maxlength="40" pattern="[A-Z0-9_-]+" value="{{ old('code') }}" placeholder="Contoh: SURAT_DOMISILI" aria-describedby="new-code-help" class="mt-1 min-h-11 w-full rounded-control border border-slate-400 bg-paper px-3"><p id="new-code-help" class="mt-1 text-xs text-muted">Kode ini membantu petugas membedakan jenis surat.</p>@error('code')<p class="mt-1 text-sm text-danger" role="alert">{{ $message }}</p>@enderror</div>
                <div><label for="new-name" class="block text-sm font-medium">Nama jenis surat</label><input id="new-name" name="name" required maxlength="150" value="{{ old('name') }}" class="mt-1 min-h-11 w-full rounded-control border border-slate-400 bg-paper px-3"></div>
                <div class="sm:col-span-2"><label for="new-description" class="block text-sm font-medium">Keterangan</label><textarea id="new-description" name="description" rows="2" maxlength="4000" class="mt-1 min-h-20 w-full rounded-control border border-slate-400 bg-paper px-3 py-2">{{ old('description') }}</textarea></div>
                <section class="space-y-3 border-t border-line pt-4 sm:col-span-2" aria-labelledby="new-fields-heading"><div><h3 id="new-fields-heading" class="font-semibold">Informasi yang perlu diisi warga</h3><p class="mt-1 text-sm text-muted">Contoh: keperluan surat, tanggal kegiatan, atau pilihan jenis usaha.</p></div><x-correspondence.config-builder kind="fields" id-prefix="new" :items="old('required_fields', [])" /></section>
                <section class="space-y-3 border-t border-line pt-4 sm:col-span-2" aria-labelledby="new-attachments-heading"><div><h3 id="new-attachments-heading" class="font-semibold">Dokumen yang perlu disiapkan warga</h3><p class="mt-1 text-sm text-muted">Warga dapat mengunggah PDF, JPG, JPEG, atau PNG.</p></div><x-correspondence.config-builder kind="attachments" id-prefix="new" :items="old('required_attachments', [])" /></section>
                <label class="flex min-h-11 items-center gap-3 text-sm sm:col-span-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active')) class="size-4 accent-emerald-700">Langsung aktif dan dapat dipilih warga</label>
                <div class="sm:col-span-2"><x-ui.button type="submit" data-submit-button>Simpan jenis surat</x-ui.button></div>
            </form>
        </section>

        <section class="mt-7" aria-labelledby="types-list-heading">
            <h2 id="types-list-heading" class="text-base font-semibold">Jenis surat yang tersedia</h2>
            <div class="mt-3 space-y-4">
                @forelse ($types as $type)
                    <details class="rounded-card border border-line bg-paper p-4" @if((string) old('_letter_type_id') === (string) $type->id) open @endif>
                        <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-3 [&::-webkit-details-marker]:hidden">
                            <span class="min-w-0"><span class="block break-words font-medium">{{ $type->name }}</span><span class="mt-1 block font-mono text-xs text-muted">{{ $type->code }}</span></span>
                            <x-ui.status-badge :status="$type->is_active ? 'Aktif' : 'Nonaktif'" :tone="$type->is_active ? 'success' : 'neutral'" />
                        </summary>
                        <form method="POST" action="{{ route('correspondence.types.update', $type) }}" class="mt-4 grid gap-4 border-t border-line pt-4 sm:grid-cols-2" data-unsaved-form>
                            @csrf @method('PUT')
                            <input type="hidden" name="_letter_type_id" value="{{ $type->id }}">
                            @php($editingThisType = (string) old('_letter_type_id') === (string) $type->id)
                            <div><label for="code-{{ $type->id }}" class="block text-sm font-medium">Kode internal</label><input id="code-{{ $type->id }}" name="code" required maxlength="40" value="{{ $editingThisType ? old('code', $type->code) : $type->code }}" class="mt-1 min-h-11 w-full rounded-control border border-slate-400 bg-paper px-3"></div>
                            <div><label for="name-{{ $type->id }}" class="block text-sm font-medium">Nama jenis surat</label><input id="name-{{ $type->id }}" name="name" required maxlength="150" value="{{ $editingThisType ? old('name', $type->name) : $type->name }}" class="mt-1 min-h-11 w-full rounded-control border border-slate-400 bg-paper px-3"></div>
                            <div class="sm:col-span-2"><label for="description-{{ $type->id }}" class="block text-sm font-medium">Keterangan</label><textarea id="description-{{ $type->id }}" name="description" rows="2" maxlength="4000" class="mt-1 min-h-20 w-full rounded-control border border-slate-400 bg-paper px-3 py-2">{{ $editingThisType ? old('description', $type->description) : $type->description }}</textarea></div>
                            <section class="space-y-3 border-t border-line pt-4 sm:col-span-2" aria-labelledby="fields-heading-{{ $type->id }}"><div><h3 id="fields-heading-{{ $type->id }}" class="font-semibold">Informasi yang perlu diisi warga</h3><p class="mt-1 text-sm text-muted">Tambahkan pertanyaan dan pilih cara warga menjawab.</p></div><x-correspondence.config-builder kind="fields" id-prefix="edit-{{ $type->id }}" :items="$editingThisType ? old('required_fields', $type->required_fields ?? []) : ($type->required_fields ?? [])" /></section>
                            <section class="space-y-3 border-t border-line pt-4 sm:col-span-2" aria-labelledby="attachments-heading-{{ $type->id }}"><div><h3 id="attachments-heading-{{ $type->id }}" class="font-semibold">Dokumen yang perlu disiapkan warga</h3><p class="mt-1 text-sm text-muted">Unggahan yang diterima: PDF, JPG, JPEG, PNG. Ukuran maksimal 10 MB per file.</p></div><x-correspondence.config-builder kind="attachments" id-prefix="edit-{{ $type->id }}" :items="$editingThisType ? old('required_attachments', $type->required_attachments ?? []) : ($type->required_attachments ?? [])" /></section>
                            <label class="flex min-h-11 items-center gap-3 text-sm sm:col-span-2"><input type="checkbox" name="is_active" value="1" @checked($editingThisType ? old('is_active', $type->is_active) : $type->is_active) class="size-4 accent-emerald-700">Aktif dan dapat diajukan warga</label>
                            <div class="sm:col-span-2"><x-ui.button type="submit" data-submit-button>Simpan perubahan</x-ui.button></div>
                        </form>
                    </details>
                @empty
                    <x-ui.empty-state title="Belum ada jenis surat" description="Tambahkan jenis surat sesuai prosedur resmi desa." />
                @endforelse
            </div>
            <div class="mt-4">{{ $types->links() }}</div>
        </section>
    </main>
</x-layouts.app>
