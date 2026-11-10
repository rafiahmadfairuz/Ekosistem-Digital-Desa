<x-layouts.app title="Edit data desa">
    <main id="main" class="mx-auto max-w-3xl px-4 py-8 sm:px-8">
        <x-ui.breadcrumbs :items="[['label' => 'Pengaturan desa', 'url' => route('admin.village-settings.index')], ['label' => 'Edit desa']]" />
        <x-ui.page-heading class="mt-5" title="Edit data desa" description="Perubahan nama atau kode desa akan terlihat pada pilihan wilayah dan data administrasi terkait.">
            <x-slot:actions><a class="inline-flex min-h-11 items-center underline underline-offset-4" href="{{ route('admin.village-settings.index') }}">Kembali ke pengaturan desa</a></x-slot:actions>
        </x-ui.page-heading>

        <form class="mt-6 space-y-5 rounded-card border border-line bg-paper p-5 sm:p-6" method="POST" action="{{ route('admin.villages.update', $village) }}" data-unsaved-form>
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-semibold" for="village-name">Nama desa</label>
                <input class="mt-2 min-h-11 w-full border border-slate-400 bg-white px-3 text-base" id="village-name" name="name" value="{{ old('name', $village->name) }}" required maxlength="150" autocomplete="organization" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">
                @error('name')<p class="mt-1 text-sm text-red-800" role="alert">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold" for="village-code">Kode desa</label>
                <input class="mt-2 min-h-11 w-full border border-slate-400 bg-white px-3 text-base" id="village-code" name="code" value="{{ old('code', $village->code) }}" required maxlength="30" autocomplete="off" spellcheck="false" aria-invalid="{{ $errors->has('code') ? 'true' : 'false' }}">
                @error('code')<p class="mt-1 text-sm text-red-800" role="alert">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold" for="village-address">Alamat</label>
                <textarea class="mt-2 min-h-24 w-full border border-slate-400 bg-white px-3 py-2 text-base" id="village-address" name="address" maxlength="2000">{{ old('address', $village->address) }}</textarea>
                @error('address')<p class="mt-1 text-sm text-red-800" role="alert">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-semibold" for="village-postal-code">Kode pos</label>
                    <input class="mt-2 min-h-11 w-full border border-slate-400 bg-white px-3 text-base" id="village-postal-code" name="postal_code" value="{{ old('postal_code', $village->postal_code) }}" maxlength="10" inputmode="numeric">
                    @error('postal_code')<p class="mt-1 text-sm text-red-800" role="alert">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold" for="village-phone">Telepon</label>
                    <input class="mt-2 min-h-11 w-full border border-slate-400 bg-white px-3 text-base" id="village-phone" name="phone" type="tel" value="{{ old('phone', $village->phone) }}" maxlength="30" autocomplete="tel">
                    @error('phone')<p class="mt-1 text-sm text-red-800" role="alert">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold" for="village-email">Email</label>
                <input class="mt-2 min-h-11 w-full border border-slate-400 bg-white px-3 text-base" id="village-email" name="email" type="email" value="{{ old('email', $village->email) }}" maxlength="150" autocomplete="email">
                @error('email')<p class="mt-1 text-sm text-red-800" role="alert">{{ $message }}</p>@enderror
            </div>
            <button class="min-h-11 bg-brand px-5 font-semibold text-ink focus-visible:outline-2 focus-visible:outline-offset-2" data-submit-button>Simpan perubahan</button>
        </form>
    </main>
</x-layouts.app>
