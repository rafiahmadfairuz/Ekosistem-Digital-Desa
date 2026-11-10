<?php

namespace Tests\Feature;

use App\Models\BumdesStaff;
use App\Models\Role;
use App\Models\User;
use App\Services\NavigationMenu;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_resident_sees_profile_and_correspondence_navigation_without_administrative_destinations(): void
    {
        $this->seed(RolesSeeder::class);
        $resident = $this->userWithRoles(['Resident']);

        $this->actingAs($resident)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Lengkapi profil warga')
            ->assertSee('id="mobile-navigation"', false)
            ->assertSee('data-mobile-menu-open', false)
            ->assertSee('aria-haspopup="dialog"', false)
            ->assertDontSee('Log audit')
            ->assertDontSee('Akun dan peran');

        $routes = collect(app(NavigationMenu::class)->for($resident))->flatten(1)->pluck('route');
        $this->assertSame([
            'dashboard', 'profile.show', 'marketplace.index', 'marketplace.cart',
            'marketplace.orders.index',
            'correspondence.index', 'services.index',
        ], $routes->all());
        $this->assertFalse($routes->contains('marketplace.seller.application'));
        $this->actingAs($resident)->get(route('marketplace.seller.application'))
            ->assertRedirect(route('profile.create'))
            ->assertSessionHas('status', 'Lengkapi profil warga dan pilih wilayah sebelum mendaftar sebagai penjual.');
    }

    public function test_multi_role_navigation_does_not_repeat_destinations(): void
    {
        $this->seed(RolesSeeder::class);
        $user = $this->userWithRoles(['Resident', 'Village Admin', 'Super Admin', 'BUMDes Manager']);

        $routes = collect(app(NavigationMenu::class)->for($user))->flatten(1)->pluck('route');

        $this->assertCount($routes->count(), $routes->unique());
        $this->assertSame(1, $routes->countBy()->get('dashboard'));
        $this->assertTrue($routes->contains('profile.show'));
        $this->assertTrue($routes->contains('admin.resident-region-verification.index'));
        $this->assertTrue($routes->contains('bumdes.staff.index'));

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Wilayah')
            ->assertSee('Akun dan akses')
            ->assertSee('Pemantauan')
            ->assertSee('Layanan BUMDes')
            ->assertSee('mobile-navigation');
    }

    public function test_unassigned_rt_has_no_region_navigation_and_keeps_server_denial(): void
    {
        $this->seed(RolesSeeder::class);
        $officer = $this->userWithRoles(['RT']);

        $routes = collect(app(NavigationMenu::class)->for($officer))->flatten(1)->pluck('route');

        $this->assertFalse($routes->contains('officer.residents.index'));
        $this->actingAs($officer)->get(route('officer.residents.index'))->assertForbidden();
    }

    public function test_bumdes_operator_receives_correspondence_but_not_manager_staff_navigation(): void
    {
        $this->seed(RolesSeeder::class);
        $operator = $this->userWithRoles(['BUMDes Operator']);
        BumdesStaff::query()->create(['user_id' => $operator->id, 'employment_status' => 'active']);

        $routes = collect(app(NavigationMenu::class)->for($operator))->flatten(1)->pluck('route');

        $this->assertFalse($routes->contains('bumdes.staff.index'));
        $this->assertTrue($routes->contains('correspondence.index'));
        $this->actingAs($operator)->get(route('bumdes.staff.index'))->assertForbidden();
        $this->actingAs($operator)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Permohonan surat')
            ->assertDontSee('Marketplace')
            ->assertDontSee('Wi-Fi');
    }

    public function test_super_admin_routes_stay_forbidden_to_residents_who_enter_them_directly(): void
    {
        $this->seed(RolesSeeder::class);
        $resident = $this->userWithRoles(['Resident']);

        $this->actingAs($resident)->get(route('admin.users.manage'))->assertForbidden();
    }

    /**
     * @param  array<int, string>  $roleNames
     */
    private function userWithRoles(array $roleNames): User
    {
        $user = User::factory()->active()->create();
        $roleIds = Role::query()->whereIn('name', $roleNames)->pluck('id');
        $user->roles()->attach($roleIds);

        return $user->load('roles');
    }
}
