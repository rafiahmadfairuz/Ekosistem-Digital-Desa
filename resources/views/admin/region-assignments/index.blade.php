<x-layouts.app title="Penugasan wilayah">
<main id="main" class="mx-auto max-w-6xl px-4 py-8 sm:px-8"><p class="text-sm text-muted">Akses teknis · Super Admin</p><h1 class="mt-2 text-2xl font-semibold">Penugasan RT/RW</h1><p class="mt-2 max-w-3xl leading-7 text-muted">Cakupan data petugas berasal dari penugasan aktif. Menambahkan atau mencabut penugasan tidak mengubah peran akun.</p>
<p class="mt-2 max-w-3xl leading-7 text-muted">Petugas RT ditugaskan pada satu pasangan RT/RW. Petugas RW ditugaskan pada RW yang menaungi RT di bawahnya. Akun harus memiliki peran yang sesuai dengan wilayah yang dipilih.</p>
<form class="mt-7 grid gap-4 border-y border-slate-300 py-5 md:grid-cols-[1fr_1fr_auto] md:items-end" data-unsaved-form method="POST" action="{{ route('admin.region-assignments.store') }}">@csrf
<div><label class="block text-sm font-semibold" for="user_id">Petugas aktif</label><select class="mt-2 min-h-11 w-full border border-slate-400 bg-white px-2 text-base" id="user_id" name="user_id" required><option value="">Pilih akun…</option>@foreach($users as $user)<option value="{{ $user->id }}" data-role-set="{{ $user->roles->pluck('name')->intersect(['RT','RW','Resident'])->sort()->implode('|') }}">{{ $user->username }} · {{ $user->roles->pluck('name')->intersect(['RT','RW','Resident'])->implode(', ') }}</option>@endforeach</select></div>
<div><label class="block text-sm font-semibold" for="region_id">Wilayah penugasan</label><select class="mt-2 min-h-11 w-full border border-slate-400 bg-white px-2 text-base" id="region_id" name="region_id" required disabled><option value="">Pilih akun terlebih dahulu…</option>
</select></div>
<button class="min-h-11 bg-brand px-5 font-semibold">Tambahkan penugasan</button></form>
<p id="region-assignment-help" class="mt-2 text-sm text-muted" aria-live="polite">Pilih akun untuk melihat jenis wilayah yang sesuai.</p>
<script>
    const officerSelect = document.getElementById('user_id');
    const regionSelect = document.getElementById('region_id');
    const regionHelp = document.getElementById('region-assignment-help');
    const regionOptions = [
        @foreach($rtRegions as $region)
            { id: @json((string) $region->id), type: 'RT', label: @json('RT '.$region->region_code.' / RW '.$region->parent->region_code) },
        @endforeach
        @foreach($rwRegions as $region)
            { id: @json((string) $region->id), type: 'RW', label: @json('RW '.$region->region_code) },
        @endforeach
    ];

    officerSelect.addEventListener('change', () => {
        const selectedOfficer = officerSelect.selectedOptions[0];
        const roles = selectedOfficer?.dataset.roleSet?.split('|') ?? [];
        const allowedType = roles.includes('RT') && !roles.includes('RW')
            ? 'RT'
            : roles.includes('RW') && !roles.includes('RT') ? 'RW' : null;
        let visibleCount = 0;

        regionSelect.replaceChildren(new Option(allowedType ? 'Pilih wilayah…' : 'Pilih akun terlebih dahulu…', ''));
        for (const region of regionOptions) {
            if (region.type === allowedType) {
                regionSelect.add(new Option(region.label, region.id));
                visibleCount++;
            }
        }

        regionSelect.value = '';
        regionSelect.disabled = visibleCount === 0;
        regionSelect.options[0].textContent = visibleCount > 0 ? 'Pilih wilayah…' : (allowedType ? 'Tidak ada wilayah yang sesuai…' : 'Pilih akun terlebih dahulu…');
        regionHelp.textContent = visibleCount > 0
            ? `Pilih wilayah ${allowedType} untuk akun ini.`
            : 'Akun ini tidak memiliki satu peran RT atau RW yang sesuai, atau wilayahnya belum tersedia.';
    });
</script>
<h2 class="mt-8 text-lg font-semibold">Riwayat penugasan</h2><div class="mt-3 divide-y divide-slate-200 border-y border-slate-300">@forelse($assignments as $assignment)@php($regionLabel = $assignment->region ? ($assignment->region->region_type === 'rt' ? $assignment->region->name.' / '.$assignment->region->parent?->name : $assignment->region->name) : (($assignment->former_region_type === 'rt' ? $assignment->former_region_name.' / '.$assignment->former_region_parent_name : $assignment->former_region_name).' · '.$assignment->former_village_name))<article class="flex flex-wrap items-center justify-between gap-3 py-4"><div class="min-w-0"><p class="break-words font-medium">{{ $assignment->user?->username ?? 'Akun dihapus · referensi #'.($assignment->former_user_id ?? 'tidak tersedia') }} · {{ $regionLabel }} @if($assignment->region === null)<span class="text-sm font-normal text-muted">(wilayah dihapus)</span>@endif</p><p class="mt-1 text-sm text-muted">{{ $assignment->is_active ? 'Penugasan aktif sejak '.$assignment->assigned_at : 'Penugasan berakhir '.$assignment->ended_at }}</p></div>@if($assignment->is_active)<form method="POST" action="{{ route('admin.region-assignments.revoke', $assignment) }}" data-confirm="Cabut penugasan wilayah ini? Akses terkait akan berhenti segera.">@csrf @method('DELETE')<button class="min-h-11 border border-slate-500 px-4 font-medium">Cabut penugasan</button></form>@endif</article>@empty<p class="py-6 text-muted">Belum ada penugasan wilayah.</p>@endforelse</div><div class="mt-5">{{ $assignments->links() }}</div></main>
</x-layouts.app>
