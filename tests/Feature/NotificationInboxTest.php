<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NotificationInboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_only_see_and_mark_their_own_notifications_as_read(): void
    {
        $owner = User::factory()->active()->create();
        $otherUser = User::factory()->active()->create();
        $ownedId = DB::table('notifications')->insertGetId([
            'user_id' => $owner->getKey(), 'title' => 'Status layanan berubah', 'message' => 'Buka pembaruan.',
            'reference_type' => null, 'reference_id' => null, 'created_at' => now(),
        ]);
        $otherId = DB::table('notifications')->insertGetId([
            'user_id' => $otherUser->getKey(), 'title' => 'Privat milik akun lain', 'message' => 'Jangan tampilkan.',
            'reference_type' => null, 'reference_id' => null, 'created_at' => now(),
        ]);

        $this->actingAs($owner)->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Status layanan berubah')
            ->assertDontSee('Privat milik akun lain');

        $this->actingAs($owner)->post(route('notifications.open', $otherId))->assertNotFound();
        $this->actingAs($owner)->post(route('notifications.open', $ownedId))->assertRedirect(route('notifications.index'));
        $this->assertNotNull(DB::table('notifications')->where('id', $ownedId)->value('read_at'));
        $this->assertNull(DB::table('notifications')->where('id', $otherId)->value('read_at'));
    }

    public function test_unread_notifications_are_exposed_in_shared_navigation(): void
    {
        $owner = User::factory()->active()->create();
        DB::table('notifications')->insert([
            'user_id' => $owner->getKey(), 'title' => 'Uji notifikasi', 'message' => 'Pesan umum.',
            'reference_type' => null, 'reference_id' => null, 'created_at' => now(),
        ]);

        $this->actingAs($owner)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Pemberitahuan')
            ->assertSee('1 belum dibaca');
    }
}
