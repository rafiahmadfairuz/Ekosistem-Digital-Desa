<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PendingUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_can_activate_pending_accounts(): void
    {
        $this->seed(RolesSeeder::class);
        $pendingUser = User::factory()->create([
            'username' => 'pending01',
            'account_status' => 'pending',
        ]);
        $operator = User::factory()->create([
            'username' => 'resident01',
            'account_status' => 'active',
        ]);

        $this->actingAs($operator)
            ->post(route('admin.users.activate', $pendingUser))
            ->assertForbidden();

        $this->assertSame('pending', $pendingUser->fresh()->account_status);

        $superAdmin = User::factory()->create([
            'username' => 'superadmin01',
            'account_status' => 'active',
        ]);
        $superAdmin->roles()->attach(Role::query()->where('name', 'Super Admin')->value('id'));

        $this->actingAs($superAdmin)
            ->post(route('admin.users.activate', $pendingUser))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('status', 'Akun berhasil diaktifkan.');

        $this->assertSame('active', $pendingUser->fresh()->account_status);
    }
}
