<?php

namespace App\Services;

use App\Models\AssistedServiceLog;
use App\Models\AssistedServiceSession;
use App\Models\LetterDocument;
use App\Models\LetterRequest;
use App\Models\LetterRequestVersion;
use App\Models\LetterType;
use App\Models\Resident;
use App\Models\User;
use App\Notifications\LetterWorkflowNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CorrespondenceWorkflow
{
    /** @param array<string, string|int> $fields
     * @param  array<string, UploadedFile>  $attachments
     */
    public function create(User $actor, LetterType $type, array $fields, array $attachments, ?int $residentId = null, ?int $assistanceSessionId = null): LetterRequest
    {
        abort_unless($actor->can('create', LetterRequest::class), 403);
        abort_unless($type->is_active, 404);

        $isResidentSubmission = $actor->hasRole('Resident');
        if ($isResidentSubmission) {
            abort_unless($residentId === null || (int) $actor->resident?->getKey() === $residentId, 403);
            $resident = $actor->resident;
        } else {
            abort_unless($actor->hasRole('BUMDes Operator') || $actor->hasRole('BUMDes Manager'), 403);
            abort_unless($assistanceSessionId !== null, 403);
            $session = AssistedServiceSession::query()->whereKey($assistanceSessionId)
                ->where('operator_id', $actor->getKey())->where('service_type', 'correspondence')
                ->where('status', 'in_progress')->first();
            abort_unless($session !== null && ($residentId === null || (int) $session->resident_id === $residentId), 403);
            $resident = $session->resident;
        }

        abort_unless($resident instanceof Resident && $resident->region_verified_at !== null, 422, 'Profil warga harus terverifikasi sebelum mengajukan surat.');
        if (! $isResidentSubmission) {
            abort_unless($resident->user?->account_status === 'active' && $resident->user->hasRole('Resident'), 422, 'Akun warga tidak dapat menerima permohonan berbantuan.');
        }
        $this->assertConfiguredFields($type, $fields, $attachments);
        $fields = $this->configuredValues($type, $fields);
        $attachments = $this->configuredAttachments($type, $attachments);

        return DB::transaction(function () use ($actor, $type, $fields, $attachments, $resident, $isResidentSubmission, $assistanceSessionId): LetterRequest {
            $session = null;
            if (! $isResidentSubmission) {
                $session = AssistedServiceSession::query()->whereKey($assistanceSessionId)->lockForUpdate()->firstOrFail();
                abort_unless((int) $session->operator_id === (int) $actor->getKey()
                    && (int) $session->resident_id === (int) $resident->getKey()
                    && $session->service_type === 'correspondence' && $session->status === 'in_progress'
                    && $session->reference_type === null, 409, 'Permintaan bantuan sudah berubah atau tidak lagi tersedia.');
            }
            $encrypted = $this->encrypt($fields);
            $confirmedAt = $isResidentSubmission ? now() : null;
            $letterRequest = LetterRequest::query()->create([
                'resident_id' => $resident->getKey(),
                'created_by' => $actor->getKey(),
                'letter_type_id' => $type->getKey(),
                'request_number' => $this->requestNumber(),
                'submission_channel' => $isResidentSubmission ? 'self_service' : 'assisted_bumdes',
                'encrypted_data' => $encrypted,
                'letter_type_snapshot' => [
                    'code' => $type->code,
                    'name' => $type->name,
                    'required_fields' => $type->required_fields ?? [],
                    'required_attachments' => $type->required_attachments ?? [],
                    'template_path' => $type->template_path,
                ],
                'status' => $isResidentSubmission ? 'rt_review' : 'draft',
                'resident_confirmed_at' => $confirmedAt,
                'submitted_at' => $confirmedAt,
                'revision_number' => 0,
            ]);

            $letterRequest->versions()->create([
                'changed_by' => $actor->getKey(),
                'version' => 1,
                'encrypted_data' => $encrypted,
                'submitted_at' => $confirmedAt,
            ]);
            $this->storeAttachments($letterRequest, $actor, 1, $attachments);
            $this->audit('letter.request_created', $letterRequest, ['channel' => $letterRequest->submission_channel]);

            if (! $isResidentSubmission) {
                $session->forceFill([
                    'reference_type' => 'letter_request',
                    'reference_id' => $letterRequest->getKey(),
                    'status' => 'awaiting_resident_confirmation',
                    'awaiting_confirmation_at' => now(),
                ])->save();
                AssistedServiceLog::query()->create([
                    'session_id' => $session->getKey(),
                    'actor_id' => $actor->getKey(),
                    'action' => 'assisted_letter_prepared_for_resident_review',
                    'metadata' => ['request_number' => $letterRequest->request_number],
                    'created_at' => now(),
                ]);
            }

            if ($isResidentSubmission) {
                $this->notifyNextStage($letterRequest, 'rt_review');
                $resident->user->notify($this->notification($letterRequest, 'Permohonan surat Anda telah dikirim untuk pemeriksaan RT.', 'rt_review'));
            } else {
                $resident->user->notify($this->notification($letterRequest, 'Permohonan bantuan surat menunggu konfirmasi Anda.', 'draft'));
            }

            return $letterRequest->load(['resident', 'letterType']);
        });
    }

    public function confirmAssisted(User $residentUser, LetterRequest $letterRequest): LetterRequest
    {
        abort_unless($letterRequest->submission_channel === 'assisted_bumdes'
            && $letterRequest->status === 'draft'
            && (int) $letterRequest->resident?->user_id === (int) $residentUser->getKey(), 404);

        return DB::transaction(function () use ($residentUser, $letterRequest): LetterRequest {
            $locked = LetterRequest::query()->whereKey($letterRequest->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($locked->status === 'draft'
                && $locked->submission_channel === 'assisted_bumdes'
                && (int) $locked->resident->user_id === (int) $residentUser->getKey(), 404);
            $locked->forceFill([
                'resident_confirmed_at' => now(),
                'submitted_at' => now(),
                'status' => 'rt_review',
            ])->save();
            $this->appendVersion($locked, $residentUser, $this->decrypt($locked), now());
            $this->audit('letter.assisted_request_confirmed', $locked, ['status' => 'rt_review']);
            $session = AssistedServiceSession::query()->where('reference_type', 'letter_request')
                ->where('reference_id', $locked->getKey())->where('resident_id', $locked->resident_id)
                ->where('operator_id', $locked->created_by)->where('status', 'awaiting_resident_confirmation')
                ->lockForUpdate()->first();
            if ($session !== null) {
                $session->forceFill([
                    'status' => 'completed',
                    'resident_confirmed_at' => now(),
                    'completed_at' => now(),
                ])->save();
                AssistedServiceLog::query()->create([
                    'session_id' => $session->getKey(),
                    'actor_id' => $residentUser->getKey(),
                    'action' => 'resident_confirmed_assisted_letter',
                    'metadata' => ['request_number' => $locked->request_number],
                    'created_at' => now(),
                ]);
            }
            $this->notifyNextStage($locked, 'rt_review');
            $locked->resident->user->notify($this->notification($locked, 'Permohonan surat Anda telah dikirim untuk pemeriksaan RT.', 'rt_review'));

            return $locked;
        });
    }

    /** @param array<string, string|int> $fields
     * @param  array<string, UploadedFile>  $attachments
     */
    public function revise(User $residentUser, LetterRequest $letterRequest, array $fields, array $attachments): LetterRequest
    {
        abort_unless($residentUser->can('update', $letterRequest), 403);
        $type = $letterRequest->effectiveLetterType();
        $this->assertConfiguredFields($type, $fields, $attachments, false);
        $fields = $this->configuredValues($type, $fields);
        $attachments = $this->configuredAttachments($type, $attachments);

        return DB::transaction(function () use ($residentUser, $letterRequest, $fields, $attachments): LetterRequest {
            $locked = LetterRequest::query()->whereKey($letterRequest->getKey())->lockForUpdate()->firstOrFail();
            abort_unless($residentUser->can('update', $locked), 403);
            $encrypted = $this->encrypt($fields);
            $version = (int) $locked->versions()->max('version') + 1;
            $locked->forceFill([
                'encrypted_data' => $encrypted,
                'status' => 'rt_review',
                'submitted_at' => now(),
                'rejection_reason' => null,
                'revision_number' => $locked->revision_number + 1,
            ])->save();
            $this->appendVersion($locked, $residentUser, $fields, now(), $encrypted, $version);
            $this->storeAttachments($locked, $residentUser, $version, $attachments);
            $this->audit('letter.request_resubmitted', $locked, ['revision_number' => $locked->revision_number]);
            $this->notifyNextStage($locked, 'rt_review');

            return $locked;
        });
    }

    public function decide(User $actor, LetterRequest $letterRequest, string $decision, ?string $reason): LetterRequest
    {
        return DB::transaction(function () use ($actor, $letterRequest, $decision, $reason): LetterRequest {
            $locked = LetterRequest::query()->whereKey($letterRequest->getKey())->lockForUpdate()->with(['resident.region', 'letterType'])->firstOrFail();
            abort_unless($actor->can('review', $locked), 403);
            if (in_array($decision, ['rejected', 'revision_required'], true) && trim((string) $reason) === '') {
                throw ValidationException::withMessages(['comments' => 'Alasan wajib diisi untuk penolakan atau permintaan revisi.']);
            }

            $stage = $locked->status;
            $role = match ($stage) {
                'rt_review' => 'RT',
                'rw_review' => 'RW',
                'village_review' => 'Village Admin',
                default => throw ValidationException::withMessages(['decision' => 'Permohonan tidak berada pada tahap pemeriksaan yang dapat diproses.']),
            };
            $nextStatus = match (true) {
                $decision === 'rejected' => 'rejected',
                $decision === 'revision_required' => 'revision_required',
                $stage === 'rt_review' => 'rw_review',
                $stage === 'rw_review' => 'village_review',
                default => 'signature_pending',
            };

            $locked->approvals()->create([
                'reviewer_id' => $actor->getKey(),
                'actor_role' => $role,
                'approval_level' => match ($stage) {
                    'rt_review' => 'rt', 'rw_review' => 'rw', default => 'village_admin',
                },
                'decision' => $decision,
                'comments' => $reason === null ? null : trim($reason),
                'resident_visible' => in_array($decision, ['rejected', 'revision_required'], true),
                'reviewed_at' => now(),
            ]);
            $locked->forceFill([
                'status' => $nextStatus,
                'rejection_reason' => $decision === 'rejected' ? trim((string) $reason) : null,
            ])->save();

            if ($nextStatus === 'signature_pending') {
                $this->createDocument($locked, null);
            }

            $this->audit('letter.workflow_decided', $locked, ['stage' => $stage, 'decision' => $decision, 'status' => $nextStatus]);
            if (in_array($nextStatus, ['rejected', 'revision_required'], true)) {
                $locked->resident->user->notify($this->notification($locked, $nextStatus === 'rejected' ? 'Permohonan surat ditolak. Buka permohonan untuk melihat alasan.' : 'Permohonan surat perlu diperbaiki. Buka permohonan untuk melihat catatan revisi.', $nextStatus));
            } else {
                $this->notifyNextStage($locked, $nextStatus);
                $residentMessage = match ($stage) {
                    'rt_review' => 'RT menyetujui permohonan. Permohonan diteruskan ke RW.',
                    'rw_review' => 'RW menyetujui permohonan. Permohonan diteruskan ke administrasi desa.',
                    default => 'Administrasi desa menyetujui permohonan. Permohonan menunggu Kepala Desa.',
                };
                $locked->resident->user->notify($this->notification($locked, $residentMessage, $nextStatus));
            }

            return $locked;
        });
    }

    public function sign(User $villageHead, LetterRequest $letterRequest, string $decision, ?string $reason): LetterRequest
    {
        return DB::transaction(function () use ($villageHead, $letterRequest, $decision, $reason): LetterRequest {
            $locked = LetterRequest::query()->whereKey($letterRequest->getKey())->lockForUpdate()->with(['resident.region', 'letterType'])->firstOrFail();
            abort_unless($villageHead->can('sign', $locked), 403);
            if ($decision === 'rejected' && trim((string) $reason) === '') {
                throw ValidationException::withMessages(['comments' => 'Alasan wajib diisi untuk penolakan.']);
            }

            if ($decision === 'rejected') {
                $locked->approvals()->create([
                    'reviewer_id' => $villageHead->getKey(),
                    'actor_role' => 'Village Head',
                    'approval_level' => 'village_head',
                    'decision' => 'rejected',
                    'comments' => trim((string) $reason),
                    'resident_visible' => true,
                    'reviewed_at' => now(),
                ]);
                $locked->forceFill(['status' => 'rejected', 'rejection_reason' => trim((string) $reason)])->save();
            } else {
                $signedAt = now();
                $locked->approvals()->create([
                    'reviewer_id' => $villageHead->getKey(),
                    'actor_role' => 'Village Head',
                    'approval_level' => 'village_head',
                    'decision' => 'signed',
                    'comments' => 'Prototype signature action; not PSrE-certified.',
                    'resident_visible' => false,
                    'reviewed_at' => $signedAt,
                ]);
                $this->createDocument($locked, [
                    'user_id' => (int) $villageHead->getKey(),
                    'signer' => $villageHead->username,
                    'signed_at' => $signedAt,
                    'signature_type' => 'prototype',
                ]);
                $this->eraseNikFromRequestHistory($locked);
                $locked->forceFill(['status' => 'completed', 'completed_at' => $signedAt])->save();
            }

            $this->audit('letter.village_head_decision', $locked, ['decision' => $decision, 'status' => $locked->status]);
            $locked->resident->user->notify($this->notification($locked, $decision === 'signed' ? 'Dokumen surat prototipe telah diterbitkan.' : 'Permohonan surat ditolak pada tahap Kepala Desa.', $locked->status));

            return $locked;
        });
    }

    /** @return array<string, string> */
    public function fields(LetterRequest $letterRequest): array
    {
        $envelope = $letterRequest->encrypted_data;
        $decrypted = app(IdentityDataProtector::class)->decrypt($envelope['ciphertext'], $envelope['nonce'], $envelope['tag']);
        $values = json_decode($decrypted, true, flags: JSON_THROW_ON_ERROR);
        $labels = collect($letterRequest->effectiveLetterType()->required_fields)->keyBy('key');

        return collect($values)->mapWithKeys(fn ($value, $key): array => [
            (string) ($labels->get($key)['label'] ?? $key) => is_scalar($value) ? (string) $value : '',
        ])->all();
    }

    private function encrypt(array $fields): array
    {
        return app(IdentityDataProtector::class)->encrypt(json_encode($fields, JSON_THROW_ON_ERROR));
    }

    /**
     * The final issued PDF keeps the NIK needed on the official letter. Remove it
     * from the live encrypted payload and every encrypted revision in the database.
     */
    private function eraseNikFromRequestHistory(LetterRequest $letterRequest): void
    {
        $nikKeys = collect($letterRequest->effectiveLetterType()->required_fields ?? [])
            ->filter(fn (array $field): bool => $this->isNikField($field))
            ->pluck('key')->map(fn ($key): string => (string) $key)->all();

        if ($nikKeys === []) {
            return;
        }

        $current = $this->payloadWithoutNik($letterRequest->encrypted_data, $nikKeys);
        $letterRequest->forceFill(['encrypted_data' => $current])->save();

        $letterRequest->versions()->get()->each(function (LetterRequestVersion $version) use ($nikKeys): void {
            $clean = $this->payloadWithoutNik($version->encrypted_data, $nikKeys);
            DB::table('letter_request_versions')->where('id', $version->getKey())->update([
                'encrypted_data' => json_encode($clean, JSON_THROW_ON_ERROR),
            ]);
        });
    }

    /** @param array<string, mixed> $field */
    private function isNikField(array $field): bool
    {
        return in_array(strtolower((string) ($field['key'] ?? '')), ['nik', 'national_id'], true)
            || preg_match('/\bnik\b|nomor induk kependudukan/i', (string) ($field['label'] ?? '')) === 1;
    }

    /** @param array{ciphertext: string, nonce: string, tag: string} $envelope
     * @param  list<string>  $configuredNikKeys
     * @return array{ciphertext: string, nonce: string, tag: string}
     */
    private function payloadWithoutNik(array $envelope, array $configuredNikKeys): array
    {
        $plaintext = app(IdentityDataProtector::class)->decrypt($envelope['ciphertext'], $envelope['nonce'], $envelope['tag']);
        $fields = json_decode($plaintext, true, flags: JSON_THROW_ON_ERROR);
        $keys = array_fill_keys(array_map('strtolower', $configuredNikKeys), true);

        foreach (array_keys($fields) as $key) {
            if (isset($keys[strtolower((string) $key)]) || in_array(strtolower((string) $key), ['nik', 'national_id'], true)) {
                unset($fields[$key]);
            }
        }

        return $this->encrypt($fields);
    }

    /** @return array<string, string|int> */
    private function decrypt(LetterRequest $letterRequest): array
    {
        $envelope = $letterRequest->encrypted_data;
        $plaintext = app(IdentityDataProtector::class)->decrypt($envelope['ciphertext'], $envelope['nonce'], $envelope['tag']);

        return json_decode($plaintext, true, flags: JSON_THROW_ON_ERROR);
    }

    /** @param array<string, UploadedFile> $files */
    private function storeAttachments(LetterRequest $letterRequest, User $actor, int $version, array $files): void
    {
        foreach ($files as $key => $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $extension = strtolower($file->extension());
            $filename = Str::random(48).'.'.$extension;
            $path = 'letters/'.$letterRequest->getKey().'/attachments/'.$filename;
            $contents = file_get_contents($file->getRealPath());
            if (! is_string($contents) || ! Storage::disk('local')->put($path, $contents)) {
                throw new RuntimeException('Unable to store a private correspondence attachment.');
            }

            $letterRequest->attachments()->create([
                'uploaded_by' => $actor->getKey(),
                'request_version' => $version,
                'attachment_key' => $key,
                'private_file_path' => $path,
                'original_filename' => mb_substr(basename($file->getClientOriginalName()), 0, 255),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'file_size' => strlen($contents),
                'file_hash' => hash('sha256', $contents),
                'uploaded_at' => now(),
            ]);
        }
    }

    /** @param array<string, string|int> $fields
     * @param  array<string, UploadedFile>  $attachments
     */
    private function assertConfiguredFields(LetterType $type, array $fields, array $attachments, bool $requireFiles = true): void
    {
        foreach ($type->required_fields ?? [] as $field) {
            if (($field['required'] ?? true) && (! array_key_exists($field['key'], $fields) || trim((string) $fields[$field['key']]) === '')) {
                throw ValidationException::withMessages(['fields.'.$field['key'] => 'Isian ini wajib dilengkapi.']);
            }
        }

        if (! $requireFiles) {
            return;
        }
        foreach ($type->required_attachments ?? [] as $attachment) {
            if (($attachment['required'] ?? true) && ! isset($attachments[$attachment['key']])) {
                throw ValidationException::withMessages(['attachments.'.$attachment['key'] => 'Lampiran ini wajib diunggah.']);
            }
        }
    }

    /** @param array<string, string|int> $fields
     * @return array<string, string|int>
     */
    private function configuredValues(LetterType $type, array $fields): array
    {
        $keys = collect($type->required_fields ?? [])->pluck('key')->all();

        return array_intersect_key($fields, array_fill_keys($keys, true));
    }

    /** @param array<string, UploadedFile> $files
     * @return array<string, UploadedFile>
     */
    private function configuredAttachments(LetterType $type, array $files): array
    {
        $keys = collect($type->required_attachments ?? [])->pluck('key')->all();

        return array_intersect_key($files, array_fill_keys($keys, true));
    }

    /** @param array<string, string|int>|null $values */
    private function appendVersion(LetterRequest $letterRequest, User $actor, ?array $values, ?\DateTimeInterface $submittedAt, ?array $encrypted = null, ?int $version = null): LetterRequestVersion
    {
        return $letterRequest->versions()->create([
            'changed_by' => $actor->getKey(),
            'version' => $version ?? ((int) $letterRequest->versions()->max('version') + 1),
            'encrypted_data' => $encrypted ?? $this->encrypt($values ?? []),
            'submitted_at' => $submittedAt,
        ]);
    }

    /** @param array{user_id: int, signer: string, signed_at: Carbon, signature_type: string}|null $signature */
    private function createDocument(LetterRequest $letterRequest, ?array $signature): LetterDocument
    {
        $version = (int) $letterRequest->documents()->max('version') + 1;
        $pdf = app(LetterPdfRenderer::class)->render($letterRequest, $this->fields($letterRequest), $signature);
        $path = 'letters/'.$letterRequest->getKey().'/documents/'.sprintf('version-%04d.pdf', $version);
        if (! Storage::disk('local')->put($path, $pdf)) {
            throw new RuntimeException('Unable to store a private correspondence document.');
        }

        return $letterRequest->documents()->create([
            'document_number' => $letterRequest->request_number.'/'.sprintf('%02d', $version),
            'private_file_path' => $path,
            'document_hash' => hash('sha256', $pdf),
            'version' => $version,
            'signed_by' => $signature['user_id'] ?? null,
            'signed_at' => $signature['signed_at'] ?? null,
            'signature_type' => $signature['signature_type'] ?? null,
            'issued_at' => $signature['signed_at'] ?? null,
            'created_at' => now(),
        ]);
    }

    private function requestNumber(): string
    {
        do {
            $number = 'LTR-'.now()->format('Ymd').'-'.Str::upper(Str::random(8));
        } while (LetterRequest::query()->where('request_number', $number)->exists());

        return $number;
    }

    /** @param array<string, scalar|null> $metadata */
    private function audit(string $action, LetterRequest $letterRequest, array $metadata = []): void
    {
        app(AuditLogger::class)->record($action, 'letter_request', (int) $letterRequest->getKey(), $metadata);
    }

    private function notifyNextStage(LetterRequest $letterRequest, string $status): void
    {
        $letterRequest->loadMissing('resident.region');
        $region = $letterRequest->resident->region;
        $targets = match ($status) {
            'rt_review' => $this->assignedUsers('RT', (int) $region?->getKey()),
            'rw_review' => $this->assignedUsers('RW', (int) $region?->parent_id),
            'village_review' => $this->roleUsers('Village Admin'),
            'signature_pending' => $this->roleUsers('Village Head'),
            default => collect(),
        };
        $message = match ($status) {
            'rt_review' => 'Permohonan baru menunggu pemeriksaan RT.',
            'rw_review' => 'Permohonan menunggu pemeriksaan RW.',
            'village_review' => 'Permohonan menunggu pemeriksaan administrasi desa.',
            'signature_pending' => 'Permohonan menunggu keputusan Kepala Desa.',
            default => 'Status permohonan surat diperbarui.',
        };

        Notification::send($targets, $this->notification($letterRequest, $message, $status));
    }

    private function assignedUsers(string $role, int $regionId): Collection
    {
        return User::query()->where('account_status', 'active')
            ->whereHas('roles', fn ($query) => $query->where('name', $role))
            ->whereHas('regionOfficerAssignments', fn ($query) => $query->where('is_active', true)->where('region_id', $regionId))
            ->get();
    }

    private function roleUsers(string $role): Collection
    {
        return User::query()->where('account_status', 'active')
            ->whereHas('roles', fn ($query) => $query->where('name', $role))->get();
    }

    private function notification(LetterRequest $letterRequest, string $message, string $status): LetterWorkflowNotification
    {
        return new LetterWorkflowNotification([
            'title' => 'Pembaruan permohonan surat',
            'message' => $message,
            'request_number' => $letterRequest->request_number,
            'status' => $status,
            'request_id' => (int) $letterRequest->getKey(),
        ]);
    }
}
