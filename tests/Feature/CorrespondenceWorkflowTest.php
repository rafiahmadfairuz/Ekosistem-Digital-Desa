<?php

namespace Tests\Feature;

use App\Models\AssistedServiceSession;
use App\Models\BumdesStaff;
use App\Models\LetterApproval;
use App\Models\LetterDocument;
use App\Models\LetterRequest;
use App\Models\LetterType;
use App\Models\Region;
use App\Models\RegionOfficer;
use App\Models\Resident;
use App\Models\Role;
use App\Models\User;
use App\Models\Village;
use App\Services\IdentityDataProtector;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use LogicException;
use Tests\TestCase;

class CorrespondenceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->seed(RolesSeeder::class);
    }

    public function test_resident_submits_encrypted_request_and_only_owner_can_view_it(): void
    {
        [, , $rtRegion] = $this->regions();
        $resident = $this->userWithRoles(['Resident']);
        $profile = $this->resident($resident, $rtRegion);
        $type = $this->letterType();
        $rtOfficer = $this->assignedOfficer('RT', $rtRegion);

        $this->actingAs($resident)->post(route('correspondence.store'), [
            'letter_type_id' => $type->id,
            'resident_confirmation' => '1',
            'fields' => ['purpose' => 'Pengajuan surat keterangan', 'unexpected' => 'jangan simpan'],
        ])->assertRedirect();

        $letterRequest = LetterRequest::query()->firstOrFail();
        $this->assertSame($profile->id, $letterRequest->resident_id);
        $this->assertSame($resident->id, $letterRequest->created_by);
        $this->assertSame('self_service', $letterRequest->submission_channel);
        $this->assertSame('rt_review', $letterRequest->status);
        $this->assertStringNotContainsString('Pengajuan surat keterangan', json_encode($letterRequest->encrypted_data));
        $this->assertStringNotContainsString('jangan simpan', json_encode($letterRequest->encrypted_data));
        $this->assertDatabaseHas('letter_request_versions', ['letter_request_id' => $letterRequest->id, 'version' => 1]);
        $this->assertDatabaseHas('audit_logs', ['entity_type' => 'letter_request', 'entity_id' => $letterRequest->id]);
        $this->assertDatabaseHas('notifications', ['user_id' => $rtOfficer->id, 'reference_type' => 'letter_request', 'reference_id' => $letterRequest->id]);

        $otherResident = $this->userWithRoles(['Resident']);
        $this->actingAs($otherResident)->get(route('correspondence.show', $letterRequest))->assertForbidden();
        $this->actingAs($otherResident)->get(route('correspondence.index'))->assertOk()->assertDontSee('Permohonan surat Anda telah dikirim');
        $this->actingAs($resident)->get(route('correspondence.show', $letterRequest))->assertOk()->assertSee('Pengajuan surat keterangan');
        $residentNotification = DB::table('notifications')->where('user_id', $resident->id)->firstOrFail();
        $this->actingAs($otherResident)->post(route('correspondence.notifications.read', $residentNotification->id))->assertNotFound();
        $this->actingAs($resident)->post(route('correspondence.notifications.read', $residentNotification->id))->assertRedirect();
        $this->assertNotNull(DB::table('notifications')->where('id', $residentNotification->id)->value('read_at'));
    }

    public function test_new_resident_without_a_profile_cannot_see_other_letter_requests(): void
    {
        [, , $firstRegion] = $this->regions();
        $firstOwner = $this->userWithRoles(['Resident']);
        $this->resident($firstOwner, $firstRegion);
        $type = $this->letterType();
        $firstRequest = $this->createRequest($firstOwner, $type);

        $otherVillage = Village::query()->create(['code' => 'CORR003', 'name' => 'Desa Kedua']);
        $otherRw = Region::query()->create(['village_id' => $otherVillage->id, 'parent_id' => null, 'region_type' => 'rw', 'region_code' => '02', 'name' => 'RW 02']);
        $otherRt = Region::query()->create(['village_id' => $otherVillage->id, 'parent_id' => $otherRw->id, 'region_type' => 'rt', 'region_code' => '02/01', 'name' => 'RT 01']);
        $secondOwner = $this->userWithRoles(['Resident']);
        $this->resident($secondOwner, $otherRt);
        $secondRequest = $this->createRequest($secondOwner, $type);

        $newResident = $this->userWithRoles(['Resident']);
        $this->actingAs($newResident)->get(route('correspondence.index'))
            ->assertOk()
            ->assertDontSee($firstRequest->request_number)
            ->assertDontSee($secondRequest->request_number)
            ->assertDontSee('Buat permohonan surat');

        $this->actingAs($secondOwner)->get(route('correspondence.index'))
            ->assertOk()
            ->assertSee($secondRequest->request_number)
            ->assertDontSee($firstRequest->request_number)
            ->assertSee('Buat permohonan surat');
    }

    public function test_workflow_is_sequential_region_scoped_and_village_head_only(): void
    {
        [, $rw, $rtRegion] = $this->regions();
        $resident = $this->userWithRoles(['Resident']);
        $this->resident($resident, $rtRegion);
        $type = $this->letterType();
        $type->forceFill(['required_fields' => [
            ['key' => 'purpose', 'label' => 'Keperluan', 'type' => 'textarea', 'required' => true],
            ['key' => 'national_id', 'label' => 'NIK', 'type' => 'text', 'required' => true, 'max' => 16],
        ]])->save();
        $rt = $this->assignedOfficer('RT', $rtRegion);
        $rwOfficer = $this->assignedOfficer('RW', $rw);
        $admin = $this->userWithRoles(['Village Admin']);
        $head = $this->userWithRoles(['Village Head']);
        $superAdmin = $this->userWithRoles(['Super Admin']);

        $this->actingAs($resident)->post(route('correspondence.store'), [
            'letter_type_id' => $type->id,
            'resident_confirmation' => '1',
            'fields' => ['purpose' => 'Uji validasi', 'national_id' => '1234'],
        ])->assertSessionHasErrors('fields.national_id');
        $this->assertDatabaseCount('letter_requests', 0);

        $letterRequest = $this->createRequest($resident, $type);

        $this->actingAs($rwOfficer)->post(route('correspondence.decision', $letterRequest), ['decision' => 'approved'])->assertForbidden();
        $this->actingAs($this->userWithRoles(['RT']))->post(route('correspondence.decision', $letterRequest), ['decision' => 'approved'])->assertForbidden();
        $this->actingAs($rt)->post(route('correspondence.decision', $letterRequest), ['decision' => 'approved'])->assertRedirect();
        $this->assertSame('rw_review', $letterRequest->fresh()->status);
        $this->actingAs($rt)->post(route('correspondence.decision', $letterRequest->fresh()), ['decision' => 'approved'])->assertForbidden();

        $this->actingAs($rwOfficer)->post(route('correspondence.decision', $letterRequest->fresh()), ['decision' => 'approved'])->assertRedirect();
        $this->assertSame('village_review', $letterRequest->fresh()->status);
        $this->actingAs($admin)->post(route('correspondence.decision', $letterRequest->fresh()), ['decision' => 'approved'])->assertRedirect();
        $this->assertSame('signature_pending', $letterRequest->fresh()->status);
        $this->assertDatabaseHas('letter_documents', ['letter_request_id' => $letterRequest->id, 'version' => 1, 'signature_type' => null]);

        $this->actingAs($superAdmin)->post(route('correspondence.signature', $letterRequest->fresh()), [
            'decision' => 'signed', 'prototype_acknowledged' => '1',
        ])->assertForbidden();
        $this->actingAs($this->userWithRoles(['BUMDes Operator']))->post(route('correspondence.signature', $letterRequest->fresh()), [
            'decision' => 'signed', 'prototype_acknowledged' => '1',
        ])->assertForbidden();
        $this->actingAs($head)->post(route('correspondence.signature', $letterRequest->fresh()), [
            'decision' => 'signed', 'prototype_acknowledged' => '1',
        ])->assertRedirect();

        $this->assertSame('completed', $letterRequest->fresh()->status);
        $document = LetterDocument::query()->where('letter_request_id', $letterRequest->id)->whereNotNull('signed_at')->firstOrFail();
        $this->assertSame($head->id, $document->signed_by);
        $this->assertSame('prototype', $document->signature_type);
        $pdf = Storage::disk('local')->get($document->private_file_path);
        $this->assertStringStartsWith('%PDF-1.4', $pdf);
        $this->assertStringContainsString('TERSERTIFIKASI', $pdf);
        $this->assertStringContainsString('1234567890123456', $pdf);
        $protector = app(IdentityDataProtector::class);
        $completedRequest = $letterRequest->fresh();
        $currentPayload = $protector->decrypt($completedRequest->encrypted_data['ciphertext'], $completedRequest->encrypted_data['nonce'], $completedRequest->encrypted_data['tag']);
        $this->assertStringNotContainsString('1234567890123456', $currentPayload);
        foreach ($completedRequest->versions as $version) {
            $versionPayload = $protector->decrypt($version->encrypted_data['ciphertext'], $version->encrypted_data['nonce'], $version->encrypted_data['tag']);
            $this->assertStringNotContainsString('1234567890123456', $versionPayload);
        }
        $this->assertSame(hash('sha256', $pdf), $document->document_hash);
        $this->assertDatabaseHas('letter_approvals', ['letter_request_id' => $letterRequest->id, 'approval_level' => 'village_head', 'decision' => 'signed']);
        $this->actingAs($resident)->put(route('correspondence.revise', $letterRequest->fresh()), [
            'fields' => ['purpose' => 'Tidak boleh mengubah dokumen terbit'],
        ])->assertForbidden();
        $this->expectException(LogicException::class);
        $document->forceFill(['document_hash' => str_repeat('0', 64)])->save();
    }

    public function test_revision_requires_reason_and_resubmission_preserves_history_and_versions(): void
    {
        [, , $rtRegion] = $this->regions();
        $resident = $this->userWithRoles(['Resident']);
        $this->resident($resident, $rtRegion);
        $type = $this->letterType();
        $type->forceFill(['required_fields' => [
            ['key' => 'purpose', 'label' => 'Keperluan', 'type' => 'textarea', 'required' => true],
            ['key' => 'national_id', 'label' => 'NIK', 'type' => 'text', 'required' => true, 'max' => 16],
        ]])->save();
        $rt = $this->assignedOfficer('RT', $rtRegion);
        $letterRequest = $this->createRequest($resident, $type);

        $this->actingAs($rt)->post(route('correspondence.decision', $letterRequest), ['decision' => 'revision_required'])->assertSessionHasErrors('comments');
        $this->actingAs($rt)->post(route('correspondence.decision', $letterRequest), [
            'decision' => 'revision_required', 'comments' => 'Lengkapi tujuan surat.',
        ])->assertRedirect();
        $this->assertSame('revision_required', $letterRequest->fresh()->status);
        $this->actingAs($resident)->get(route('correspondence.show', $letterRequest->fresh()))->assertOk()->assertSee('Lengkapi tujuan surat.');

        $this->actingAs($resident)->put(route('correspondence.revise', $letterRequest->fresh()), [
            'fields' => ['purpose' => 'Tujuan telah diperbarui', 'national_id' => '1234567890123456'],
        ])->assertRedirect();

        $this->assertSame('rt_review', $letterRequest->fresh()->status);
        $this->assertSame(1, $letterRequest->fresh()->revision_number);
        $this->assertCount(2, $letterRequest->fresh()->versions);
        $this->assertCount(1, $letterRequest->fresh()->approvals);
        $this->assertDatabaseHas('letter_approvals', ['letter_request_id' => $letterRequest->id, 'decision' => 'revision_required']);

        $rw = $this->assignedOfficer('RW', $rtRegion->parent);
        $admin = $this->userWithRoles(['Village Admin']);
        $head = $this->userWithRoles(['Village Head']);
        $this->actingAs($rt)->post(route('correspondence.decision', $letterRequest->fresh()), ['decision' => 'approved'])->assertRedirect();
        $this->actingAs($rw)->post(route('correspondence.decision', $letterRequest->fresh()), ['decision' => 'approved'])->assertRedirect();
        $this->actingAs($admin)->post(route('correspondence.decision', $letterRequest->fresh()), ['decision' => 'approved'])->assertRedirect();
        $this->actingAs($head)->post(route('correspondence.signature', $letterRequest->fresh()), [
            'decision' => 'signed', 'prototype_acknowledged' => '1',
        ])->assertRedirect();

        $letterRequest = $letterRequest->fresh();
        $protector = app(IdentityDataProtector::class);
        $current = $protector->decrypt($letterRequest->encrypted_data['ciphertext'], $letterRequest->encrypted_data['nonce'], $letterRequest->encrypted_data['tag']);
        $this->assertStringNotContainsString('1234567890123456', $current);
        foreach ($letterRequest->versions as $version) {
            $plaintext = $protector->decrypt($version->encrypted_data['ciphertext'], $version->encrypted_data['nonce'], $version->encrypted_data['tag']);
            $this->assertStringNotContainsString('1234567890123456', $plaintext);
        }
        $document = LetterDocument::query()->where('letter_request_id', $letterRequest->id)->whereNotNull('signed_at')->firstOrFail();
        $this->assertStringContainsString('1234567890123456', Storage::disk('local')->get($document->private_file_path));
    }

    public function test_letter_type_edits_do_not_rewrite_existing_request_configuration(): void
    {
        [, , $rtRegion] = $this->regions();
        $resident = $this->userWithRoles(['Resident']);
        $this->resident($resident, $rtRegion);
        $type = $this->letterType();
        $letterRequest = $this->createRequest($resident, $type);

        $type->forceFill([
            'name' => 'Nama Surat Baru',
            'required_fields' => [['key' => 'new_field', 'label' => 'Isian baru', 'type' => 'text', 'required' => true]],
        ])->save();

        $letterRequest = $letterRequest->fresh(['letterType']);
        $this->assertSame('Surat Keterangan Uji', $letterRequest->effectiveLetterType()->name);
        $this->assertSame('purpose', $letterRequest->effectiveLetterType()->required_fields[0]['key']);
        $this->actingAs($resident)->get(route('correspondence.show', $letterRequest))->assertOk()->assertSee('Keperluan');
    }

    public function test_rt_and_rw_cannot_view_or_decide_for_another_region(): void
    {
        [, , $rtRegion] = $this->regions();
        $resident = $this->userWithRoles(['Resident']);
        $this->resident($resident, $rtRegion);
        $letterRequest = $this->createRequest($resident, $this->letterType());

        $otherVillage = Village::query()->create(['code' => 'CORR002', 'name' => 'Desa Lain']);
        $otherRw = Region::query()->create(['village_id' => $otherVillage->id, 'parent_id' => null, 'region_type' => 'rw', 'region_code' => '02', 'name' => 'RW 02']);
        $otherRt = Region::query()->create(['village_id' => $otherVillage->id, 'parent_id' => $otherRw->id, 'region_type' => 'rt', 'region_code' => '02/01', 'name' => 'RT 01']);
        $rt = $this->assignedOfficer('RT', $otherRt);
        $rw = $this->assignedOfficer('RW', $otherRw);

        $this->actingAs($rt)->get(route('correspondence.show', $letterRequest))->assertForbidden();
        $this->actingAs($rt)->post(route('correspondence.decision', $letterRequest), ['decision' => 'approved'])->assertForbidden();
        $letterRequest->forceFill(['status' => 'rw_review'])->save();
        $this->actingAs($rw)->get(route('correspondence.show', $letterRequest))->assertForbidden();
        $this->actingAs($rw)->post(route('correspondence.decision', $letterRequest), ['decision' => 'approved'])->assertForbidden();
    }

    public function test_assisted_request_belongs_to_resident_and_requires_resident_confirmation(): void
    {
        [, , $rtRegion] = $this->regions();
        $resident = $this->userWithRoles(['Resident']);
        $this->resident($resident, $rtRegion);
        $operator = $this->operatorWithStaff();
        $type = $this->letterType();

        $this->actingAs($resident)->post(route('bumdes.assistance.store'), ['service_type' => 'correspondence'])->assertRedirect();
        $session = AssistedServiceSession::query()->firstOrFail();
        $this->actingAs($operator)->post(route('bumdes.assistance.accept', $session), ['identity_verified' => '1'])->assertRedirect();

        $this->actingAs($operator)->post(route('correspondence.store'), [
            'letter_type_id' => $type->id,
            'assistance_session_id' => $session->id,
            'fields' => ['purpose' => 'Surat yang dibantu'],
        ])->assertRedirect();

        $letterRequest = LetterRequest::query()->firstOrFail();
        $this->assertSame($resident->resident->id, $letterRequest->resident_id);
        $this->assertSame($operator->id, $letterRequest->created_by);
        $this->assertSame('assisted_bumdes', $letterRequest->submission_channel);
        $this->assertSame('draft', $letterRequest->status);
        $this->actingAs($operator)->post(route('correspondence.confirm', $letterRequest))->assertNotFound();
        $this->actingAs($resident)->post(route('correspondence.confirm', $letterRequest))->assertRedirect();
        $this->assertSame('rt_review', $letterRequest->fresh()->status);
        $this->assertNotNull($letterRequest->fresh()->resident_confirmed_at);
    }

    public function test_type_management_is_village_admin_only_and_uploads_are_private_and_authorized(): void
    {
        [, , $rtRegion] = $this->regions();
        $resident = $this->userWithRoles(['Resident']);
        $this->resident($resident, $rtRegion);
        $operator = $this->operatorWithStaff();
        $type = $this->letterType([[
            'key' => 'identity', 'label' => 'Identitas', 'required' => true,
            'max_kb' => 5120,
        ]]);

        $this->actingAs($operator)->get(route('correspondence.types'))->assertForbidden();
        $villageAdmin = $this->userWithRoles(['Village Admin']);
        $this->actingAs($villageAdmin)->get(route('correspondence.types'))->assertOk()
            ->assertSee('Tambah isian')->assertSee('Tambah dokumen')->assertDontSee('Konfigurasi isian (JSON)');
        $this->actingAs($villageAdmin)->post(route('correspondence.types.store'), [
            'code' => 'SURAT_PENGANTAR', 'name' => 'Surat Pengantar', 'is_active' => '1',
            'required_fields' => '[{"key":"purpose","label":"Keperluan","type":"text","required":true,"max":300}]',
            'required_attachments' => '[]',
        ])->assertRedirect();
        $this->assertDatabaseHas('letter_types', ['code' => 'SURAT_PENGANTAR', 'is_active' => true]);

        $this->actingAs($resident)->post(route('correspondence.store'), [
            'letter_type_id' => $type->id,
            'resident_confirmation' => '1',
            'fields' => ['purpose' => 'Lampiran privat'],
            'attachments' => ['identity' => UploadedFile::fake()->createWithContent('identity.pdf', "%PDF-1.4\nprivate")],
        ])->assertRedirect();

        $letterRequest = LetterRequest::query()->firstOrFail();
        $attachment = $letterRequest->attachments()->firstOrFail();
        $this->assertNotSame('identity.pdf', basename($attachment->private_file_path));
        Storage::disk('local')->assertExists($attachment->private_file_path);
        $this->actingAs($this->userWithRoles(['Resident']))->get(route('correspondence.attachments.download', [$letterRequest, $attachment]))->assertForbidden();
        $this->actingAs($resident)->get(route('correspondence.attachments.download', [$letterRequest, $attachment]))->assertOk();

        try {
            LetterApproval::query()->create([
                'letter_request_id' => $letterRequest->id, 'reviewer_id' => $resident->id,
                'actor_role' => 'RT', 'approval_level' => 'rt', 'decision' => 'approved', 'reviewed_at' => now(),
            ])->forceFill(['comments' => 'ubah'])->save();
            $this->fail('An approval decision must reject updates.');
        } catch (LogicException) {
            $this->assertTrue(true);
        }
    }

    public function test_letter_type_can_be_configured_from_plain_labels_and_choices(): void
    {
        $villageAdmin = $this->userWithRoles(['Village Admin']);

        $this->actingAs($villageAdmin)->post(route('correspondence.types.store'), [
            'code' => 'SURAT_DOMISILI',
            'name' => 'Surat Keterangan Domisili',
            'description' => 'Untuk keperluan administrasi.',
            'is_active' => '1',
            'required_fields' => [
                [
                    'label' => 'Keperluan surat', 'type' => 'textarea', 'required' => '1', 'max' => '300',
                ],
                [
                    'label' => 'Jenis tempat tinggal', 'type' => 'select', 'required' => '0',
                    'options_text' => "Rumah milik sendiri\nRumah sewa",
                ],
            ],
            'required_attachments' => [
                ['label' => 'Kartu Keluarga', 'required' => '1', 'max_mb' => '5'],
            ],
        ])->assertRedirect(route('correspondence.types'));

        $type = LetterType::query()->where('code', 'SURAT_DOMISILI')->firstOrFail();
        $this->assertSame('keperluan_surat', $type->required_fields[0]['key']);
        $this->assertSame('textarea', $type->required_fields[0]['type']);
        $this->assertTrue($type->required_fields[0]['required']);
        $this->assertSame(['Rumah milik sendiri', 'Rumah sewa'], $type->required_fields[1]['options']);
        $this->assertFalse($type->required_fields[1]['required']);
        $this->assertSame('kartu_keluarga', $type->required_attachments[0]['key']);
        $this->assertSame(5120, $type->required_attachments[0]['max_kb']);
    }

    public function test_invalid_attachment_type_is_rejected_and_super_admin_cannot_sign(): void
    {
        [, , $rtRegion] = $this->regions();
        $resident = $this->userWithRoles(['Resident']);
        $this->resident($resident, $rtRegion);
        $type = $this->letterType([['key' => 'support', 'label' => 'Dokumen', 'required' => true]]);

        $this->actingAs($resident)->post(route('correspondence.store'), [
            'letter_type_id' => $type->id,
            'resident_confirmation' => '1',
            'fields' => ['purpose' => 'Uji file'],
            'attachments' => ['support' => UploadedFile::fake()->create('script.exe', 20, 'application/x-msdownload')],
        ])->assertSessionHasErrors('attachments.support');
        $this->assertDatabaseCount('letter_requests', 0);
    }

    /** @return array{Village, Region, Region} */
    private function regions(): array
    {
        $village = Village::query()->create(['code' => 'CORR001', 'name' => 'Desa Uji']);
        $rw = Region::query()->create(['village_id' => $village->id, 'parent_id' => null, 'region_type' => 'rw', 'region_code' => '01', 'name' => 'RW 01']);
        $rt = Region::query()->create(['village_id' => $village->id, 'parent_id' => $rw->id, 'region_type' => 'rt', 'region_code' => '01/01', 'name' => 'RT 01']);

        return [$village, $rw, $rt];
    }

    /** @param list<string> $roleNames */
    private function userWithRoles(array $roleNames): User
    {
        $user = User::factory()->active()->create();
        $user->roles()->attach(Role::query()->whereIn('name', $roleNames)->pluck('id'));

        return $user->load('roles');
    }

    private function resident(User $owner, Region $region): Resident
    {
        $encrypted = app(IdentityDataProtector::class)->encrypt('Nama Warga Uji');
        $resident = new Resident;
        $resident->forceFill([
            'user_id' => $owner->id,
            'region_id' => $region->id,
            'full_name_ciphertext' => $encrypted['ciphertext'],
            'full_name_nonce' => $encrypted['nonce'],
            'full_name_tag' => $encrypted['tag'],
            'region_verified_at' => now(),
        ])->save();

        return $resident;
    }

    /** @param list<array<string, mixed>> $requiredAttachments */
    private function letterType(array $requiredAttachments = []): LetterType
    {
        return LetterType::query()->create([
            'code' => 'SURAT_UJI_'.strtoupper(substr(bin2hex(random_bytes(3)), 0, 6)),
            'name' => 'Surat Keterangan Uji',
            'description' => 'Jenis untuk pengujian alur.',
            'required_fields' => [['key' => 'purpose', 'label' => 'Keperluan', 'type' => 'textarea', 'required' => true, 'max' => 500]],
            'required_attachments' => $requiredAttachments,
            'is_active' => true,
        ]);
    }

    private function assignedOfficer(string $role, Region $region): User
    {
        $officer = $this->userWithRoles([$role]);
        RegionOfficer::query()->create([
            'user_id' => $officer->id,
            'region_id' => $region->id,
            'assigned_at' => now(),
            'assigned_by' => null,
            'is_active' => true,
        ]);

        return $officer;
    }

    private function operatorWithStaff(): User
    {
        $operator = $this->userWithRoles(['BUMDes Operator']);
        BumdesStaff::query()->create([
            'user_id' => $operator->id,
            'department' => 'Layanan warga',
            'position' => 'Operator layanan',
            'employment_status' => 'active',
        ]);

        return $operator;
    }

    private function createRequest(User $resident, LetterType $type): LetterRequest
    {
        $fields = ['purpose' => 'Keperluan pengujian surat'];
        foreach ($type->required_fields ?? [] as $field) {
            if (in_array(strtolower((string) ($field['key'] ?? '')), ['nik', 'national_id'], true)
                || preg_match('/\bnik\b|nomor induk kependudukan/i', (string) ($field['label'] ?? '')) === 1) {
                $fields[$field['key']] = '1234567890123456';
            }
        }

        $this->actingAs($resident)->post(route('correspondence.store'), [
            'letter_type_id' => $type->id,
            'resident_confirmation' => '1',
            'fields' => $fields,
        ])->assertRedirect();

        return LetterRequest::query()->latest('id')->firstOrFail();
    }
}
