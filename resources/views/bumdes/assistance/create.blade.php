<x-layouts.app title="Ajukan bantuan BUMDes">
    <main id="main" class="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-8">
        <x-ui.page-heading title="Ajukan bantuan BUMDes" description="Pilih layanan yang Anda perlukan. Petugas BUMDes akan meninjau dan menerima permintaan sebelum membantu." />

        <x-ui.alert type="info">
            Jangan tulis kata sandi, kode OTP, PIN, atau informasi pembayaran. Petugas tidak akan meminta rahasia akun Anda.
        </x-ui.alert>

        <form method="POST" action="{{ route('bumdes.assistance.store') }}" class="space-y-5 rounded-card border border-line bg-paper p-5 sm:p-7">
            @csrf
            <div>
                <label for="service_type" class="block text-sm font-semibold">Layanan yang dibantu</label>
                <select id="service_type" name="service_type" required class="mt-2 min-h-11 w-full rounded-control border border-line bg-paper px-3 text-base text-ink">
                    <option value="">Pilih jenis bantuan</option>
                    @foreach ($serviceTypes as $key => $label)
                        <option value="{{ $key }}" @selected(old('service_type') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('service_type')<p class="mt-1 text-sm text-danger" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="notes" class="block text-sm font-semibold">Catatan kegiatan (opsional)</label>
                <textarea id="notes" name="notes" rows="4" maxlength="2000" aria-describedby="notes-help" class="mt-2 min-h-28 w-full rounded-control border border-line bg-paper px-3 py-2 text-base">{{ old('notes') }}</textarea>
                <p id="notes-help" class="mt-1 text-sm text-muted">Tuliskan hal umum yang dibantu, bukan data pribadi atau rahasia.</p>
            </div>

            <div class="flex flex-wrap gap-3 border-t border-line pt-4">
                <x-ui.button type="submit">Kirim permintaan bantuan</x-ui.button>
                <a href="{{ route('bumdes.assistance.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-control border border-slate-500 px-4 text-sm font-medium hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2">Batal</a>
            </div>
        </form>
    </main>
</x-layouts.app>
