<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_user_can_sign_in_with_username(): void
    {
        $user = User::factory()->create([
            'username' => 'warga01',
            'account_status' => 'active',
            'password' => Hash::make('correct-horse-battery'),
        ]);

        $this->post(route('login.store'), [
            'login' => 'warga01',
            'password' => 'correct-horse-battery',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
    }

    public function test_pending_user_cannot_sign_in(): void
    {
        User::factory()->create([
            'username' => 'menunggu',
            'account_status' => 'pending',
            'password' => Hash::make('correct-horse-battery'),
        ]);

        $this->from(route('login'))->post(route('login.store'), [
            'login' => 'menunggu',
            'password' => 'correct-horse-battery',
        ])->assertRedirect(route('login'))
            ->assertSessionHasErrors('login');

        $this->assertGuest();
    }

    public function test_login_attempts_are_rate_limited_by_identity_and_ip(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('login.store'), ['login' => 'unknown@example.test', 'password' => 'WrongPassword123'])
                ->assertSessionHasErrors('login');
        }

        $this->post(route('login.store'), ['login' => 'unknown@example.test', 'password' => 'WrongPassword123'])
            ->assertTooManyRequests();
    }

    public function test_disabled_account_is_logged_out_from_existing_session(): void
    {
        $user = User::factory()->create(['account_status' => 'disabled']);

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_active_verified_user_can_sign_out(): void
    {
        $user = User::factory()->create(['account_status' => 'active']);

        $this->actingAs($user)->post(route('logout'))->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_unverified_active_account_is_sent_to_email_verification(): void
    {
        $user = User::factory()->create(['account_status' => 'active', 'email_verified_at' => null]);

        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('verification.notice'));
    }

    public function test_signed_email_verification_works_without_an_active_session(): void
    {
        $user = User::factory()->unverified()->create(['account_status' => 'pending']);
        $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $this->get($url)->assertRedirect(route('login'));

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $this->assertSame('pending', $user->fresh()->account_status);
    }

    public function test_password_reset_changes_password_for_active_verified_user(): void
    {
        $user = User::factory()->create(['account_status' => 'active']);
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewStrongPassword123',
            'password_confirmation' => 'NewStrongPassword123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('NewStrongPassword123', $user->fresh()->password));
    }

    public function test_inactive_account_cannot_reset_its_password(): void
    {
        $user = User::factory()->create(['account_status' => 'disabled', 'password' => 'OldPassword123']);
        $token = Password::broker()->createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewStrongPassword123',
            'password_confirmation' => 'NewStrongPassword123',
        ])->assertSessionHasErrors('email');

        $this->assertTrue(Hash::check('OldPassword123', $user->fresh()->password));
    }

    public function test_public_registration_creates_a_pending_resident_without_privileged_roles(): void
    {
        $this->seed(RolesSeeder::class);
        Notification::fake();

        $this->post(route('register.store'), [
            'username' => 'warga_baru',
            'email' => 'warga@example.test',
            'password' => 'StrongPassword123',
            'password_confirmation' => 'StrongPassword123',
            'role' => 'Super Admin',
        ])->assertRedirect(route('verification.notice'));

        $user = User::query()->where('username', 'warga_baru')->firstOrFail();

        $this->assertSame('pending', $user->account_status);
        $this->assertTrue(Hash::check('StrongPassword123', $user->password));
        $this->assertSame(['Resident'], $user->roles()->orderBy('name')->pluck('name')->all());
        $this->assertDatabaseMissing('user_roles', [
            'user_id' => $user->getKey(),
            'role_id' => Role::query()->where('name', 'Super Admin')->value('id'),
        ]);
        $this->assertAuthenticatedAs($user);
    }
}
