<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\Resident;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\ResidentProfileData;
use App\Services\ResidentRegionScope;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ResidentProfileController extends Controller
{
    public function show(Request $request, ResidentProfileData $profileData, AuditLogger $audit): View|RedirectResponse
    {
        $resident = $request->user()->resident;

        if ($resident === null) {
            $this->authorize('create', Resident::class);

            return redirect()->route('profile.create');
        }

        $this->authorize('view', $resident);
        $audit->record('resident.profile_viewed', 'resident', (int) $resident->getKey(), request: $request);

        return view('profile.show', [
            'resident' => $resident->load('region.parent'),
            'profile' => $profileData->forOwner($resident),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Resident::class);

        return view('profile.create', ['regions' => $this->availableRtRegions()]);
    }

    public function store(Request $request, ResidentProfileData $profileData, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('create', Resident::class);
        $validated = $this->validateProfile($request, true);
        $resident = DB::transaction(function () use ($request, $profileData, $validated, $audit): Resident {
            $owner = User::query()->whereKey($request->user()->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($owner->account_status === 'active' && $owner->hasRole('Resident') && ! $owner->resident()->exists(), 403);
            $resident = new Resident;
            $resident->user_id = $owner->getKey();
            $resident->region_id = $validated['region_id'];
            $profileData->apply($resident, $validated);
            $resident->save();
            $audit->record('resident.profile_created', 'resident', (int) $resident->getKey(), ['region_verified' => false], $request);

            return $resident;
        });

        return redirect()->route('profile.show')->with('status', 'Profil tersimpan. Wilayah tempat tinggal menunggu verifikasi administrasi.');
    }

    public function edit(Request $request, ResidentProfileData $profileData): View|RedirectResponse
    {
        $resident = $request->user()->resident;
        if ($resident === null) {
            return redirect()->route('profile.create');
        }

        $this->authorize('update', $resident);

        return view('profile.edit', [
            'resident' => $resident,
            'profile' => $profileData->forOwner($resident),
            'regions' => $resident->region_verified_at === null ? $this->availableRtRegions() : collect(),
        ]);
    }

    public function update(Request $request, ResidentProfileData $profileData, AuditLogger $audit): RedirectResponse
    {
        $resident = $request->user()->resident;
        abort_unless($resident !== null, 404);
        $this->authorize('update', $resident);
        $validated = $this->validateProfile($request, $resident->region_verified_at === null);

        DB::transaction(function () use ($resident, $validated, $profileData, $audit, $request): void {
            $owner = User::query()->whereKey($request->user()->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($owner->account_status === 'active' && $owner->hasRole('Resident'), 403);
            $locked = Resident::query()->whereKey($resident->getKey())->lockForUpdate()->firstOrFail();
            if ($locked->region_verified_at !== null
                && array_key_exists('address', $validated)
                && $profileData->forOwner($locked)['address'] !== trim((string) ($validated['address'] ?? ''))) {
                $locked->region_verified_at = null;
            }

            if (array_key_exists('region_id', $validated) && $locked->region_verified_at === null) {
                $locked->region_id = $validated['region_id'];
            }
            $profileData->apply($locked, $validated);
            $locked->save();
            $audit->record('resident.profile_updated', 'resident', (int) $locked->getKey(), ['region_verified' => $locked->region_verified_at !== null], $request);
        });

        return redirect()->route('profile.show')->with('status', 'Profil berhasil diperbarui.');
    }

    public function review(Request $request, Resident $resident, ResidentProfileData $profileData, AuditLogger $audit): View
    {
        abort_unless($request->user()->can('view', $resident), 404);
        abort_if((int) $request->user()->getKey() === (int) $resident->user_id, 404);
        $audit->record('resident.profile_viewed_by_officer', 'resident', (int) $resident->getKey(), ['scope' => 'assigned_region'], $request);

        return view('residents.review', [
            'resident' => $resident->load(['user:id,username', 'region.parent']),
            'residentName' => $profileData->visibleName($resident),
        ]);
    }

    public function regionalIndex(Request $request, ResidentRegionScope $scope, ResidentProfileData $profileData, AuditLogger $audit): View
    {
        $this->authorize('viewAny', Resident::class);
        $residents = $scope->queryFor($request->user())->with(['region.parent'])->orderBy('id')->simplePaginate(30);
        foreach ($residents as $resident) {
            $resident->setAttribute('display_name', $profileData->visibleName($resident));
            $audit->record('resident.profile_listed_by_officer', 'resident', (int) $resident->getKey(), ['scope' => 'assigned_region'], $request);
        }

        return view('residents.index', compact('residents'));
    }

    /** @return array<string, mixed> */
    private function validateProfile(Request $request, bool $allowRegion): array
    {
        $availableRegions = $allowRegion ? $this->availableRtRegions() : collect();
        if ($allowRegion && $availableRegions->isEmpty()) {
            throw ValidationException::withMessages([
                'region_id' => 'Daftar wilayah RT belum tersedia. Hubungi Super Admin untuk menambahkan data desa, RW, dan RT terlebih dahulu.',
            ]);
        }

        $rules = [
            'full_name' => ['required', 'string', 'min:2', 'max:180'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:2000'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female,other,undisclosed'],
        ];
        if ($allowRegion) {
            $rules['region_id'] = ['required', 'integer', 'exists:regions,id'];
        }

        $validated = $request->validate($rules);
        if ($allowRegion) {
            abort_unless($availableRegions->contains('id', (int) $validated['region_id']), 422, 'Pilih wilayah RT yang terhubung dengan RW yang valid.');
        }

        return $validated;
    }

    /** @return Collection<int, Region> */
    private function availableRtRegions(): Collection
    {
        return Region::query()->where('region_type', 'rt')->whereHas('village', fn ($query) => $query->where('is_active', true))
            ->with(['parent', 'village'])->orderBy('village_id')->orderBy('region_code')->get()
            ->filter(fn (Region $region): bool => $region->parent?->region_type === 'rw'
                && (int) $region->parent->village_id === (int) $region->village_id)
            ->values();
    }
}
