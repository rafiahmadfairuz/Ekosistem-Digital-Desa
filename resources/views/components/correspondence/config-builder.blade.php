@props([
    'kind',
    'items' => [],
    'idPrefix',
])

@php
    $isAttachment = $kind === 'attachments';
    $inputName = $isAttachment ? 'required_attachments' : 'required_fields';
    $limit = $isAttachment ? 15 : 30;
    $items = is_array($items) ? array_values($items) : [];
@endphp

<div data-config-builder="{{ $kind }}" data-next-index="{{ count($items) }}" data-limit="{{ $limit }}" class="space-y-3">
    <div data-config-list class="space-y-3">
        @foreach($items as $index => $item)
            @php
                $item = is_array($item) ? $item : [];
                $itemId = $idPrefix.'-'.$kind.'-'.$index;
                $valueLabel = $item['label'] ?? '';
                $optionsText = $item['options_text'] ?? implode("\n", $item['options'] ?? []);
                $maxMb = $item['max_mb'] ?? number_format(((int) ($item['max_kb'] ?? 5120)) / 1024, 2, '.', '');
            @endphp
            <fieldset data-config-row class="rounded-control border border-line bg-white p-4">
                <legend class="px-1 text-sm font-semibold">{{ $isAttachment ? 'Dokumen' : 'Isian' }} {{ $index + 1 }}</legend>
                <input type="hidden" name="{{ $inputName }}[{{ $index }}][key]" value="{{ $item['key'] ?? '' }}">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="{{ $itemId }}-label" class="block text-sm font-medium">{{ $isAttachment ? 'Nama dokumen' : 'Informasi yang diminta' }}</label>
                        <input id="{{ $itemId }}-label" name="{{ $inputName }}[{{ $index }}][label]" value="{{ $valueLabel }}" required maxlength="120" placeholder="{{ $isAttachment ? 'Contoh: Kartu Keluarga' : 'Contoh: Keperluan surat' }}" aria-invalid="{{ $errors->has($inputName.'.'.$index.'.label') ? 'true' : 'false' }}" class="mt-1 min-h-11 w-full rounded-control border border-slate-400 bg-paper px-3 text-base">
                        @error($inputName.'.'.$index.'.label')<p class="mt-1 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                    </div>
                    @if($isAttachment)
                        <div>
                            <label for="{{ $itemId }}-size" class="block text-sm font-medium">Batas ukuran file</label>
                            <div class="mt-1 flex items-center gap-2"><input id="{{ $itemId }}-size" type="number" name="{{ $inputName }}[{{ $index }}][max_mb]" value="{{ $maxMb }}" min="0.01" max="10" step="0.01" required inputmode="decimal" aria-invalid="{{ $errors->has($inputName.'.'.$index.'.max_kb') ? 'true' : 'false' }}" class="min-h-11 w-36 rounded-control border border-slate-400 bg-paper px-3 text-base"><span class="text-sm text-muted">MB (maksimal 10 MB)</span></div>
                            @error($inputName.'.'.$index.'.max_kb')<p class="mt-1 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                        </div>
                    @else
                        <div>
                            <label for="{{ $itemId }}-type" class="block text-sm font-medium">Cara warga mengisi</label>
                            <select id="{{ $itemId }}-type" name="{{ $inputName }}[{{ $index }}][type]" data-field-type required aria-invalid="{{ $errors->has($inputName.'.'.$index.'.type') ? 'true' : 'false' }}" class="mt-1 min-h-11 w-full rounded-control border border-slate-400 bg-paper px-3 text-base">
                                <option value="text" @selected(($item['type'] ?? 'text') === 'text')>Jawaban singkat</option>
                                <option value="textarea" @selected(($item['type'] ?? '') === 'textarea')>Jawaban panjang</option>
                                <option value="date" @selected(($item['type'] ?? '') === 'date')>Tanggal</option>
                                <option value="integer" @selected(($item['type'] ?? '') === 'integer')>Angka</option>
                                <option value="select" @selected(($item['type'] ?? '') === 'select')>Pilih dari daftar</option>
                            </select>
                        </div>
                    @endif
                    <div class="flex flex-wrap items-end gap-4 sm:col-span-2">
                        <label class="flex min-h-11 items-center gap-2 text-sm"><input type="hidden" name="{{ $inputName }}[{{ $index }}][required]" value="0"><input type="checkbox" name="{{ $inputName }}[{{ $index }}][required]" value="1" @checked($item['required'] ?? true) class="size-4 accent-emerald-700">Wajib diisi</label>
                        @unless($isAttachment)
                            <div data-max-control @class(['hidden' => ! in_array($item['type'] ?? 'text', ['text', 'textarea'], true)])>
                                <label for="{{ $itemId }}-max" class="block text-sm font-medium">Batas panjang jawaban</label>
                                <div class="mt-1 flex items-center gap-2"><input id="{{ $itemId }}-max" type="number" name="{{ $inputName }}[{{ $index }}][max]" value="{{ $item['max'] ?? 255 }}" min="1" max="2000" step="1" inputmode="numeric" aria-invalid="{{ $errors->has($inputName.'.'.$index.'.max') ? 'true' : 'false' }}" class="min-h-11 w-36 rounded-control border border-slate-400 bg-paper px-3 text-base"><span class="text-sm text-muted">karakter</span></div>
                                @error($inputName.'.'.$index.'.max')<p class="mt-1 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                            </div>
                            <div data-options-control @class(['hidden' => ($item['type'] ?? '') !== 'select'])>
                                <label for="{{ $itemId }}-options" class="block text-sm font-medium">Pilihan yang tersedia</label>
                                <textarea id="{{ $itemId }}-options" name="{{ $inputName }}[{{ $index }}][options_text]" rows="3" placeholder="Tulis satu pilihan per baris" aria-invalid="{{ $errors->has($inputName.'.'.$index.'.options') ? 'true' : 'false' }}" class="mt-1 min-h-20 w-full rounded-control border border-slate-400 bg-paper px-3 py-2 text-base">{{ $optionsText }}</textarea>
                                <p class="mt-1 text-xs text-muted">Masukkan satu pilihan pada setiap baris.</p>
                                @error($inputName.'.'.$index.'.options')<p class="mt-1 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
                            </div>
                        @endunless
                        <button type="button" data-config-remove class="ml-auto min-h-11 border border-red-700 px-3 text-sm font-medium text-red-800 focus-visible:outline-2">Hapus {{ $isAttachment ? 'dokumen' : 'isian' }}</button>
                    </div>
                </div>
            </fieldset>
        @endforeach
    </div>

    <template data-config-template>
        @if($isAttachment)
            <fieldset data-config-row class="rounded-control border border-line bg-white p-4">
                <legend class="px-1 text-sm font-semibold">Dokumen baru</legend>
                <input type="hidden" name="required_attachments[__INDEX__][key]" value="">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label for="{{ $idPrefix }}-attachment-__INDEX__-label" class="block text-sm font-medium">Nama dokumen</label><input id="{{ $idPrefix }}-attachment-__INDEX__-label" name="required_attachments[__INDEX__][label]" required maxlength="120" placeholder="Contoh: Kartu Keluarga" class="mt-1 min-h-11 w-full rounded-control border border-slate-400 bg-paper px-3 text-base"></div>
                    <div><label for="{{ $idPrefix }}-attachment-__INDEX__-size" class="block text-sm font-medium">Batas ukuran file</label><div class="mt-1 flex items-center gap-2"><input id="{{ $idPrefix }}-attachment-__INDEX__-size" type="number" name="required_attachments[__INDEX__][max_mb]" value="5" min="0.01" max="10" step="0.01" required inputmode="decimal" class="min-h-11 w-36 rounded-control border border-slate-400 bg-paper px-3 text-base"><span class="text-sm text-muted">MB (maksimal 10 MB)</span></div></div>
                    <label class="flex min-h-11 items-center gap-2 text-sm sm:col-span-2"><input type="hidden" name="required_attachments[__INDEX__][required]" value="0"><input type="checkbox" name="required_attachments[__INDEX__][required]" value="1" checked class="size-4 accent-emerald-700">Wajib diunggah</label>
                    <div class="flex justify-end sm:col-span-2"><button type="button" data-config-remove class="min-h-11 border border-red-700 px-3 text-sm font-medium text-red-800 focus-visible:outline-2">Hapus dokumen</button></div>
                </div>
            </fieldset>
        @else
            <fieldset data-config-row class="rounded-control border border-line bg-white p-4">
                <legend class="px-1 text-sm font-semibold">Isian baru</legend>
                <input type="hidden" name="required_fields[__INDEX__][key]" value="">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label for="{{ $idPrefix }}-field-__INDEX__-label" class="block text-sm font-medium">Informasi yang diminta</label><input id="{{ $idPrefix }}-field-__INDEX__-label" name="required_fields[__INDEX__][label]" required maxlength="120" placeholder="Contoh: Keperluan surat" class="mt-1 min-h-11 w-full rounded-control border border-slate-400 bg-paper px-3 text-base"></div>
                    <div><label for="{{ $idPrefix }}-field-__INDEX__-type" class="block text-sm font-medium">Cara warga mengisi</label><select id="{{ $idPrefix }}-field-__INDEX__-type" name="required_fields[__INDEX__][type]" data-field-type required class="mt-1 min-h-11 w-full rounded-control border border-slate-400 bg-paper px-3 text-base"><option value="text">Jawaban singkat</option><option value="textarea">Jawaban panjang</option><option value="date">Tanggal</option><option value="integer">Angka</option><option value="select">Pilih dari daftar</option></select></div>
                    <div class="flex flex-wrap items-end gap-4 sm:col-span-2">
                        <label class="flex min-h-11 items-center gap-2 text-sm"><input type="hidden" name="required_fields[__INDEX__][required]" value="0"><input type="checkbox" name="required_fields[__INDEX__][required]" value="1" checked class="size-4 accent-emerald-700">Wajib diisi</label>
                        <div data-max-control><label for="{{ $idPrefix }}-field-__INDEX__-max" class="block text-sm font-medium">Batas panjang jawaban</label><div class="mt-1 flex items-center gap-2"><input id="{{ $idPrefix }}-field-__INDEX__-max" type="number" name="required_fields[__INDEX__][max]" value="255" min="1" max="2000" step="1" inputmode="numeric" class="min-h-11 w-36 rounded-control border border-slate-400 bg-paper px-3 text-base"><span class="text-sm text-muted">karakter</span></div></div>
                        <div data-options-control class="hidden"><label for="{{ $idPrefix }}-field-__INDEX__-options" class="block text-sm font-medium">Pilihan yang tersedia</label><textarea id="{{ $idPrefix }}-field-__INDEX__-options" name="required_fields[__INDEX__][options_text]" rows="3" placeholder="Tulis satu pilihan per baris" class="mt-1 min-h-20 w-full rounded-control border border-slate-400 bg-paper px-3 py-2 text-base"></textarea><p class="mt-1 text-xs text-muted">Masukkan satu pilihan pada setiap baris.</p></div>
                        <button type="button" data-config-remove class="ml-auto min-h-11 border border-red-700 px-3 text-sm font-medium text-red-800 focus-visible:outline-2">Hapus isian</button>
                    </div>
                </div>
            </fieldset>
        @endif
    </template>

    <button type="button" data-config-add class="min-h-11 border border-emerald-700 px-4 text-sm font-semibold text-emerald-800 focus-visible:outline-2">{{ $isAttachment ? 'Tambah dokumen' : 'Tambah isian' }}</button>
    <p data-config-count class="text-sm text-muted" aria-live="polite"></p>
</div>
