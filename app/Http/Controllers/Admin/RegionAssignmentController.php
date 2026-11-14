<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\RegionOfficer;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RegionAssignmentController extends Controller
{
    public function index(AuditLogger $audit): View
    {
        abort_unless(request()->user()->hasRole('Super Admin'), 403);
        $audit->record('authorization.region_assignments_viewed', 'region_officer_collection');

        $assignments = RegionOfficer::query()->with(['user.roles', 'region.parent', 'region.village'])->orderByDesc('assigned_at')->simplePaginate(30);
        $users = User::query()->where('account_status', 'active')->with('roles')->orderBy('username')->get()
            ->filter(fn (User $user): bool => $user->roles->contains(fn ($role): bool => in_array($role->name, ['RT', 'RW'], true)));
        $regions = Region::query()->with(['parent', 'village'])->orderBy('region_type')->orderBy('region_code')->get();
        $rtRegions = $regions->filter(fn (Region $region): bool => $region->region_type === 'rt'
            && $region->village?->is_active
            && $region->parent?->region_type === 'rw'
            && (int) $region->parent->village_id === (int) $region->village_id
            && $region->parent->village?->is_active)->values();
        $rwRegions = $regions->filter(fn (Region $region): bool => $region->region_type === 'rw'
            && $region->parent_id === null
            && $region->village?->is_active)->values();

        return view('admin.region-assignments.index', compact('assignments', 'users', 'rtRegions', 'rwRegions'));
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        abort_unless($request->user()->hasRole('Super Admin'), 403);

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'region_id' => ['required', 'integer', 'exists:regions,id'],
        ]);
        $region = Region::query()->findOrFail($validated['region_id']);
        $role = $region->region_type === 'rt' ? 'RT' : 'RW';

        abort_if($region->region_type === 'rt' && ($region->parent === null || $region->parent->region_type !== 'rw' || (int) $region->parent->village_id !== (int) $region->village_id), 422, 'Wilayah RT harus berada di bawah RW pada desa yang sama.');
        abort_if($region->region_type === 'rw' && $region->parent_id !== null, 422, 'Wilayah RW tidak boleh memiliki induk.');
        abort_unless($region->village?->is_active, 422, 'Penugasan hanya dapat dibuat pada desa yang aktif.');

        $assignment = DB::transaction(function () use ($validated, $region, $role, $audit): RegionOfficer {
            $officer = User::query()->with('roles')->whereKey($validated['user_id'])->lockForUpdate()->firstOrFail();
            $roleNames = $officer->roles->pluck('name')->sort()->values()->all();
            $allowedRoleSets = [
                'RT' => [['RT'], ['RT', 'Resident']],
                'RW' => [['RW'], ['RW', 'Resident']],
            ];
            abort_unless(
                $officer->account_status === 'active'
                    && in_array($roleNames, $allowedRoleSets[$role] ?? [], true),
                422,
                'Akun aktif harus memiliki peran yang sesuai dengan jenis wilayah.'
            );

            $existing = RegionOfficer::query()->where('user_id', $officer->getKey())
                ->where('region_id', $region->getKey())->where('is_active', true)->exists();

            abort_if($existing, 422, 'Penugasan aktif sudah tersedia.');

            $assignment = RegionOfficer::query()->create([
                'user_id' => $officer->getKey(),
                'region_id' => $region->getKey(),
                'assigned_at' => now(),
                'assigned_by' => auth()->id(),
                'is_active' => true,
                'ended_at' => null,
            ]);
            $audit->record('authorization.region_assigned', 'region_officer', (int) $assignment->getKey(), [
                'target_user_id' => (int) $officer->getKey(), 'region_id' => (int) $region->getKey(),
            ]);

            return $assignment;
        });

        return back()->with('status', 'Penugasan wilayah berhasil ditambahkan.');
    }

    public function revoke(RegionOfficer $assignment, AuditLogger $audit): RedirectResponse
    {
        abort_unless(request()->user()->hasRole('Super Admin'), 403);
        abort_if(! $assignment->is_active, 404);

        DB::transaction(function () use ($assignment, $audit): void {
            User::query()->whereKey($assignment->user_id)->lockForUpdate()->firstOrFail();
            $locked = RegionOfficer::query()->whereKey($assignment->getKey())->lockForUpdate()->firstOrFail();
            abort_if(! $locked->is_active, 404);
            $locked->forceFill(['is_active' => false, 'ended_at' => now()])->save();
            $audit->record('authorization.region_revoked', 'region_officer', (int) $locked->getKey(), [
                'target_user_id' => (int) $locked->user_id, 'region_id' => (int) $locked->region_id,
            ]);
        });

        return back()->with('status', 'Penugasan wilayah dinonaktifkan. Riwayat penugasan dipertahankan.');
    }
}
