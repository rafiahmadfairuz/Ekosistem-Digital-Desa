<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BumdesStaff;
use App\Models\RegionOfficer;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserAccessController extends Controller
{
    private const PROTECTED_ROLES = ['Super Admin', 'Village Head'];

    private const ASSIGNABLE_ROLES = ['BUMDes Manager', 'BUMDes Operator', 'Village Admin', 'RT', 'RW', 'Resident', 'Village Head'];

    public function index(AuditLogger $audit): View
    {
        $this->authorize('viewAny', User::class);
        $audit->record('authorization.user_list_viewed', 'account_collection');

        $users = User::query()->with('roles')->orderByDesc('created_at')->simplePaginate(30);
        $roles = Role::query()->whereIn('name', self::ASSIGNABLE_ROLES)->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function villageHeadReplacement(): View
    {
        $this->authorize('viewAny', User::class);
        $villageHeadRole = Role::query()->where('name', 'Village Head')->first();
        $currentVillageHead = $villageHeadRole === null ? null : User::query()
            ->with('roles')->whereHas('roles', fn ($query) => $query->where('roles.id', $villageHeadRole->getKey()))->first();
        $villageHeadCandidates = User::query()->where('account_status', 'active')->whereNotNull('email_verified_at')
            ->where('id', '!=', auth()->id())
            ->whereDoesntHave('roles', fn ($query) => $query->whereIn('name', self::PROTECTED_ROLES))
            ->orderBy('username')->get(['id', 'username']);

        return view('admin.users.village-head-replacement', compact('currentVillageHead', 'villageHeadCandidates'));
    }

    public function replaceVillageHead(Request $request, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('viewAny', User::class);
        $validated = $request->validate([
            'target_user_id' => ['required', 'integer', 'exists:users,id'],
            'current_password' => ['required', 'current_password'],
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        DB::transaction(function () use ($validated, $audit, $request): void {
            $role = Role::query()->where('name', 'Village Head')->lockForUpdate()->firstOrFail();
            $outgoingHead = User::query()->whereHas('roles', fn ($query) => $query->where('roles.id', $role->getKey()))
                ->lockForUpdate()->first();
            if ($outgoingHead === null) {
                throw ValidationException::withMessages(['target_user_id' => 'Belum ada Kepala Desa yang perlu diganti. Tetapkan pejabat pertama melalui pilihan peran.']);
            }

            $incomingHead = User::query()->whereKey($validated['target_user_id'])->lockForUpdate()->firstOrFail();
            if ((int) $incomingHead->getKey() === (int) $outgoingHead->getKey()) {
                throw ValidationException::withMessages(['target_user_id' => 'Pilih akun yang berbeda dari Kepala Desa saat ini.']);
            }
            if ((int) $incomingHead->getKey() === (int) $request->user()->getKey()
                || $incomingHead->account_status !== 'active'
                || ! $incomingHead->hasVerifiedEmail()
                || $incomingHead->roles()->whereIn('name', ['Super Admin', 'Village Head'])->exists()) {
                throw ValidationException::withMessages(['target_user_id' => 'Akun pengganti harus aktif, terverifikasi, bukan Super Admin, dan belum memegang peran Kepala Desa.']);
            }

            $outgoingHead->roles()->detach($role->getKey());
            $incomingHead->roles()->attach($role->getKey(), ['assigned_at' => now(), 'assigned_by' => $request->user()->getKey()]);
            $metadata = [
                'replacement_user_id' => (int) $incomingHead->getKey(),
                'reason' => trim($validated['reason']),
            ];
            $audit->record('authorization.village_head_role_removed', 'user', (int) $outgoingHead->getKey(), $metadata, $request);
            $audit->record('authorization.village_head_role_assigned', 'user', (int) $incomingHead->getKey(), [
                'previous_user_id' => (int) $outgoingHead->getKey(),
                'reason' => trim($validated['reason']),
            ], $request);
        });

        return redirect()->route('admin.users.village-head.replacement')->with('status', 'Peran Kepala Desa berhasil dialihkan. Peran tersebut sudah dicabut dari pejabat sebelumnya, sementara akun, riwayat, dan peran lainnya tetap tersimpan.');
    }

    public function updateRoles(Request $request, User $user, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('manageAccess', $user);

        $validated = $request->validate([
            'role_ids' => ['sometimes', 'array', 'max:7'],
            'role_ids.*' => ['integer', 'distinct', 'exists:roles,id'],
        ]);
        $roleIds = array_map('intval', $validated['role_ids'] ?? []);
        $roles = Role::query()->whereIn('id', $roleIds)->get();

        abort_if($roles->count() !== count($roleIds) || $roles->contains(fn (Role $role): bool => ! in_array($role->name, self::ASSIGNABLE_ROLES, true)), 422);

        DB::transaction(function () use ($user, $roleIds, $roles, $audit): void {
            $locked = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();
            $nextRoleNames = $roles->pluck('name');
            $sortedRoleNames = $nextRoleNames->sort()->values()->all();
            $allowedRoleSets = array_map(
                fn (array $roleSet): array => collect($roleSet)->sort()->values()->all(),
                [
                    ['Super Admin'], ['BUMDes Manager'], ['BUMDes Operator'], ['Village Admin'],
                    ['RT'], ['RW'], ['Resident'], ['Seller'], ['Village Head'],
                    ['RT', 'Resident'], ['RW', 'Resident'],
                ],
            );
            if (! in_array($sortedRoleNames, $allowedRoleSets, true)) {
                throw ValidationException::withMessages(['role_ids' => 'Pilih satu peran, atau gunakan hanya kombinasi RT + Resident atau RW + Resident.']);
            }

            if ($locked->roles()->whereIn('name', self::PROTECTED_ROLES)->exists()) {
                throw ValidationException::withMessages(['role_ids' => 'Peran Super Admin atau Village Head pada akun ini dilindungi dan tidak dapat diubah dari halaman ini.']);
            }

            if ($nextRoleNames->contains('Village Head')) {
                $headRole = Role::query()->where('name', 'Village Head')->lockForUpdate()->firstOrFail();
                if ($locked->account_status !== 'active' || ! $locked->hasVerifiedEmail()) {
                    throw ValidationException::withMessages(['role_ids' => 'Peran Kepala Desa hanya dapat diberikan kepada akun aktif dengan email terverifikasi.']);
                }

                $alreadyAssigned = DB::table('user_roles')->where('role_id', $headRole->getKey())->where('user_id', '!=', $locked->getKey())->exists();
                if ($alreadyAssigned) {
                    throw ValidationException::withMessages(['role_ids' => 'Peran Kepala Desa sudah diberikan kepada akun lain. Hubungi administrator untuk proses pergantian yang tercatat.']);
                }
            }

            $previousRoleNames = $locked->roles()->pluck('name');
            $removedRoleNames = $previousRoleNames->diff($nextRoleNames);
            $locked->roles()->detach();

            foreach ($roleIds as $roleId) {
                $locked->roles()->attach($roleId, ['assigned_at' => now(), 'assigned_by' => auth()->id()]);
            }

            foreach (['RT' => 'rt', 'RW' => 'rw'] as $roleName => $regionType) {
                if (! $removedRoleNames->contains($roleName)) {
                    continue;
                }

                $assignments = RegionOfficer::query()->where('user_id', $locked->getKey())->where('is_active', true)
                    ->whereHas('region', fn ($query) => $query->where('region_type', $regionType))->get();
                foreach ($assignments as $assignment) {
                    $assignment->forceFill(['is_active' => false, 'ended_at' => now()])->save();
                    $audit->record('authorization.region_revoked_role_removed', 'region_officer', (int) $assignment->getKey(), ['target_user_id' => (int) $locked->getKey()]);
                }
            }

            if (($removedRoleNames->contains('BUMDes Manager') || $removedRoleNames->contains('BUMDes Operator'))
                && ! $nextRoleNames->contains('BUMDes Manager')
                && ! $nextRoleNames->contains('BUMDes Operator')) {
                $staff = BumdesStaff::query()->where('user_id', $locked->getKey())->where('employment_status', 'active')->first();
                if ($staff !== null) {
                    $staff->employment_status = 'inactive';
                    $staff->save();
                    $audit->record('bumdes_staff.deactivated_after_role_removal', 'bumdes_staff', (int) $staff->getKey(), ['target_user_id' => (int) $locked->getKey()]);
                }
            }

            $audit->record('authorization.roles_changed', 'user', (int) $locked->getKey(), ['role_ids' => $roleIds]);
        });

        return back()->with('status', 'Peran akun berhasil diperbarui.');
    }

    public function updateStatus(Request $request, User $user, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('manageAccess', $user);
        $validated = $request->validate(['account_status' => ['required', 'in:active,suspended,disabled']]);

        $message = DB::transaction(function () use ($user, $validated, $audit): string {
            $locked = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();
            $oldStatus = $locked->account_status;
            $newStatus = $validated['account_status'];

            if ($oldStatus === 'pending') {
                throw ValidationException::withMessages([
                    'account_status' => 'Akun yang menunggu harus diaktifkan melalui halaman Aktivasi akun setelah email diverifikasi.',
                ]);
            }

            if ($newStatus === 'active' && ! $locked->hasVerifiedEmail()) {
                throw ValidationException::withMessages([
                    'account_status' => 'Email harus diverifikasi sebelum akun dapat diaktifkan.',
                ]);
            }

            if ($oldStatus === $newStatus) {
                return 'Status akun tidak berubah.';
            }

            $locked->account_status = $newStatus;

            if ($newStatus !== 'active') {
                $locked->setRememberToken(Str::random(60));
            }
            $locked->save();

            if ($newStatus !== 'active' && Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id')) {
                DB::table('sessions')->where('user_id', $locked->getKey())->delete();
            }

            if ($newStatus === 'disabled') {
                $assignments = RegionOfficer::query()->where('user_id', $locked->getKey())->where('is_active', true)->get();
                foreach ($assignments as $assignment) {
                    $assignment->forceFill(['is_active' => false, 'ended_at' => now()])->save();
                    $audit->record('authorization.region_revoked_account_disabled', 'region_officer', (int) $assignment->getKey(), ['target_user_id' => (int) $locked->getKey()]);
                }
            }

            $audit->record('account.status_changed', 'user', (int) $locked->getKey(), ['from' => $oldStatus, 'to' => $newStatus]);

            return match ($newStatus) {
                'suspended' => 'Akun ditangguhkan. Login dan sesi lama dihentikan sementara; penugasan wilayah tetap tersedia untuk pemulihan.',
                'disabled' => 'Akun dinonaktifkan. Sesi dan penugasan wilayah aktif dicabut.',
                default => $oldStatus === 'disabled'
                    ? 'Akun diaktifkan. Buat kembali penugasan RT/RW yang diperlukan.'
                    : 'Akun diaktifkan kembali.',
            };
        });

        return back()->with('status', $message);
    }

    public function destroy(Request $request, User $user, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('manageAccess', $user);

        if (! Schema::hasColumn('region_officers', 'former_user_id')) {
            throw ValidationException::withMessages([
                'account_delete' => 'Skema database belum diperbarui. Jalankan `php artisan migrate`, lalu coba hapus akun kembali.',
            ]);
        }

        DB::transaction(function () use ($user, $audit): void {
            $locked = User::query()->whereKey($user->getKey())->lockForUpdate()->firstOrFail();
            if ($locked->roles()->whereIn('name', self::PROTECTED_ROLES)->exists()) {
                throw ValidationException::withMessages([
                    'account_delete' => 'Akun dengan peran Super Admin atau Village Head tidak dapat dihapus dari halaman ini.',
                ]);
            }

            if (($reason = $this->accountDeletionBlocker($locked)) !== null) {
                throw ValidationException::withMessages(['account_delete' => $reason]);
            }

            $userId = (int) $locked->getKey();
            $email = $locked->email;
            $resident = $locked->resident;
            $locked->regionOfficerAssignments()->update(['former_user_id' => $userId, 'user_id' => null]);
            $audit->record('account.deleted', 'user', $userId, ['target_user_id' => $userId]);

            if ($resident !== null) {
                $audit->record('resident.profile_deleted_with_account', 'resident', (int) $resident->getKey(), ['target_user_id' => $userId]);
                $resident->delete();
            }

            if (Schema::hasTable('notifications')) {
                DB::table('notifications')->where('user_id', $userId)->delete();
            }
            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->where('user_id', $userId)->delete();
            }
            if ($email !== null && Schema::hasTable('password_reset_tokens')) {
                DB::table('password_reset_tokens')->where('email', $email)->delete();
            }

            $locked->roles()->detach();
            $locked->delete();
        });

        return back()->with('status', 'Akun berhasil dihapus. Riwayat audit tetap tersimpan.');
    }

    private function accountDeletionBlocker(User $user): ?string
    {
        $resident = $user->resident()->first();
        if ($resident !== null && Schema::hasTable('letter_requests')
            && DB::table('letter_requests')->where('resident_id', $resident->getKey())->exists()) {
            return 'Profil warga akun ini masih terhubung dengan riwayat permohonan surat. Akun harus dinonaktifkan agar riwayat layanan tetap terjaga.';
        }
        if ($user->bumdesStaff()->exists()) {
            return 'Akun memiliki data staf BUMDes. Nonaktifkan akun agar riwayat staf tetap terjaga.';
        }

        foreach ([
            ['letter_requests', 'created_by', 'riwayat permohonan surat'],
            ['letter_approvals', 'reviewer_id', 'riwayat pemeriksaan surat'],
            ['letter_request_versions', 'changed_by', 'riwayat perubahan surat'],
            ['letter_attachments', 'uploaded_by', 'lampiran surat'],
            ['letter_documents', 'signed_by', 'dokumen surat'],
        ] as [$table, $column, $label]) {
            if (Schema::hasTable($table) && DB::table($table)->where($column, $user->getKey())->exists()) {
                return 'Akun memiliki '.$label.'. Nonaktifkan akun agar catatan dan dokumen tetap terjaga.';
            }
        }

        return null;
    }
}
