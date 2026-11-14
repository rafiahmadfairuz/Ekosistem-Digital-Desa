<?php

namespace Tests\Feature;

use App\Models\Region;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegionAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    public function test_only_super_admin_can_assign_regions_and_role_must_match_region_type(): void
    {
        $village = Village::query()->create(['name' => 'Desa Uji', 'code' => 'DU', 'is_active' => true]);
        $rw = Region::query()->create(['village_id' => $village->getKey(), 'region_type' => 'rw', 'region_code' => '01', 'name' => 'RW 01']);
        $rt = Region::query()->create(['village_id' => $village->getKey(), 'parent_id' => $rw->getKey(), 'region_type' => 'rt', 'region_code' => '01', 'name' => 'RT 01']);
        $officer = $this->userWithRoles(['RT', 'Resident']);
        $admin = $this->userWithRoles(['Super Admin']);

        $this->actingAs($admin)->post(route('admin.region-assignments.store'), [
            'user_id' => $officer->getKey(), 'region_id' => $rt->getKey(),
        ])->assertRedirect();
        $this->assertDatabaseHas('region_officers', ['user_id' => $officer->getKey(), 'region_id' => $rt->getKey(), 'is_active' => true]);

        $this->from(route('admin.region-assignments.index'))->post(route('admin.region-assignments.store'), [
            'user_id' => $officer->getKey(), 'region_id' => $rw->getKey(),
        ])->assertUnprocessable();

        $resident = $this->userWithRoles(['Resident']);
        $this->from(route('admin.region-assignments.index'))->post(route('admin.region-assignments.store'), [
            'user_id' => $resident->getKey(), 'region_id' => $rt->getKey(),
        ])->assertUnprocessable();

        $otherAdmin = $this->userWithRoles(['Village Admin']);
        $this->actingAs($otherAdmin)->post(route('admin.region-assignments.store'), [
            'user_id' => $officer->getKey(), 'region_id' => $rt->getKey(),
        ])->assertForbidden();
    }

    public function test_region_choices_are_filtered_after_account_selection(): void
    {
        $village = Village::query()->create(['name' => 'Desa Uji', 'code' => 'DU', 'is_active' => true]);
        $rw = Region::query()->create(['village_id' => $village->getKey(), 'region_type' => 'rw', 'region_code' => '01', 'name' => 'RW 01']);
        Region::query()->create(['village_id' => $village->getKey(), 'parent_id' => $rw->getKey(), 'region_type' => 'rt', 'region_code' => '01', 'name' => 'RT 01']);
        $admin = $this->userWithRoles(['Super Admin']);

        $this->actingAs($admin)->get(route('admin.region-assignments.index'))
            ->assertOk()
            ->assertSee('Pilih akun terlebih dahulu…')
            ->assertSee('const regionOptions', false)
            ->assertSee("type: 'RT'", false)
            ->assertSee("type: 'RW'", false)
            ->assertSee("roles.includes('RT')", false);
    }

    /** @param list<string> $roles */
    private function userWithRoles(array $roles): User
    {
        $user = User::factory()->active()->create();
        $user->roles()->attach(Role::query()->whereIn('name', $roles)->pluck('id'));

        return $user->load('roles');
    }
}
