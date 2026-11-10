<x-layouts.app title="Data warga terbatas">
<main id="main" class="mx-auto max-w-5xl px-4 py-8 sm:px-8">
    <x-ui.breadcrumbs :items="[['label' => 'Ringkasan', 'url' => route('dashboard')], ['label' => 'Warga wilayah tugas', 'url' => route('officer.residents.index')], ['label' => 'Data warga']]" />
    <p class="text-sm text-muted">Tampilan terbatas · cakupan wilayah terverifikasi</p><h1 class="mt-2 break-words text-2xl font-semibold">{{ $residentName }}</h1><p class="mt-2 text-muted">{{ $resident->region->name }} / {{ $resident->region->parent->name }}</p><p class="mt-6 max-w-2xl border-l-4 border-slate-400 bg-paper p-4 leading-7">Untuk menjaga kerahasiaan, halaman petugas hanya menampilkan nama dan wilayah. Hubungi warga melalui proses layanan yang disetujui desa; jangan menyalin atau menyebarkan data ini.</p>
</main>
</x-layouts.app>
