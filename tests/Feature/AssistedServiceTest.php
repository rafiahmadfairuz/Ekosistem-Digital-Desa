<?php

namespace Tests\Feature;

use App\Models\AssistedServiceSession;
use App\Models\BumdesStaff;
use App\Models\LetterRequest;
use App\Models\LetterType;
use App\Models\Region;
use App\Models\Resident;
use App\Models\Role;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Models\Village;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssistedServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    public function test_resident_requests_help_and_operator_must_accept_before_assisting(): void
    {
        [$resident, $profile] = $this->residentWithVerifiedRegion('assist-resident');
        $operator = $this->bumdesUser('BUMDes Operator', 'assist-operator');

        $this->actingAs($resident)->get(route('bumdes.assistance.index'))
            ->assertOk()->assertSee('Ajukan bantuan')->assertSee(route('bumdes.assistance.create'));

        $this->actingAs($resident)->post(route('bumdes.assistance.store'), [
            'service_type' => 'general',
            'notes' => 'Dibantu memahami menu layanan digital.',
        ])->assertRedirect();

        $session = AssistedServiceSession::query()->firstOrFail();
        $this->assertSame($profile->id, $session->resident_id);
        $this->assertNull($session->operator_id);
        $this->assertSame('pending', $session->status);
        $this->assertNull($session->identity_verified_at);
        $this->actingAs($resident)->get(route('bumdes.assistance.show', $session))
            ->assertOk()
            ->assertSee('Referensi sesi')
            ->assertSee($session->session_reference);
        $this->assertNotSame('Dibantu memahami menu layanan digital.', $session->getRawOriginal('notes'));
        $this->assertDatabaseHas('assisted_service_logs', ['session_id' => $session->id, 'action' => 'assistance_requested', 'actor_id' => $resident->id]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $operator->id,
            'reference_type' => 'assisted_service_session',
            'reference_id' => $session->id,
        ]);

        $this->actingAs($operator)->post(route('bumdes.assistance.request-confirmation', $session))->assertForbidden();
        $this->actingAs($operator)->post(route('bumdes.assistance.accept', $session), ['identity_verified' => '1'])->assertRedirect();
        $session->refresh();
        $this->assertSame($operator->id, $session->operator_id);
        $this->assertSame('in_progress', $session->status);
        $this->assertNotNull($session->identity_verified_at);
        $this->assertDatabaseHas('assisted_service_logs', ['session_id' => $session->id, 'action' => 'request_accepted_and_identity_verified', 'actor_id' => $operator->id]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $resident->id,
            'reference_type' => 'assisted_service_session',
            'reference_id' => $session->id,
        ]);

        $this->actingAs($operator)->post(route('bumdes.assistance.request-confirmation', $session))->assertRedirect();
        $this->assertSame('awaiting_resident_confirmation', $session->fresh()->status);
        $this->actingAs($resident)->get(route('bumdes.assistance.show', $session))->assertOk()->assertSee('Dibantu memahami menu layanan digital.');
        $this->actingAs($resident)->post(route('bumdes.assistance.confirm', $session))->assertRedirect();
        $this->assertSame('completed', $session->fresh()->status);
        $this->assertNotNull($session->fresh()->resident_confirmed_at);
        $this->assertDatabaseHas('assisted_service_logs', ['session_id' => $session->id, 'action' => 'resident_confirmed_and_completed', 'actor_id' => $resident->id]);
    }

    public function test_session_access_is_scoped_and_completed_history_cannot_be_cancelled_or_deleted(): void
    {
        [$residentUser, $profile] = $this->residentWithVerifiedRegion('assist-owner');
        [, $otherResidentProfile] = $this->residentWithVerifiedRegion('assist-other-resident');
        $operator = $this->bumdesUser('BUMDes Operator', 'assist-owner-operator');
        $otherOperator = $this->bumdesUser('BUMDes Operator', 'assist-other-operator');
        $session = $this->createSession($profile, $operator);
        $otherSession = $this->createSession($otherResidentProfile, $otherOperator);

        $this->actingAs($otherOperator)->get(route('bumdes.assistance.show', $session))->assertForbidden();
        $this->actingAs($residentUser)->get(route('bumdes.assistance.show', $otherSession))->assertNotFound();
        $this->actingAs($residentUser)->post(route('bumdes.assistance.confirm', $otherSession))->assertNotFound();
        $session->forceFill(['status' => 'completed', 'completed_at' => now()])->save();
        $this->actingAs($operator)->post(route('bumdes.assistance.cancel', $session), ['reason' => 'Tidak diperlukan lagi'])->assertForbidden();

        $log = $session->logs()->create(['actor_id' => $operator->id, 'action' => 'test_event', 'metadata' => [], 'created_at' => now()]);
        $this->expectException(\LogicException::class);
        $log->delete();
    }

    public function test_assistance_chat_is_private_encrypted_and_read_only_after_session_completion(): void
    {
        [$resident, $profile] = $this->residentWithVerifiedRegion('assist-chat-owner');
        $otherResident = $this->userWithRole('Resident', 'assist-chat-outsider');
        $operator = $this->bumdesUser('BUMDes Operator', 'assist-chat-operator');
        $otherOperator = $this->bumdesUser('BUMDes Operator', 'assist-chat-other-operator');
        $session = $this->createSession($profile, $operator);

        $this->actingAs($resident)->post(route('bumdes.assistance.messages.store', $session), [
            'body' => 'Mohon bantu saya memilih layanan yang sesuai.',
        ])->assertRedirect();
        $firstMessage = $session->messages()->firstOrFail();
        $this->assertNotSame('Mohon bantu saya memilih layanan yang sesuai.', $firstMessage->getRawOriginal('body'));
        $this->assertDatabaseHas('assisted_service_logs', ['session_id' => $session->id, 'action' => 'message_sent', 'actor_id' => $resident->id]);

        $this->actingAs($operator)->get(route('bumdes.assistance.show', $session))->assertOk()
            ->assertSee('Mohon bantu saya memilih layanan yang sesuai.');
        $this->actingAs($otherOperator)->get(route('bumdes.assistance.show', $session))->assertForbidden();
        $this->actingAs($otherOperator)->post(route('bumdes.assistance.messages.store', $session), ['body' => 'Pesan pihak lain'])
            ->assertForbidden();
        $this->actingAs($otherResident)->get(route('bumdes.assistance.show', $session))->assertNotFound();

        $this->actingAs($operator)->post(route('bumdes.assistance.messages.store', $session), [
            'body' => 'Silakan buka menu layanan dan pilih bantuan warga.',
        ])->assertRedirect();
        $this->actingAs($resident)->get(route('bumdes.assistance.show', $session))->assertOk()
            ->assertSee('Mohon bantu saya memilih layanan yang sesuai.')
            ->assertSee('Silakan buka menu layanan dan pilih bantuan warga.');
        $this->actingAs($resident)->post(route('bumdes.assistance.messages.store', $session), [
            'body' => 'OTP: 123456',
        ])->assertSessionHasErrors('body');
        $this->assertDatabaseCount('assisted_service_messages', 2);

        $session->forceFill(['status' => 'completed', 'completed_at' => now()])->save();
        $this->actingAs($resident)->get(route('bumdes.assistance.show', $session))->assertOk()
            ->assertSee('Riwayat pesan tetap dapat dibaca.');
        $this->actingAs($operator)->post(route('bumdes.assistance.messages.store', $session), ['body' => 'Pesan setelah selesai'])
            ->assertForbidden();
    }

    public function test_only_managers_can_view_operational_reports_and_invalid_resident_is_rejected(): void
    {
        $operator = $this->bumdesUser('BUMDes Operator', 'assist-report-operator');
        $manager = $this->bumdesUser('BUMDes Manager', 'assist-report-manager');

        $this->get(route('bumdes.assistance.index'))->assertRedirect(route('login'));
        $this->actingAs($operator)->get(route('bumdes.assistance.reports'))->assertForbidden();
        $this->actingAs($manager)->get(route('bumdes.assistance.reports'))->assertOk()->assertSee('Laporan operasional BUMDes');
        $this->actingAs($operator)->post(route('bumdes.assistance.store'), [])->assertForbidden();
        $this->assertDatabaseCount('assisted_service_sessions', 0);
    }

    public function test_sensitive_notes_are_rejected_and_reference_links_must_belong_to_the_resident(): void
    {
        [$resident, $profile] = $this->residentWithVerifiedRegion('assist-reference-owner');
        [, $otherProfile] = $this->residentWithVerifiedRegion('assist-reference-other');
        $operator = $this->bumdesUser('BUMDes Operator', 'assist-reference-operator');
        $session = $this->createSession($profile, $operator);
        $session->forceFill(['service_type' => 'internet_complaint'])->save();
        $ownRequest = ServiceRequest::query()->create([
            'resident_id' => $profile->id,
            'service_type' => 'other',
            'subject' => 'Laporan milik warga ini',
            'description' => 'Data terkait bantuan',
            'status' => 'submitted',
        ]);
        $otherRequest = ServiceRequest::query()->create([
            'resident_id' => $otherProfile->id,
            'service_type' => 'other',
            'subject' => 'Laporan warga lain',
            'description' => 'Teks privat',
            'status' => 'submitted',
        ]);

        $this->actingAs($operator)->get(route('bumdes.assistance.show', $session))
            ->assertOk()->assertSee('service_request:'.$ownRequest->id)->assertDontSee('service_request:'.$otherRequest->id);

        $this->actingAs($resident)->post(route('bumdes.assistance.store'), [
            'service_type' => 'general', 'notes' => 'OTP: 123456',
        ])->assertSessionHasErrors('notes');
        $this->actingAs($operator)->put(route('bumdes.assistance.reference.update', $session), [
            'reference' => 'service_request:'.$otherRequest->id,
        ])->assertNotFound();
        $this->assertNull($session->fresh()->reference_type);
    }

    public function test_assisted_correspondence_is_linked_to_session_and_resident_confirmation_completes_it(): void
    {
        [$resident, $profile] = $this->residentWithVerifiedRegion('assist-letter-resident');
        $operator = $this->bumdesUser('BUMDes Operator', 'assist-letter-operator');
        $type = LetterType::query()->create([
            'code' => 'ASSISTED', 'name' => 'Surat bantuan', 'required_fields' => [
                ['key' => 'purpose', 'label' => 'Keperluan', 'type' => 'text', 'required' => true, 'max' => 80],
            ], 'required_attachments' => [], 'is_active' => true,
        ]);

        $this->actingAs($resident)->post(route('bumdes.assistance.store'), ['service_type' => 'correspondence'])->assertRedirect();
        $session = AssistedServiceSession::query()->firstOrFail();
        $this->actingAs($operator)->post(route('bumdes.assistance.accept', $session), ['identity_verified' => '1'])->assertRedirect();

        $this->actingAs($operator)->get(route('correspondence.create', ['assistance_session_id' => $session->id, 'type_id' => $type->id]))
            ->assertOk()->assertSee('Warga pemilik permohonan');
        $this->actingAs($operator)->post(route('correspondence.store'), [
            'letter_type_id' => $type->id,
            'assistance_session_id' => $session->id,
            'fields' => ['purpose' => 'Pengantar administrasi'],
        ])->assertRedirect();

        $letter = LetterRequest::query()->firstOrFail();
        $session->refresh();
        $this->assertSame('letter_request', $session->reference_type);
        $this->assertSame($letter->id, $session->reference_id);
        $this->assertSame($profile->id, $session->resident_id);
        $this->assertSame($operator->id, $session->operator_id);
        $this->assertSame('awaiting_resident_confirmation', $session->status);
        $this->actingAs($resident)->post(route('correspondence.confirm', $letter))->assertRedirect();
        $this->assertSame('completed', $session->fresh()->status);
        $this->assertNotNull($session->fresh()->resident_confirmed_at);
    }

    public function test_manager_assigned_to_help_request_can_prepare_the_resident_owned_correspondence_draft(): void
    {
        [$resident] = $this->residentWithVerifiedRegion('assist-manager-letter-resident');
        $manager = $this->bumdesUser('BUMDes Manager', 'assist-letter-manager');
        $type = LetterType::query()->create([
            'code' => 'MANAGER_ASSISTED', 'name' => 'Surat bantuan manager', 'required_fields' => [
                ['key' => 'purpose', 'label' => 'Keperluan', 'type' => 'text', 'required' => true, 'max' => 80],
            ], 'required_attachments' => [], 'is_active' => true,
        ]);

        $this->actingAs($resident)->post(route('bumdes.assistance.store'), ['service_type' => 'correspondence'])->assertRedirect();
        $session = AssistedServiceSession::query()->firstOrFail();
        $this->actingAs($manager)->post(route('bumdes.assistance.accept', $session), ['identity_verified' => '1'])->assertRedirect();
        $this->actingAs($manager)->get(route('correspondence.create', ['assistance_session_id' => $session->id, 'type_id' => $type->id]))->assertOk();
        $this->actingAs($manager)->post(route('correspondence.store'), [
            'letter_type_id' => $type->id,
            'assistance_session_id' => $session->id,
            'fields' => ['purpose' => 'Pendampingan pengajuan'],
        ])->assertRedirect();

        $letter = LetterRequest::query()->firstOrFail();
        $this->assertSame($resident->resident->id, $letter->resident_id);
        $this->assertSame($manager->id, $letter->created_by);
        $this->actingAs($manager)->get(route('correspondence.show', $letter))->assertOk();
    }

    /** @return array{User, Resident} */
    private function residentWithVerifiedRegion(string $username): array
    {
        $user = $this->userWithRole('Resident', $username);
        $village = Village::query()->create(['code' => strtoupper(substr(str_replace('-', '', $username), 0, 7)).str_pad((string) (Village::query()->count() + 1), 3, '0', STR_PAD_LEFT), 'name' => 'Desa Bantuan']);
        $rw = Region::query()->create(['village_id' => $village->id, 'region_type' => 'rw', 'region_code' => 'RW-01', 'name' => 'RW 01']);
        $rt = Region::query()->create(['village_id' => $village->id, 'parent_id' => $rw->id, 'region_type' => 'rt', 'region_code' => 'RT-01', 'name' => 'RT 01']);
        $resident = (new Resident)->forceFill([
            'user_id' => $user->id,
            'region_id' => $rt->id,
            'full_name_ciphertext' => 'cipher',
            'region_verified_at' => now(),
        ]);
        $resident->save();

        return [$user->load('resident'), $resident];
    }

    private function bumdesUser(string $role, string $username): User
    {
        $user = $this->userWithRole($role, $username);
        BumdesStaff::query()->create(['user_id' => $user->id, 'employment_status' => 'active']);

        return $user;
    }

    private function userWithRole(string $role, string $username): User
    {
        $user = User::factory()->active()->create(['username' => $username]);
        $user->roles()->attach(Role::query()->where('name', $role)->value('id'));

        return $user->load('roles');
    }

    private function createSession(Resident $resident, User $operator): AssistedServiceSession
    {
        return AssistedServiceSession::query()->create([
            'session_reference' => 'BAS-TEST-'.fake()->unique()->numerify('######'),
            'resident_id' => $resident->id,
            'operator_id' => $operator->id,
            'service_type' => 'general',
            'status' => 'in_progress',
            'started_at' => now(),
            'requested_at' => now(),
            'accepted_at' => now(),
            'identity_verified_at' => now(),
        ]);
    }
}
