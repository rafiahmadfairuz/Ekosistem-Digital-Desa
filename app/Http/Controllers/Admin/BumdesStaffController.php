<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BumdesStaff;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BumdesStaffController extends Controller
{
    public function index(AuditLogger $audit): View
    {
        $this->authorize('viewAny', BumdesStaff::class);
        $audit->record('bumdes_staff.list_viewed', 'bumdes_staff_collection');

        return view('bumdes.staff.index', [
            'staff' => BumdesStaff::query()->with('user.roles')->orderBy('department')->orderBy('position')->simplePaginate(30),
            'eligibleUsers' => User::query()->where('account_status', 'active')->whereDoesntHave('bumdesStaff')
                ->whereHas('roles', fn ($query) => $query->whereIn('name', ['BUMDes Manager', 'BUMDes Operator']))
                ->with('roles')->orderBy('username')->get(),
        ]);
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('create', BumdesStaff::class);
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'department' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'joined_at' => ['nullable', 'date'],
        ]);
        DB::transaction(function () use ($data, $audit): void {
            $user = User::query()->with('roles')->whereKey($data['user_id'])->lockForUpdate()->firstOrFail();
            abort_unless($user->account_status === 'active' && $user->roles->contains(fn ($role): bool => in_array($role->name, ['BUMDes Manager', 'BUMDes Operator'], true)), 422);
            abort_if(BumdesStaff::query()->where('user_id', $user->getKey())->exists(), 422, 'Profil staf BUMDes sudah tersedia.');
            $profile = BumdesStaff::query()->create([
                ...$data,
                'employment_status' => 'active',
            ]);
            $audit->record('bumdes_staff.profile_created', 'bumdes_staff', (int) $profile->getKey(), ['target_user_id' => (int) $user->getKey()]);
        });

        return back()->with('status', 'Profil staf BUMDes berhasil dibuat.');
    }

    public function update(Request $request, BumdesStaff $staff, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('update', $staff);
        $data = $request->validate([
            'department' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'employment_status' => ['required', 'in:active,inactive,suspended'],
            'joined_at' => ['nullable', 'date'],
        ]);
        DB::transaction(function () use ($staff, $data, $audit): void {
            $staff->fill($data)->save();
            $audit->record('bumdes_staff.profile_updated', 'bumdes_staff', (int) $staff->getKey(), ['employment_status' => $staff->employment_status]);
        });

        return back()->with('status', 'Profil staf BUMDes diperbarui.');
    }

    public function destroy(BumdesStaff $staff, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('delete', $staff);

        DB::transaction(function () use ($staff, $audit): void {
            $staffId = (int) $staff->getKey();
            $userId = (int) $staff->user_id;

            $audit->record('bumdes_staff.profile_deleted', 'bumdes_staff', $staffId, ['target_user_id' => $userId]);
            $staff->delete();
        });

        return back()->with('status', 'Profil staf BUMDes dihapus. Akun dan perannya tetap tersedia.');
    }
}
