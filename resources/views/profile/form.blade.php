<form method="POST" action="{{ $action }}" class="space-y-5" data-unsaved-form novalidate>@csrf @if($method !== 'POST')@method($method)@endif
    <div><label class="block text-sm font-semibold" for="full_name">Nama lengkap</label><input class="mt-2 min-h-12 w-full border border-slate-400 bg-white px-3 text-base focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand" id="full_name" name="full_name" value="{{ old('full_name', $profile['full_name'] ?? '') }}" required maxlength="180" autocomplete="name" aria-describedby="full_name-help @error('full_name') full_name-error @enderror" @error('full_name') aria-invalid="true" @enderror><p id="full_name-help" class="mt-1 text-sm text-muted">Nama disimpan dalam bentuk terenkripsi.</p>@error('full_name')<p id="full_name-error" class="mt-1 text-sm text-red-800">{{ $message }}</p>@enderror</div>
    @if(isset($regions) && $regions->isNotEmpty() && ($resident->region_verified_at ?? null) === null)
        <div>
            <label class="block text-sm font-semibold" for="region_id">Wilayah tempat tinggal</label>
            <p id="region-help" class="mt-1 text-sm text-muted">Pilih RT dan RW tempat tinggal, misalnya RT 04 / RW 05. Pilihan ini menunggu pemeriksaan Admin Desa.</p>
            <select class="mt-2 min-h-12 w-full border border-slate-400 bg-white px-3 text-base" id="region_id" name="region_id" required aria-describedby="region-help @error('region_id') region-id-error @enderror" @error('region_id') aria-invalid="true" @enderror>
                <option value="">Pilih Wilayah</option>
                @foreach($regions as $region)
                    <option value="{{ $region->id }}" @selected((string) old('region_id', $resident->region_id ?? '') === (string) $region->id)>{{ $region->name }} / {{ $region->parent->name }}</option>
                @endforeach
            </select>
            @error('region_id')<p id="region-id-error" class="mt-2 text-sm text-red-800">{{ $message }}</p>@enderror
        </div>
    @elseif(($resident->region_verified_at ?? null) === null)
        <div class="border-l-4 border-amber-700 bg-amber-50 p-4 text-sm leading-6 text-amber-950" role="status">
            <p class="font-semibold">Wilayah RT belum disiapkan</p>
            <p class="mt-1">Profil belum dapat disimpan karena belum ada pilihan Wilayah yang valid. Minta Super Admin menambahkan data desa, RW, dan RT, lalu muat ulang halaman ini.</p>
            @error('region_id')<p class="mt-2 font-medium">{{ $message }}</p>@enderror
        </div>
    @endif
    <div><label class="block text-sm font-semibold" for="phone">Nomor telepon <span class="font-normal text-muted">(opsional)</span></label><input class="mt-2 min-h-12 w-full border border-slate-400 bg-white px-3 text-base" id="phone" name="phone" type="tel" value="{{ old('phone', $profile['phone'] ?? '') }}" autocomplete="tel" maxlength="30" inputmode="tel"></div>
    <div><label class="block text-sm font-semibold" for="address">Alamat</label><textarea class="mt-2 min-h-24 w-full border border-slate-400 bg-white px-3 py-2 text-base" id="address" name="address" autocomplete="street-address" maxlength="2000">{{ old('address', $profile['address'] ?? '') }}</textarea></div>
    <div><label class="block text-sm font-semibold" for="birth_date">Tanggal lahir <span class="font-normal text-muted">(opsional)</span></label><input class="mt-2 min-h-12 w-full border border-slate-400 bg-white px-3 text-base" id="birth_date" name="birth_date" type="date" value="{{ old('birth_date', $profile['birth_date'] ?? '') }}" autocomplete="bday"></div>
    <div><label class="block text-sm font-semibold" for="gender">Jenis kelamin <span class="font-normal text-muted">(opsional)</span></label><select class="mt-2 min-h-12 w-full border border-slate-400 bg-white px-3 text-base" id="gender" name="gender"><option value="">Pilih…</option><option value="male" @selected(old('gender', $profile['gender'] ?? '')==='male')>Laki-laki</option><option value="female" @selected(old('gender', $profile['gender'] ?? '')==='female')>Perempuan</option><option value="other" @selected(old('gender', $profile['gender'] ?? '')==='other')>Lainnya</option><option value="undisclosed" @selected(old('gender', $profile['gender'] ?? '')==='undisclosed')>Tidak ingin menyebutkan</option></select></div>
    <button class="min-h-12 bg-brand px-5 font-semibold text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-ink" data-submit-button><span>Simpan profil</span><svg data-loading-indicator hidden class="ml-2 inline size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".25" stroke-width="3"></circle><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3"></path></svg></button><p data-loading-status class="sr-only" role="status" aria-live="polite"></p>
</form>
