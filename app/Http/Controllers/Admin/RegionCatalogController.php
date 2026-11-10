<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\Village;
use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegionCatalogController extends Controller
{
    public function index(AuditLogger $audit): View
    {
        abort_unless(request()->user()->hasRole('Super Admin'), 403);
        $audit->record('master_data.region_catalog_viewed', 'region_collection');
        $villages = $this->villageTreeQuery()->orderBy('name')->get();
        $activeVillages = $villages->where('is_active', true)->values();
        $rwOptions = Region::query()->where('region_type', 'rw')->whereNull('parent_id')
            ->whereHas('village', fn ($query) => $query->where('is_active', true))->with('village')
            ->orderBy('village_id')->orderBy('region_code')->get();

        return view('admin.regions.index', compact('villages', 'activeVillages', 'rwOptions'));
    }

    public function settings(Request $request, AuditLogger $audit): View
    {
        $manageableVillageId = $this->manageableVillageId($request);
        $audit->record('master_data.village_settings_viewed', 'village_collection');
        $query = $this->villageTreeQuery();
        if ($manageableVillageId !== null) {
            $query->whereKey($manageableVillageId);
        }
        $villages = $query->orderBy('name')->get();

        return view('admin.regions.settings', compact('villages'));
    }

    public function storeVillage(Request $request, AuditLogger $audit): RedirectResponse
    {
        abort_unless($request->user()->hasRole('Super Admin'), 403);
        $request->merge([
            'code' => mb_strtoupper(trim((string) $request->input('code'))),
            'name' => trim((string) $request->input('name')),
        ]);
        $validated = $request->validate([
            'code' => ['required', 'string', 'alpha_dash:ascii', 'max:30', 'unique:villages,code'],
            'name' => ['required', 'string', 'min:2', 'max:150'],
        ]);
        $village = DB::transaction(function () use ($validated, $audit): Village {
            $village = Village::query()->create($validated + ['is_active' => true]);
            $audit->record('master_data.village_created', 'village', (int) $village->getKey());

            return $village;
        });

        return redirect()->route('admin.regions.index')->with('status', 'Data desa '.$village->name.' berhasil ditambahkan.');
    }

    public function updateVillage(Request $request, Village $village, AuditLogger $audit): RedirectResponse
    {
        $this->authorizeVillageManagement($request, $village);
        $request->merge([
            'code' => mb_strtoupper(trim((string) $request->input('code'))),
            'name' => trim((string) $request->input('name')),
            'address' => trim((string) $request->input('address')),
        ]);
        $validated = $request->validate([
            'code' => ['required', 'string', 'alpha_dash:ascii', 'max:30', Rule::unique('villages', 'code')->ignore($village->getKey())],
            'name' => ['required', 'string', 'min:2', 'max:150'],
            'address' => ['nullable', 'string', 'max:2000'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
        ]);

        DB::transaction(function () use ($village, $validated, $audit): void {
            $locked = Village::query()->whereKey($village->getKey())->lockForUpdate()->firstOrFail();
            $locked->fill($validated)->save();
            $audit->record('master_data.village_updated', 'village', (int) $locked->getKey(), [
                'changed_fields' => array_values(array_keys($validated)),
            ]);
        });

        return redirect()->route('admin.village-settings.index')->with('status', 'Data desa '.$validated['name'].' berhasil diperbarui.');
    }

    public function editVillage(Request $request, Village $village): View
    {
        $this->authorizeVillageManagement($request, $village);

        return view('admin.regions.village-edit', compact('village'));
    }

    public function destroyVillage(Request $request, Village $village, AuditLogger $audit): RedirectResponse
    {
        $this->authorizeVillageManagement($request, $village);

        DB::transaction(function () use ($village, $audit): void {
            $locked = Village::query()->whereKey($village->getKey())->lockForUpdate()->firstOrFail();
            if ($locked->regions()->exists()) {
                throw ValidationException::withMessages([
                    'village' => 'Desa tidak dapat dihapus karena masih memiliki data RW/RT. Hapus atau pindahkan data wilayah terlebih dahulu.',
                ]);
            }

            $audit->record('master_data.village_deleted', 'village', (int) $locked->getKey(), [
                'village_code' => $locked->code,
            ]);
            $locked->delete();
        });

        return redirect()->route('admin.village-settings.index')->with('status', 'Data desa berhasil dihapus.');
    }

    public function destroyRegion(Request $request, Region $region, AuditLogger $audit): RedirectResponse
    {
        abort_unless($request->user()->hasRole('Super Admin'), 403);

        DB::transaction(function () use ($region, $audit): void {
            $locked = Region::query()->whereKey($region->getKey())->lockForUpdate()->firstOrFail();
            $hasChildren = $locked->children()->exists();
            $hasResidents = $locked->residents()->exists();

            if ($hasChildren || $hasResidents) {
                $reason = match (true) {
                    $hasChildren && $locked->region_type === 'rw' => 'RW tidak dapat dihapus selama masih memiliki RT. Hapus RT yang tidak digunakan terlebih dahulu.',
                    $hasChildren => 'Wilayah tidak dapat dihapus selama masih memiliki wilayah turunan.',
                    default => 'Wilayah tidak dapat dihapus karena masih digunakan oleh profil warga.',
                };

                throw ValidationException::withMessages(['region' => $reason]);
            }

            if (! Schema::hasColumn('region_officers', 'former_region_id')) {
                throw ValidationException::withMessages([
                    'region' => 'Skema database belum diperbarui untuk mempertahankan riwayat penugasan. Jalankan `php artisan migrate`, lalu coba kembali.',
                ]);
            }

            $locked->load(['parent', 'village']);
            foreach ($locked->officerAssignments()->get() as $assignment) {
                $assignment->forceFill([
                    'former_region_id' => (int) $locked->getKey(),
                    'former_region_code' => $locked->region_code,
                    'former_region_name' => $locked->name,
                    'former_region_type' => $locked->region_type,
                    'former_region_parent_name' => $locked->parent?->name,
                    'former_village_name' => $locked->village?->name,
                    'region_id' => null,
                    'is_active' => false,
                    'ended_at' => $assignment->is_active ? now() : $assignment->ended_at,
                ])->save();
                $audit->record('authorization.region_assignment_history_detached', 'region_officer', (int) $assignment->getKey(), [
                    'target_user_id' => $assignment->user_id === null ? $assignment->former_user_id : (int) $assignment->user_id,
                    'former_region_id' => (int) $locked->getKey(),
                ]);
            }

            $audit->record('master_data.region_deleted', 'region', (int) $locked->getKey(), [
                'region_type' => $locked->region_type,
                'village_id' => (int) $locked->village_id,
                'parent_id' => $locked->parent_id === null ? null : (int) $locked->parent_id,
            ]);
            $locked->delete();
        });

        return redirect()->route('admin.village-settings.index')->with('status', 'Data wilayah berhasil dihapus.');
    }

    public function storeRw(Request $request, AuditLogger $audit): RedirectResponse
    {
        abort_unless($request->user()->hasRole('Super Admin'), 403);
        $request->merge([
            'region_code' => trim((string) $request->input('region_code')),
            'name' => trim((string) $request->input('name')),
        ]);
        $validated = $request->validate([
            'village_id' => ['required', 'integer', 'exists:villages,id'],
            'region_code' => ['required', 'string', 'max:30'],
            'name' => ['required', 'string', 'min:2', 'max:120'],
        ]);
        $village = Village::query()->findOrFail($validated['village_id']);
        abort_unless($village->is_active, 422, 'RW hanya dapat ditambahkan pada desa yang aktif.');
        Validator::make($validated, [
            'region_code' => ['required', Rule::unique('regions', 'region_code')->where(fn ($query) => $query
                ->where('village_id', $village->getKey())->where('region_type', 'rw')->whereNull('parent_id'))],
        ])->validate();
        $region = DB::transaction(function () use ($validated, $audit): Region {
            $village = Village::query()->whereKey($validated['village_id'])->lockForUpdate()->firstOrFail();
            abort_unless($village->is_active, 422, 'RW hanya dapat ditambahkan pada desa yang aktif.');
            $region = Region::query()->create([
                'village_id' => $validated['village_id'],
                'parent_id' => null,
                'region_type' => 'rw',
                'region_code' => $validated['region_code'],
                'name' => $validated['name'],
            ]);
            $audit->record('master_data.region_created', 'region', (int) $region->getKey(), ['region_type' => 'rw']);

            return $region;
        });

        return redirect()->route('admin.regions.index')->with('status', $region->name.' berhasil ditambahkan.');
    }

    public function storeRt(Request $request, AuditLogger $audit): RedirectResponse
    {
        abort_unless($request->user()->hasRole('Super Admin'), 403);
        $request->merge([
            'region_code' => trim((string) $request->input('region_code')),
            'name' => trim((string) $request->input('name')),
        ]);
        $parentId = $request->input('parent_id');
        $validated = $request->validate([
            'parent_id' => ['required', 'integer', 'exists:regions,id'],
            'region_code' => ['required', 'string', 'max:30'],
            'name' => ['required', 'string', 'min:2', 'max:120'],
        ]);
        $parent = Region::query()->whereKey($validated['parent_id'])->where('region_type', 'rw')->first();
        if ($parent === null) {
            throw ValidationException::withMessages(['parent_id' => 'Pilih RW yang valid sebagai induk RT.']);
        }
        $regionCode = $validated['region_code'];
        abort_unless($parent->village?->is_active, 422, 'RT hanya dapat ditambahkan pada desa yang aktif.');
        Validator::make(['region_code' => $regionCode], [
            'region_code' => ['required', Rule::unique('regions', 'region_code')->where(fn ($query) => $query
                ->where('village_id', $parent->village_id)->where('region_type', 'rt')->where('parent_id', $parent->getKey()))],
        ])->validate();

        $region = DB::transaction(function () use ($parent, $validated, $regionCode, $audit): Region {
            $village = Village::query()->whereKey($parent->village_id)->lockForUpdate()->firstOrFail();
            abort_unless($village->is_active, 422, 'RT hanya dapat ditambahkan pada desa yang aktif.');
            $region = Region::query()->create([
                'village_id' => $parent->village_id,
                'parent_id' => $parent->getKey(),
                'region_type' => 'rt',
                'region_code' => $regionCode,
                'name' => $validated['name'],
            ]);
            $audit->record('master_data.region_created', 'region', (int) $region->getKey(), [
                'region_type' => 'rt',
                'parent_id' => (int) $parent->getKey(),
            ]);

            return $region;
        });

        return redirect()->route('admin.regions.index')->with('status', $region->name.' berhasil ditambahkan di bawah '.$parent->name.'.');
    }

    private function authorizeVillageManagement(Request $request, ?Village $village = null): void
    {
        $manageableVillageId = $this->manageableVillageId($request);
        abort_if($manageableVillageId !== null && $village !== null && (int) $village->getKey() !== $manageableVillageId, 403);
    }

    private function manageableVillageId(Request $request): ?int
    {
        if ($request->user()->hasRole('Super Admin')) {
            return null;
        }

        abort_unless($request->user()->hasRole('Village Admin'), 403);
        $activeVillageIds = Village::query()->where('is_active', true)->limit(2)->pluck('id');
        abort_unless($activeVillageIds->count() === 1, 403, 'Akun Village Admin hanya dapat mengelola satu desa aktif; tetapkan cakupan desa terlebih dahulu.');

        return (int) $activeVillageIds->first();
    }

    private function villageTreeQuery(): Builder
    {
        return Village::query()->with([
            'regions.residents:id,region_id',
            'regions.officerAssignments:id,region_id',
            'regions.children' => fn ($query) => $query->orderBy('region_code'),
            'regions.children.residents:id,region_id',
            'regions.children.officerAssignments:id,region_id',
        ]);
    }
}
