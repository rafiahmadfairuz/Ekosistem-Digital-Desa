<x-layouts.app title="Edit profil">
<main id="main" class="mx-auto max-w-5xl px-4 py-8 sm:px-8">
    <x-ui.breadcrumbs :items="[['label' => 'Ringkasan', 'url' => route('dashboard')], ['label' => 'Profil warga', 'url' => route('profile.show')], ['label' => 'Edit profil']]" />
    <div class="grid gap-8 lg:grid-cols-[minmax(14rem,.7fr)_minmax(0,1.3fr)]">
        <section><p class="text-sm font-semibold text-emerald-800">Profil warga</p><h1 class="mt-2 text-2xl font-semibold">Perbarui data diri</h1><p class="mt-3 leading-7 text-muted">@if($resident->region_verified_at)Wilayah sudah diverifikasi dan tidak dapat diubah dari profil ini.@else Wilayah yang dipilih menunggu pemeriksaan Village Admin.@endif</p></section>
        <section class="min-w-0 bg-paper p-5 sm:p-8" aria-label="Form profil">@include('profile.form', ['action' => route('profile.update'), 'method' => 'PUT'])</section>
    </div>
</main>
</x-layouts.app>
