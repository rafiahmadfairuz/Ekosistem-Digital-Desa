<?php

namespace Tests\Feature;

use App\Models\BumdesStaff;
use App\Models\LetterRequest;
use App\Models\LetterType;
use App\Models\Region;
use App\Models\RegionOfficer;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use App\Services\IdentityDataProtector;
use App\Services\ResidentProfileData;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_unassigned_rt_cannot_list_residents(): void
    {
        $this->seed(RolesSeeder::class);
        $rt = $this->userWithRoles(['RT']);

        $this->actingAs($rt)->get(route('officer.residents.index'))->assertForbidden();
    }

    public function test_an_account_can_hold_multiple_roles(): void
    {
        $this->seed(RolesSeeder::class);
        $user = $this->userWithRoles(['Resident', 'RT']);

        $this->assertTrue($user->hasRole('Resident'));
        $this->assertTrue($user->hasRole('RT'));
        $this->assertCount(2, $user->roles);
    }

    public function test_region_codes_follow_village_and_parent_rw_scopes(): void
    {
        $this->seed(RolesSeeder::class);
        [$village, $rwOne] = $this->regions();
        $admin = $this->userWithRoles(['Super Admin']);

        $this->actingAs($admin)->post(route('admin.regions.rw.store'), [
            'village_id' => $village->id,
            'region_code' => 'RW-02',
            'name' => 'RW 02',
        ])->assertRedirect();

        $rwTwo = Region::query()->where('village_id', $village->id)->where('region_type', 'rw')
            ->where('region_code', 'RW-02')->firstOrFail();
        $this->actingAs($admin)->post(route('admin.regions.rt.store'), [
            'parent_id' => $rwTwo->id,
            'region_code' => '01/01',
            'name' => 'RT 01',
        ])->assertRedirect();

        $this->assertDatabaseHas('regions', [
            'village_id' => $village->id,
            'parent_id' => $rwOne->id,
            'region_type' => 'rt',
            'region_code' => '01/01',
        ]);
        $this->assertDatabaseHas('regions', [
            'village_id' => $village->id,
            'parent_id' => $rwTwo->id,
            'region_type' => 'rt',
            'region_code' => '01/01',
        ]);

        $this->actingAs($admin)->post(route('admin.regions.rt.store'), [
            'parent_id' => $rwTwo->id,
            'region_code' => '01/01',
            'name' => 'RT duplikat',
        ])->assertSessionHasErrors('region_code');
        $this->actingAs($admin)->post(route('admin.regions.rw.store'), [
            'village_id' => $village->id,
            'region_code' => 'RW-02',
            'name' => 'RW duplikat',
        ])->assertSessionHasErrors('region_code');
    }

    public function test_super_admin_can_edit_and_delete_an_empty_village(): void
    {
        $this->seed(RolesSeeder::class);
        $admin = $this->userWithRoles(['Super Admin']);
        $village = Village::query()->create(['code' => 'OLD01', 'name' => 'Desa Lama', 'is_active' => true]);

        $this->actingAs($admin)->get(route('admin.villages.edit', $village))->assertOk()->assertSee('Desa Lama');
        $this->actingAs($admin)->put(route('admin.villages.update', $village), [
            'code' => 'NEW01',
            'name' => 'Desa Baru',
            'address' => 'Alamat kantor desa',
            'postal_code' => '12345',
            'phone' => '081234567890',
            'email' => 'desa@example.test',
        ])->assertRedirect(route('admin.village-settings.index'));

        $this->assertDatabaseHas('villages', [
            'id' => $village->id, 'code' => 'NEW01', 'name' => 'Desa Baru',
            'address' => 'Alamat kantor desa', 'postal_code' => '12345',
        ]);
        $this->actingAs($admin)->delete(route('admin.villages.destroy', $village->fresh()))
            ->assertRedirect(route('admin.village-settings.index'));
        $this->assertDatabaseMissing('villages', ['id' => $village->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'master_data.village_updated', 'entity_id' => $village->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'master_data.village_deleted', 'entity_id' => $village->id]);
    }

    public function test_village_with_region_records_cannot_be_deleted(): void
    {
        $this->seed(RolesSeeder::class);
        [$village] = $this->regions();
        $admin = $this->userWithRoles(['Super Admin']);

        $this->actingAs($admin)->delete(route('admin.villages.destroy', $village))
            ->assertSessionHasErrors('village');

        $this->assertDatabaseHas('villages', ['id' => $village->id]);
        $this->assertDatabaseHas('regions', ['village_id' => $village->id]);
    }

    public function test_super_admin_can_delete_unused_rt_and_rw_in_child_first_order(): void
    {
        $this->seed(RolesSeeder::class);
        [$village, $rw, $rtOne, $rtTwo] = $this->regions();
        $admin = $this->userWithRoles(['Super Admin']);

        $this->actingAs($admin)->delete(route('admin.regions.destroy', $rw))
            ->assertSessionHasErrors('region');
        $this->actingAs($admin)->delete(route('admin.regions.destroy', $rtOne))
            ->assertRedirect(route('admin.village-settings.index'));
        $this->actingAs($admin)->delete(route('admin.regions.destroy', $rtTwo))
            ->assertRedirect(route('admin.village-settings.index'));
        $this->actingAs($admin)->delete(route('admin.regions.destroy', $rw->fresh()))
            ->assertRedirect(route('admin.village-settings.index'));

        $this->assertDatabaseMissing('regions', ['id' => $rtOne->id]);
        $this->assertDatabaseMissing('regions', ['id' => $rtTwo->id]);
        $this->assertDatabaseMissing('regions', ['id' => $rw->id]);
        $this->assertDatabaseHas('villages', ['id' => $village->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'master_data.region_deleted', 'entity_id' => $rtOne->id]);
    }

    public function test_region_delete_is_blocked_by_residents_but_preserves_officer_history(): void
    {
        $this->seed(RolesSeeder::class);
        [, , $rtOne, $rtTwo] = $this->regions();
        $admin = $this->userWithRoles(['Super Admin']);
        $owner = $this->userWithRoles(['Resident']);
        $this->resident($owner, $rtOne, 'Warga dengan wilayah', true);
        $officer = $this->userWithRoles(['RT']);
        $assignment = RegionOfficer::query()->create([
            'user_id' => $officer->id, 'region_id' => $rtTwo->id, 'assigned_at' => now(),
            'assigned_by' => null, 'is_active' => true, 'ended_at' => null,
        ]);

        $this->actingAs($admin)->delete(route('admin.regions.destroy', $rtOne))->assertSessionHasErrors('region');
        $this->actingAs($admin)->delete(route('admin.regions.destroy', $rtTwo))
            ->assertRedirect(route('admin.village-settings.index'));
        $this->assertDatabaseHas('regions', ['id' => $rtOne->id]);
        $this->assertDatabaseMissing('regions', ['id' => $rtTwo->id]);
        $this->assertDatabaseHas('region_officers', [
            'id' => $assignment->id,
            'region_id' => null,
            'former_region_id' => $rtTwo->id,
            'former_region_name' => $rtTwo->name,
            'former_region_parent_name' => $rtTwo->parent->name,
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'authorization.region_assignment_history_detached',
            'entity_id' => $assignment->id,
        ]);
    }

    public function test_village_admin_can_manage_only_the_single_active_village_record(): void
    {
        $this->seed(RolesSeeder::class);
        $village = Village::query()->create(['code' => 'ADMIN01', 'name' => 'Desa Uji', 'is_active' => true]);
        $inactiveVillage = Village::query()->create(['code' => 'ADMIN02', 'name' => 'Desa Nonaktif', 'is_active' => false]);
        $villageAdmin = $this->userWithRoles(['Village Admin']);

        $this->actingAs($villageAdmin)->get(route('admin.village-settings.index'))->assertOk()->assertSee('Desa Uji')->assertDontSee('Desa Nonaktif');
        $this->actingAs($villageAdmin)->get(route('admin.regions.index'))->assertForbidden();
        $this->actingAs($villageAdmin)->get(route('admin.villages.edit', $village))->assertOk();
        $this->actingAs($villageAdmin)->get(route('admin.villages.edit', $inactiveVillage))->assertForbidden();
        $this->actingAs($villageAdmin)->put(route('admin.villages.update', $village), [
            'code' => 'ADMIN03', 'name' => 'Desa Uji Baru',
        ])->assertRedirect(route('admin.village-settings.index'));
        $this->actingAs($villageAdmin)->post(route('admin.regions.rw.store'), [
            'village_id' => $village->id, 'region_code' => 'RW-01', 'name' => 'RW 01',
        ])->assertForbidden();
        $this->actingAs($villageAdmin)->delete(route('admin.regions.destroy', Region::query()->create([
            'village_id' => $village->id,
            'parent_id' => null,
            'region_type' => 'rw',
            'region_code' => 'RW-01',
            'name' => 'RW 01',
        ])))->assertForbidden();

        $this->assertDatabaseHas('villages', ['id' => $village->id, 'name' => 'Desa Uji Baru']);
        $this->assertDatabaseHas('villages', ['id' => $inactiveVillage->id, 'name' => 'Desa Nonaktif']);
    }

    public function test_rt_only_sees_verified_residents_in_the_assigned_rt(): void
    {
        $this->seed(RolesSeeder::class);
        [$village, $rw, $rtOne, $rtTwo] = $this->regions();
        $officer = $this->userWithRoles(['RT']);
        RegionOfficer::query()->create([
            'user_id' => $officer->id, 'region_id' => $rtOne->id, 'assigned_at' => now(),
            'assigned_by' => null, 'is_active' => true,
        ]);
        $ownerOne = $this->userWithRoles(['Resident']);
        $ownerTwo = $this->userWithRoles(['Resident']);
        $ownerUnverified = $this->userWithRoles(['Resident']);
        $one = $this->resident($ownerOne, $rtOne, 'Warga RT Satu', true);
        $two = $this->resident($ownerTwo, $rtTwo, 'Warga RT Dua', true);
        $this->resident($ownerUnverified, $rtOne, 'Belum Terverifikasi', false);
        app(ResidentProfileData::class)->apply($one, [
            'phone' => '+628123456789',
            'address' => 'Alamat rahasia', 'birth_date' => '2000-01-02', 'gender' => 'female',
        ]);
        $one->save();

        $this->actingAs($ownerOne)->get(route('profile.show'))
            ->assertOk()->assertDontSee('NIK')->assertSee('Alamat rahasia')->assertSee('+628123456789');
        $this->actingAs($ownerOne)->get(route('profile.edit'))->assertOk()->assertDontSee('national_id')->assertDontSee('NIK');

        $this->actingAs($officer)->get(route('officer.residents.index'))
            ->assertOk()->assertSee('Warga RT Satu')->assertDontSee('Warga RT Dua')->assertDontSee('Belum Terverifikasi');
        $this->actingAs($officer)->get(route('officer.residents.show', $one))->assertOk()->assertSee('Warga RT Satu')
            ->assertDontSee('1234567890123456')->assertDontSee('Alamat rahasia')->assertDontSee('+628123456789')->assertDontSee('2000-01-02')->assertDontSee('female');
        $this->assertNull($one->fresh()->birth_date);
        $this->assertNull($one->fresh()->gender);
        $this->actingAs($officer)->get(route('officer.residents.show', $two))->assertNotFound();
    }

    public function test_rw_scope_is_limited_to_child_rt_regions_in_same_village(): void
    {
        $this->seed(RolesSeeder::class);
        [, $rwOne, , $rtChild] = $this->regions();
        $otherVillage = Village::query()->create(['code' => 'TEST002', 'name' => 'Desa Uji 2']);
        $rwOtherVillage = Region::query()->create([
            'village_id' => $otherVillage->id, 'parent_id' => null, 'region_type' => 'rw',
            'region_code' => '02', 'name' => 'RW 02',
        ]);
        $rtOtherVillage = Region::query()->create([
            'village_id' => $otherVillage->id, 'parent_id' => $rwOtherVillage->id, 'region_type' => 'rt',
            'region_code' => '02/01', 'name' => 'RT 01',
        ]);
        $rwOfficer = $this->userWithRoles(['RW']);
        RegionOfficer::query()->create([
            'user_id' => $rwOfficer->id, 'region_id' => $rwOne->id, 'assigned_at' => now(),
            'assigned_by' => null, 'is_active' => true,
        ]);
        $localOwner = $this->userWithRoles(['Resident']);
        $foreignOwner = $this->userWithRoles(['Resident']);
        $this->resident($localOwner, $rtChild, 'Warga Lokal', true);
        $this->resident($foreignOwner, $rtOtherVillage, 'Warga Desa Lain', true);

        $this->actingAs($rwOfficer)->get(route('officer.residents.index'))
            ->assertOk()->assertSee('Warga Lokal')->assertDontSee('Warga Desa Lain');
    }

    public function test_revoked_rt_assignment_removes_access_immediately(): void
    {
        $this->seed(RolesSeeder::class);
        [, , $rtRegion] = $this->regions();
        $officer = $this->userWithRoles(['RT']);
        $assignment = RegionOfficer::query()->create([
            'user_id' => $officer->id, 'region_id' => $rtRegion->id, 'assigned_at' => now(),
            'assigned_by' => null, 'is_active' => true,
        ]);
        $owner = $this->userWithRoles(['Resident']);
        $resident = $this->resident($owner, $rtRegion, 'Warga Satu', true);

        $assignment->forceFill(['is_active' => false, 'ended_at' => now()])->save();

        $this->actingAs($officer)->get(route('officer.residents.show', $resident))->assertNotFound();
    }

    public function test_removing_rt_role_revokes_all_rt_region_assignments(): void
    {
        $this->seed(RolesSeeder::class);
        [, , $rtRegion] = $this->regions();
        $superAdmin = $this->userWithRoles(['Super Admin']);
        $officer = $this->userWithRoles(['RT']);
        $assignment = RegionOfficer::query()->create([
            'user_id' => $officer->id, 'region_id' => $rtRegion->id, 'assigned_at' => now(),
            'assigned_by' => $superAdmin->id, 'is_active' => true,
        ]);
        $residentOwner = $this->userWithRoles(['Resident']);
        $resident = $this->resident($residentOwner, $rtRegion, 'Warga RT', true);
        $residentRoleId = Role::query()->where('name', 'Resident')->value('id');

        $this->actingAs($superAdmin)->put(route('admin.users.roles.update', $officer), ['role_ids' => [$residentRoleId]])
            ->assertRedirect();

        $this->assertFalse((bool) $assignment->fresh()->is_active);
        $this->actingAs($officer)->get(route('officer.residents.show', $resident))->assertForbidden();
    }

    public function test_village_admin_verification_is_required_before_regional_access(): void
    {
        $this->seed(RolesSeeder::class);
        [, , $rtRegion] = $this->regions();
        $officer = $this->userWithRoles(['RT']);
        RegionOfficer::query()->create([
            'user_id' => $officer->id, 'region_id' => $rtRegion->id, 'assigned_at' => now(),
            'assigned_by' => null, 'is_active' => true,
        ]);
        $owner = $this->userWithRoles(['Resident']);
        $resident = $this->resident($owner, $rtRegion, 'Warga Menunggu', false);
        $villageAdmin = $this->userWithRoles(['Village Admin']);

        $this->actingAs($officer)->get(route('officer.residents.index'))->assertOk()->assertDontSee('Warga Menunggu');
        $this->actingAs($villageAdmin)->post(route('admin.resident-region-verification.verify', $resident))->assertRedirect();
        $this->assertNotNull($resident->fresh()->region_verified_at);
        $this->actingAs($officer)->get(route('officer.residents.index'))->assertSee('Warga Menunggu');
    }

    public function test_resident_cannot_view_another_residents_profile(): void
    {
        $this->seed(RolesSeeder::class);
        [, , $rtRegion] = $this->regions();
        $owner = $this->userWithRoles(['Resident']);
        $other = $this->userWithRoles(['Resident']);
        $resident = $this->resident($owner, $rtRegion, 'Warga Rahasia', true);

        $this->actingAs($other)->get(route('officer.residents.show', $resident))->assertForbidden();
        $this->actingAs($other)->get(route('profile.show'))->assertRedirect(route('profile.create'));
    }

    public function test_profile_update_cannot_change_owner_region_status_or_roles(): void
    {
        $this->seed(RolesSeeder::class);
        [, , $rtOne, $rtTwo] = $this->regions();
        $owner = $this->userWithRoles(['Resident']);
        $other = $this->userWithRoles(['Resident']);
        $resident = $this->resident($owner, $rtOne, 'Nama Awal', true);
        $this->actingAs($owner)->put(route('profile.update'), [
            'full_name' => 'Nama Baru',
            'national_id' => '1234567890123456',
            'region_id' => $rtTwo->id,
            'user_id' => $other->id,
            'account_status' => 'disabled',
            'role_ids' => [Role::query()->where('name', 'Super Admin')->value('id')],
        ])->assertRedirect(route('profile.show'));

        $this->assertSame($owner->id, $resident->fresh()->user_id);
        $this->assertSame($rtOne->id, $resident->fresh()->region_id);
        $this->assertSame('active', $owner->fresh()->account_status);
        $this->assertFalse($owner->fresh()->hasRole('Super Admin'));
        $this->assertSame('Nama Baru', app(ResidentProfileData::class)->visibleName($resident->fresh()));
        $this->assertFalse(Schema::hasColumn('residents', 'national_id_ciphertext'));
    }

    public function test_resident_cannot_submit_a_non_rt_candidate_region(): void
    {
        $this->seed(RolesSeeder::class);
        [, $rw] = $this->regions();
        $residentUser = $this->userWithRoles(['Resident']);

        $this->actingAs($residentUser)->post(route('profile.store'), [
            'full_name' => 'Nama Contoh', 'region_id' => $rw->id,
        ])->assertUnprocessable();

        $this->assertDatabaseCount('residents', 0);
    }

    public function test_bumdes_operator_has_no_staff_management_permission(): void
    {
        $this->seed(RolesSeeder::class);
        $operator = $this->userWithRoles(['BUMDes Operator']);
        $staff = BumdesStaff::query()->create(['user_id' => $operator->id, 'employment_status' => 'active']);

        $this->actingAs($operator)->get(route('bumdes.staff.index'))->assertForbidden();
        $this->actingAs($operator)->post(route('bumdes.staff.store'), [
            'user_id' => $operator->id, 'department' => 'Uji',
        ])->assertForbidden();
        $this->actingAs($operator)->delete(route('bumdes.staff.destroy', $staff))->assertForbidden();

        $this->assertDatabaseHas('bumdes_staff', ['id' => $staff->id]);
    }

    public function test_bumdes_manager_can_manage_staff_without_granting_roles(): void
    {
        $this->seed(RolesSeeder::class);
        $manager = $this->userWithRoles(['BUMDes Manager']);
        $operator = $this->userWithRoles(['BUMDes Operator']);

        $this->actingAs($manager)->post(route('bumdes.staff.store'), [
            'user_id' => $operator->id,
            'department' => 'Layanan warga',
            'position' => 'Operator layanan',
        ])->assertRedirect();

        $this->assertDatabaseHas('bumdes_staff', [
            'user_id' => $operator->id,
            'department' => 'Layanan warga',
            'employment_status' => 'active',
        ]);
        $this->assertSame(['BUMDes Operator'], $operator->roles()->pluck('name')->all());
    }

    public function test_bumdes_manager_can_delete_staff_profile_without_deleting_account_or_role(): void
    {
        $this->seed(RolesSeeder::class);
        $manager = $this->userWithRoles(['BUMDes Manager']);
        $operator = $this->userWithRoles(['BUMDes Operator']);
        $staff = BumdesStaff::query()->create([
            'user_id' => $operator->id,
            'department' => 'Operasional',
            'position' => 'Staf',
            'employment_status' => 'inactive',
        ]);

        $this->actingAs($manager)->delete(route('bumdes.staff.destroy', $staff))->assertRedirect();

        $this->assertDatabaseMissing('bumdes_staff', ['id' => $staff->id]);
        $this->assertDatabaseHas('users', ['id' => $operator->id]);
        $this->assertSame(['BUMDes Operator'], $operator->fresh()->roles()->pluck('name')->all());
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'bumdes_staff.profile_deleted',
            'entity_type' => 'bumdes_staff',
            'entity_id' => $staff->id,
        ]);
    }

    public function test_super_admin_cannot_change_their_own_roles_and_village_head_assignment_is_controlled(): void
    {
        $this->seed(RolesSeeder::class);
        $superAdmin = $this->userWithRoles(['Super Admin']);
        $roleId = Role::query()->where('name', 'Resident')->value('id');

        $this->actingAs($superAdmin)->put(route('admin.users.roles.update', $superAdmin), ['role_ids' => [$roleId]])->assertForbidden();

        $target = $this->userWithRoles(['Resident']);
        $villageHeadRoleId = Role::query()->where('name', 'Village Head')->value('id');
        $this->actingAs($superAdmin)->put(route('admin.users.roles.update', $target), ['role_ids' => [$villageHeadRoleId]])->assertRedirect();
        $this->assertTrue($target->fresh()->hasRole('Village Head'));

        $secondTarget = $this->userWithRoles(['Resident']);
        $this->actingAs($superAdmin)->put(route('admin.users.roles.update', $secondTarget), ['role_ids' => [$villageHeadRoleId]])
            ->assertSessionHasErrors('role_ids');
        $this->assertFalse($secondTarget->fresh()->hasRole('Village Head'));
    }

    public function test_suspending_an_account_revokes_sessions_but_keeps_assignments_for_reactivation(): void
    {
        $this->seed(RolesSeeder::class);
        [, , $rt] = $this->regions();
        $admin = $this->userWithRoles(['Super Admin']);
        $target = $this->userWithRoles(['RT']);
        $assignment = RegionOfficer::query()->create([
            'user_id' => $target->id, 'region_id' => $rt->id, 'assigned_at' => now(),
            'assigned_by' => $admin->id, 'is_active' => true,
        ]);
        DB::table('sessions')->insert([
            'id' => 'account-status-session', 'user_id' => $target->id,
            'ip_address' => '127.0.0.1', 'user_agent' => 'test', 'payload' => 'test', 'last_activity' => time(),
        ]);

        $this->actingAs($admin)->put(route('admin.users.status.update', $target), ['account_status' => 'suspended'])
            ->assertRedirect()->assertSessionHas('status');

        $this->assertSame('suspended', $target->fresh()->account_status);
        $this->assertDatabaseHas('region_officers', ['id' => $assignment->id, 'is_active' => true, 'ended_at' => null]);
        $this->assertDatabaseMissing('sessions', ['id' => 'account-status-session']);

        $this->actingAs($admin)->put(route('admin.users.status.update', $target->fresh()), ['account_status' => 'active'])
            ->assertRedirect()->assertSessionHas('status');
        $this->assertSame('active', $target->fresh()->account_status);
        $this->assertDatabaseHas('region_officers', ['id' => $assignment->id, 'is_active' => true]);
    }

    public function test_disabling_an_account_ends_assignments_and_reactivation_does_not_restore_them(): void
    {
        $this->seed(RolesSeeder::class);
        [, , $rt] = $this->regions();
        $admin = $this->userWithRoles(['Super Admin']);
        $target = $this->userWithRoles(['RT']);
        $assignment = RegionOfficer::query()->create([
            'user_id' => $target->id, 'region_id' => $rt->id, 'assigned_at' => now(),
            'assigned_by' => $admin->id, 'is_active' => true,
        ]);

        $this->actingAs($admin)->put(route('admin.users.status.update', $target), ['account_status' => 'disabled'])
            ->assertRedirect();
        $this->assertSame('disabled', $target->fresh()->account_status);
        $this->assertDatabaseHas('region_officers', ['id' => $assignment->id, 'is_active' => false]);

        $this->actingAs($admin)->put(route('admin.users.status.update', $target->fresh()), ['account_status' => 'active'])
            ->assertRedirect()->assertSessionHas('status');
        $this->assertSame('active', $target->fresh()->account_status);
        $this->assertDatabaseHas('region_officers', ['id' => $assignment->id, 'is_active' => false]);
    }

    public function test_pending_account_status_can_only_change_through_activation_flow(): void
    {
        $this->seed(RolesSeeder::class);
        $admin = $this->userWithRoles(['Super Admin']);
        $pending = User::factory()->create(['account_status' => 'pending']);
        $pending->roles()->attach(Role::query()->where('name', 'Resident')->value('id'));

        $this->actingAs($admin)->put(route('admin.users.status.update', $pending), ['account_status' => 'active'])
            ->assertSessionHasErrors('account_status');
        $this->assertSame('pending', $pending->fresh()->account_status);
        $this->actingAs($admin)->get(route('admin.users.manage'))->assertOk()->assertSee('Menunggu aktivasi')
            ->assertDontSee('status-'.$pending->id);
    }

    public function test_super_admin_can_delete_an_unlinked_account_and_audit_the_action(): void
    {
        $this->seed(RolesSeeder::class);
        $admin = $this->userWithRoles(['Super Admin']);
        $target = $this->userWithRoles(['Resident']);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $target))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
        $this->assertDatabaseMissing('user_roles', ['user_id' => $target->id]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'account.deleted',
            'entity_type' => 'user',
            'entity_id' => $target->id,
        ]);
    }

    public function test_account_with_unreferenced_resident_profile_can_be_deleted(): void
    {
        $this->seed(RolesSeeder::class);
        [, , $rt] = $this->regions();
        $admin = $this->userWithRoles(['Super Admin']);
        $target = $this->userWithRoles(['Resident']);
        $resident = $this->resident($target, $rt, 'Warga Uji', true);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $target))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
        $this->assertDatabaseMissing('residents', ['id' => $resident->id]);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'resident.profile_deleted_with_account',
            'entity_id' => $resident->id,
        ]);
    }

    public function test_account_with_resident_correspondence_history_cannot_be_deleted(): void
    {
        $this->seed(RolesSeeder::class);
        [, , $rt] = $this->regions();
        $admin = $this->userWithRoles(['Super Admin']);
        $target = $this->userWithRoles(['Resident']);
        $resident = $this->resident($target, $rt, 'Warga Uji', true);
        $type = LetterType::query()->create([
            'code' => 'SURAT_UJI', 'name' => 'Surat Uji', 'is_active' => true,
        ]);
        LetterRequest::query()->create([
            'resident_id' => $resident->id,
            'created_by' => $target->id,
            'letter_type_id' => $type->id,
            'request_number' => 'REQ-UJI-001',
            'submission_channel' => 'self_service',
            'encrypted_data' => ['ciphertext' => 'test'],
            'letter_type_snapshot' => ['code' => 'SURAT_UJI', 'name' => 'Surat Uji'],
            'status' => 'draft',
        ]);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $target))
            ->assertSessionHasErrors('account_delete');

        $this->assertDatabaseHas('users', ['id' => $target->id]);
        $this->assertDatabaseHas('residents', ['id' => $resident->id]);
        $this->assertDatabaseMissing('audit_logs', [
            'action' => 'account.deleted',
            'entity_id' => $target->id,
        ]);
    }

    public function test_disabled_account_can_be_deleted_while_region_assignment_history_is_preserved(): void
    {
        $this->seed(RolesSeeder::class);
        [, , $rt] = $this->regions();
        $admin = $this->userWithRoles(['Super Admin']);
        $target = $this->userWithRoles(['RT']);
        $assignment = RegionOfficer::query()->create([
            'user_id' => $target->id,
            'region_id' => $rt->id,
            'assigned_at' => now()->subDay(),
            'assigned_by' => $admin->id,
            'is_active' => false,
            'ended_at' => now(),
        ]);
        $target->update(['account_status' => 'disabled']);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $target))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('users', ['id' => $target->id]);
        $this->assertDatabaseHas('region_officers', [
            'id' => $assignment->id,
            'user_id' => null,
            'former_user_id' => $target->id,
            'is_active' => false,
        ]);
    }

    public function test_super_admin_cannot_delete_their_own_or_protected_accounts(): void
    {
        $this->seed(RolesSeeder::class);
        $admin = $this->userWithRoles(['Super Admin']);
        $villageHead = $this->userWithRoles(['Village Head']);

        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))->assertForbidden();
        $this->actingAs($admin)->delete(route('admin.users.destroy', $villageHead))
            ->assertSessionHasErrors('account_delete');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
        $this->assertDatabaseHas('users', ['id' => $villageHead->id]);
    }

    /** @param list<string> $roles */
    private function userWithRoles(array $roles): User
    {
        $user = User::factory()->create(['account_status' => 'active']);
        foreach ($roles as $role) {
            $user->roles()->attach(Role::query()->where('name', $role)->value('id'));
        }

        return $user;
    }

    /** @return array{Village, Region, Region, Region} */
    private function regions(): array
    {
        $village = Village::query()->create(['code' => 'TEST001', 'name' => 'Desa Uji']);
        $rw = Region::query()->create(['village_id' => $village->id, 'parent_id' => null, 'region_type' => 'rw', 'region_code' => '01', 'name' => 'RW 01']);
        $rtOne = Region::query()->create(['village_id' => $village->id, 'parent_id' => $rw->id, 'region_type' => 'rt', 'region_code' => '01/01', 'name' => 'RT 01']);
        $rtTwo = Region::query()->create(['village_id' => $village->id, 'parent_id' => $rw->id, 'region_type' => 'rt', 'region_code' => '01/02', 'name' => 'RT 02']);

        return [$village, $rw, $rtOne, $rtTwo];
    }

    private function resident(User $owner, Region $region, string $name, bool $verified): Resident
    {
        $encrypted = app(IdentityDataProtector::class)->encrypt($name);
        $resident = new Resident;
        $resident->forceFill([
            'user_id' => $owner->id,
            'region_id' => $region->id,
            'full_name_ciphertext' => $encrypted['ciphertext'],
            'full_name_nonce' => $encrypted['nonce'],
            'full_name_tag' => $encrypted['tag'],
            'region_verified_at' => $verified ? now() : null,
        ])->save();

        return $resident;
    }
}
