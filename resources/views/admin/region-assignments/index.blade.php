
<x-layouts.app title="Penugasan wilayah">
    <main id="main" class="mx-auto max-w-6xl px-4 py-7 sm:px-8 sm:py-9">
        <header class="max-w-3xl">
            <p class="inline-flex items-center gap-2 text-xs font-semibold text-emerald-600">
                <span class="size-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                <span>Akses teknis · Super Admin</span>
            </p>

            <h1 class="mt-2 text-2xl font-bold tracking-tight text-slate-800 sm:text-[1.625rem]">
                Penugasan RT/RW
            </h1>

            <p class="mt-2.5 text-sm leading-relaxed text-slate-600">
                Cakupan data petugas berasal dari penugasan aktif. Menambahkan atau mencabut penugasan tidak mengubah peran akun.
            </p>

            <p class="mt-2 rounded-xl border border-emerald-100 bg-emerald-50/70 px-4 py-3 text-xs leading-relaxed text-slate-600">
                Petugas RT ditugaskan pada satu pasangan RT/RW. Petugas RW ditugaskan pada RW yang menaungi RT di bawahnya. Akun harus memiliki peran yang sesuai dengan wilayah yang dipilih.
            </p>
        </header>

        {{-- Formulir tambah penugasan --}}
        <section class="mt-7 rounded-2xl border border-slate-200 bg-white p-5 shadow-xs sm:p-6" aria-label="Formulir tambah penugasan wilayah">
            <form class="grid gap-4 md:grid-cols-[1fr_1fr_auto] md:items-end"
                  data-unsaved-form
                  method="POST"
                  action="{{ route('admin.region-assignments.store') }}">
                @csrf

                <div class="min-w-0">
                    <label class="block text-xs font-semibold text-slate-700" for="user_id">
                        Petugas aktif
                    </label>

                    <select class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15"
                            id="user_id"
                            name="user_id"
                            required>
                        <option value="">Pilih akun…</option>

                        @foreach($users as $user)
                            <option
                                value="{{ $user->id }}"
                                data-role-set="{{ $user->roles->pluck('name')->intersect(['RT','RW','Resident'])->sort()->implode('|') }}">
                                {{ $user->username }} · {{ $user->roles->pluck('name')->intersect(['RT','RW','Resident'])->implode(', ') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="min-w-0">
                    <label class="block text-xs font-semibold text-slate-700" for="region_id">
                        Wilayah penugasan
                    </label>

                    <select class="mt-1.5 min-h-11 w-full rounded-xl border border-slate-300 bg-white px-3 text-sm text-slate-800 transition focus:border-emerald-600 focus:outline-none focus:ring-3 focus:ring-emerald-600/15"
                            id="region_id"
                            name="region_id"
                            required
                            disabled>
                        <option value="">Pilih akun terlebih dahulu…</option>
                    </select>
                </div>

                <button class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-xl bg-emerald-600 px-5 text-sm font-semibold text-white shadow-xs transition hover:bg-emerald-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600">
                    Tambahkan penugasan
                </button>
            </form>

            <p id="region-assignment-help" class="mt-3 text-xs leading-relaxed text-slate-500" aria-live="polite">
                Pilih akun untuk melihat jenis wilayah yang sesuai.
            </p>
        </section>

        {{-- Filter wilayah berdasarkan peran petugas --}}
        <script>
            const officerSelect = document.getElementById('user_id');
            const regionSelect = document.getElementById('region_id');
            const regionHelp = document.getElementById('region-assignment-help');

            const regionOptions = [
                @foreach($rtRegions as $region)
                    {
                        id: @json((string) $region->id),
                        type: 'RT',
                        label: @json('RT '.$region->region_code.' / RW '.$region->parent->region_code)
                    },
                @endforeach

                @foreach($rwRegions as $region)
                    {
                        id: @json((string) $region->id),
                        type: 'RW',
                        label: @json('RW '.$region->region_code)
                    },
                @endforeach
            ];

            officerSelect.addEventListener('change', () => {
                const selectedOfficer = officerSelect.selectedOptions[0];
                const roles = selectedOfficer?.dataset.roleSet?.split('|') ?? [];

                const allowedType = roles.includes('RT') && !roles.includes('RW')
                    ? 'RT'
                    : roles.includes('RW') && !roles.includes('RT')
                        ? 'RW'
                        : null;

                let visibleCount = 0;

                regionSelect.replaceChildren(
                    new Option(
                        allowedType ? 'Pilih wilayah…' : 'Pilih akun terlebih dahulu…',
                        ''
                    )
                );

                for (const region of regionOptions) {
                    if (region.type === allowedType) {
                        regionSelect.add(new Option(region.label, region.id));
                        visibleCount++;
                    }
                }

                regionSelect.value = '';
                regionSelect.disabled = visibleCount === 0;

                regionSelect.options[0].textContent = visibleCount > 0
                    ? 'Pilih wilayah…'
                    : (
                        allowedType
                            ? 'Tidak ada wilayah yang sesuai…'
                            : 'Pilih akun terlebih dahulu…'
                    );

                regionHelp.textContent = visibleCount > 0
                    ? `Pilih wilayah ${allowedType} untuk akun ini.`
                    : 'Akun ini tidak memiliki satu peran RT atau RW yang sesuai, atau wilayahnya belum tersedia.';
            });
        </script>

        {{-- Riwayat penugasan --}}
        <section class="mt-8" aria-labelledby="assignment-history-title">
            <div class="flex items-center justify-between gap-3">
                <h2 id="assignment-history-title" class="text-lg font-bold tracking-tight text-slate-800">
                    Riwayat penugasan
                </h2>
            </div>

            <div class="mt-3.5 space-y-3">
                @forelse($assignments as $assignment)
                    @php($regionLabel = $assignment->region ? ($assignment->region->region_type === 'rt' ? $assignment->region->name.' / '.$assignment->region->parent?->name : $assignment->region->name) : (($assignment->former_region_type === 'rt' ? $assignment->former_region_name.' / '.$assignment->former_region_parent_name : $assignment->former_region_name).' · '.$assignment->former_village_name))

                    <article class="flex flex-col justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-xs transition hover:border-slate-300 sm:flex-row sm:items-center sm:px-5">
                        <div class="flex min-w-0 items-start gap-3.5">
                            <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 text-xs font-bold uppercase text-emerald-700" aria-hidden="true">
                                {{ mb_substr($assignment->user?->username ?? '?', 0, 1) }}
                            </span>

                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="break-words text-sm font-semibold text-slate-800">
                                        {{ $assignment->user?->username ?? 'Akun dihapus · referensi #'.($assignment->former_user_id ?? 'tidak tersedia') }}

                                        <span class="mx-1 font-normal text-slate-400">·</span>

                                        <span class="text-emerald-700">{{ $regionLabel }}</span>

                                        @if($assignment->region === null)
                                            <span class="ml-1 inline-flex items-center rounded-md border border-slate-200 bg-slate-50 px-2 py-0.5 text-xs font-medium text-slate-500">
                                                (wilayah dihapus)
                                            </span>
                                        @endif
                                    </p>
                                </div>

                                <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                    @if($assignment->is_active)
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                                            <span class="size-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                                            <span>Aktif</span>
                                        </span>

                                        <p class="text-xs text-slate-500">
                                            Penugasan aktif sejak {{ $assignment->assigned_at }}
                                        </p>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-0.5 text-xs font-medium text-slate-600">
                                            <span class="size-1.5 rounded-full bg-slate-400" aria-hidden="true"></span>
                                            <span>Berakhir</span>
                                        </span>

                                        <p class="text-xs text-slate-500">
                                            Penugasan berakhir {{ $assignment->ended_at }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($assignment->is_active)
                            <form method="POST"
                                  action="{{ route('admin.region-assignments.revoke', $assignment) }}"
                                  data-confirm="Cabut penugasan wilayah ini? Akses terkait akan berhenti segera."
                                  class="shrink-0">
                                @csrf
                                @method('DELETE')

                                <button class="inline-flex min-h-10 items-center justify-center rounded-xl border border-red-200 bg-white px-4 text-xs font-semibold text-red-700 transition hover:border-red-300 hover:bg-red-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
                                    Cabut penugasan
                                </button>
                            </form>
                        @endif
                    </article>
                @empty
                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-5 py-10 text-center">
                        <p class="text-sm font-medium text-slate-500">
                            Belum ada penugasan wilayah.
                        </p>
                    </div>
                @endforelse
            </div>

            <div class="mt-5">
                {{ $assignments->links() }}
            </div>
        </section>
    </main>
</x-layouts.app>
