<x-layouts.app title="Buat permohonan surat">
    <main id="main" class="mx-auto max-w-3xl px-4 py-8 sm:px-8">
        <x-ui.breadcrumbs :items="[['label' => 'Persuratan', 'url' => route('correspondence.index')], ['label' => 'Buat permohonan']]" />
        <x-ui.page-heading class="mt-5" title="Buat permohonan surat" description="Lengkapi informasi sesuai jenis surat. Isian disimpan secara terenkripsi.">
            <x-slot:actions><a href="{{ route('correspondence.index') }}" class="inline-flex min-h-11 items-center text-sm font-medium underline underline-offset-4">Kembali ke persuratan</a></x-slot:actions>
        </x-ui.page-heading>

        @if ($types->isEmpty())
            <div class="mt-6"><x-ui.empty-state title="Belum ada jenis surat aktif" description="Hubungi administrasi desa agar jenis surat yang diperlukan tersedia." /></div>
        @elseif (! $selectedType)
            <form method="GET" action="{{ route('correspondence.create') }}" class="mt-6 rounded-card border border-line bg-paper p-5">
                @if ($assisted)<input type="hidden" name="assistance_session_id" value="{{ $assistanceSession->id }}">@endif
                <label for="type_id" class="block text-sm font-medium">Jenis surat</label>
                <select id="type_id" name="type_id" required class="mt-2 min-h-11 w-full rounded-control border border-slate-400 bg-paper px-3 text-ink focus-visible:outline-offset-2">
                    <option value="">Pilih jenis surat</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                    @endforeach
                </select>
                <x-ui.button type="submit" class="mt-4">Lanjut</x-ui.button>
            </form>
        @else
            <section class="mt-6 rounded-card border border-line bg-paper p-5">
                <h2 class="text-lg font-semibold">{{ $selectedType->name }}</h2>
                @if ($selectedType->description)<p class="mt-2 text-sm leading-6 text-muted">{{ $selectedType->description }}</p>@endif
                <p class="mt-3 text-sm text-muted">Tanda * menunjukkan isian wajib. {{ $assisted ? 'Warga pemilik permintaan akan meninjau dan mengonfirmasi draf dari akunnya.' : 'Periksa informasi sebelum mengirim permohonan.' }}</p>
            </section>

            @if ($assisted)
                <div class="mt-5 rounded-card border border-line bg-paper p-5 text-sm leading-6">
                    <h2 class="font-semibold">Permintaan bantuan diterima</h2>
                    <p class="mt-1 text-muted">Permintaan {{ $assistanceSession->session_reference }} · warga <strong>{{ $assistedResidentUsername }}</strong>. Isian profil yang tersedia sudah dimuat; pastikan warga memeriksa draf sebelum mengirimkannya.</p>
                </div>
            @elseif (count($profilePrefill ?? []))
                <div class="mt-5 rounded-card border border-emerald-800/30 bg-emerald-50 p-4 text-sm leading-6" role="status">
                    Beberapa isian sudah diambil dari profil Anda. Silakan periksa dan ubah jika perlu. Data NIK, bila diminta, tetap harus diisi sendiri.
                </div>
            @endif

            <form method="POST" action="{{ route('correspondence.store') }}" enctype="multipart/form-data" data-unsaved-form class="mt-5 space-y-5">
                @csrf
                <input type="hidden" name="letter_type_id" value="{{ $selectedType->id }}">
                @if ($assisted)<input type="hidden" name="assistance_session_id" value="{{ $assistanceSession->id }}">@endif
                @if ($assisted)
                    <div class="rounded-card border border-line bg-paper p-5">
                        <p class="text-sm font-medium">Warga pemilik permohonan</p>
                        <p class="mt-1">{{ $assistedResidentUsername }}</p>
                        @error('assistance_session_id')<p class="mt-1 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                    </div>
                @endif

                <fieldset class="space-y-4 rounded-card border border-line bg-paper p-5">
                    <legend class="px-2 text-base font-semibold">Informasi permohonan</legend>
                    @foreach ($selectedType->required_fields ?? [] as $field)
                        @php($fieldId = 'field-'.$field['key'])
                        @php($fieldError = $errors->has('fields.'.$field['key']))
                        @php($isNikField = in_array(strtolower($field['key']), ['nik', 'national_id'], true) || preg_match('/\bnik\b|nomor induk kependudukan/i', $field['label']) === 1)
                        <div>
                            <label for="{{ $fieldId }}" class="block text-sm font-medium">{{ $field['label'] }} @if ($field['required'] ?? true)<span aria-hidden="true">*</span><span class="sr-only"> wajib diisi</span>@endif</label>
                            @if ($field['type'] === 'textarea')
                                <textarea id="{{ $fieldId }}" name="fields[{{ $field['key'] }}]" rows="4" @required($field['required'] ?? true) aria-invalid="{{ $fieldError ? 'true' : 'false' }}" class="mt-1 min-h-28 w-full rounded-control border {{ $fieldError ? 'border-danger' : 'border-slate-400' }} bg-paper px-3 py-2 text-ink focus-visible:outline-offset-2">{{ old('fields.'.$field['key'], $profilePrefill[$field['key']] ?? '') }}</textarea>
                            @elseif ($field['type'] === 'select')
                                <select id="{{ $fieldId }}" name="fields[{{ $field['key'] }}]" @required($field['required'] ?? true) aria-invalid="{{ $fieldError ? 'true' : 'false' }}" class="mt-1 min-h-11 w-full rounded-control border {{ $fieldError ? 'border-danger' : 'border-slate-400' }} bg-paper px-3 text-ink focus-visible:outline-offset-2">
                                    <option value="">Pilih {{ strtolower($field['label']) }}</option>
                                    @foreach ($field['options'] ?? [] as $option)<option value="{{ $option }}" @selected(old('fields.'.$field['key'], $profilePrefill[$field['key']] ?? '') === $option)>{{ $option }}</option>@endforeach
                                </select>
                            @else
                                <input id="{{ $fieldId }}" type="{{ $isNikField ? 'text' : match ($field['type']) { 'date' => 'date', 'integer' => 'number', default => 'text' } }}" name="fields[{{ $field['key'] }}]" value="{{ old('fields.'.$field['key'], $profilePrefill[$field['key']] ?? '') }}" @required($field['required'] ?? true) @if ($isNikField) inputmode="numeric" autocomplete="off" maxlength="16" pattern="[0-9]{16}" aria-describedby="{{ $fieldId }}-help" @elseif ($field['type'] === 'text') maxlength="{{ min((int) ($field['max'] ?? 255), 2000) }}" @endif aria-invalid="{{ $fieldError ? 'true' : 'false' }}" class="mt-1 min-h-11 w-full rounded-control border {{ $fieldError ? 'border-danger' : 'border-slate-400' }} bg-paper px-3 text-ink focus-visible:outline-offset-2">
                                @if ($isNikField)<p id="{{ $fieldId }}-help" class="mt-1 text-sm leading-6 text-muted">Masukkan 16 digit NIK. Nilai ini disimpan terenkripsi selama proses surat dan dihapus dari data permohonan setelah surat final diterbitkan.</p>@endif
                            @endif
                            @error('fields.'.$field['key'])<p class="mt-1 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </fieldset>

                @if (count($selectedType->required_attachments ?? []))
                    <fieldset class="space-y-4 rounded-card border border-line bg-paper p-5">
                        <legend class="px-2 text-base font-semibold">Dokumen pendukung</legend>
                        <p class="text-sm leading-6 text-muted">Format yang diterima: PDF, JPG, JPEG, PNG. Ukuran maksimum setiap file mengikuti ketentuan jenis surat.</p>
                        @foreach ($selectedType->required_attachments as $attachment)
                            <div>
                                <label for="attachment-{{ $attachment['key'] }}" class="block text-sm font-medium">{{ $attachment['label'] }} @if ($attachment['required'] ?? true)<span aria-hidden="true">*</span><span class="sr-only"> wajib diunggah</span>@endif</label>
                                <input id="attachment-{{ $attachment['key'] }}" type="file" name="attachments[{{ $attachment['key'] }}]" accept=".pdf,.jpg,.jpeg,.png" @required($attachment['required'] ?? true) aria-invalid="{{ $errors->has('attachments.'.$attachment['key']) ? 'true' : 'false' }}" class="mt-1 min-h-11 w-full rounded-control border border-slate-400 bg-paper p-2 text-sm focus-visible:outline-offset-2">
                                @error('attachments.'.$attachment['key'])<p class="mt-1 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                            </div>
                        @endforeach
                    </fieldset>
                @endif

                @if (! $assisted)
                    <label class="flex min-h-12 items-start gap-3 rounded-card border border-line bg-paper p-4 text-sm leading-6"><input type="checkbox" name="resident_confirmation" value="1" required class="mt-1 size-4 accent-emerald-700"><span>Saya telah memeriksa informasi dan menyatakan permohonan ini benar. Permohonan akan dikirim ke RT untuk diperiksa.</span></label>
                @endif
                <x-ui.button type="submit" data-submit-button>{{ $assisted ? 'Simpan draf untuk ditinjau warga' : 'Kirim permohonan' }}</x-ui.button>
            </form>
        @endif
    </main>
</x-layouts.app>
