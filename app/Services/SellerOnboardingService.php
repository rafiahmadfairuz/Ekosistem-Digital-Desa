<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Seller;
use App\Models\SellerStatusEvent;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class SellerOnboardingService
{
    public function __construct(private readonly AuditLogger $audit, private readonly MarketplaceNotifier $notifier) {}

    /** @param array{business_name: string, business_description?: ?string, business_address?: ?string, contact_phone?: ?string} $data */
    public function apply(User $applicant, array $data, ?UploadedFile $document = null): Seller
    {
        abort_unless($applicant->can('apply', Seller::class), 403);

        return DB::transaction(function () use ($applicant, $data, $document): Seller {
            $seller = Seller::query()->create([
                'user_id' => $applicant->getKey(),
                'business_name' => trim($data['business_name']),
                'business_description' => isset($data['business_description']) ? trim($data['business_description']) : null,
                'business_address' => isset($data['business_address']) ? trim($data['business_address']) : null,
                'contact_phone' => isset($data['contact_phone']) ? trim($data['contact_phone']) : null,
                'verification_status' => 'pending',
            ]);
            SellerStatusEvent::query()->create(['seller_id' => $seller->getKey(), 'actor_id' => $applicant->getKey(), 'from_status' => null, 'to_status' => 'pending', 'created_at' => now()]);
            if ($document !== null) {
                $this->storeDocument($seller, $applicant, $document);
            }
            $this->audit->record('marketplace.seller_application_submitted', 'seller', (int) $seller->getKey());
            $this->notifier->toActiveMarketplaceReviewers('Pengajuan penjual baru', 'Ada pengajuan penjual yang menunggu peninjauan.', 'seller', (int) $seller->getKey());

            return $seller;
        });
    }

    /** @param array{business_name: string, business_description?: ?string, business_address?: ?string, contact_phone?: ?string} $data */
    public function resubmit(User $applicant, Seller $seller, array $data, ?UploadedFile $document = null): Seller
    {
        abort_unless($applicant->can('update', $seller), 403);

        return DB::transaction(function () use ($applicant, $seller, $data, $document): Seller {
            $locked = Seller::query()->whereKey($seller->getKey())->lockForUpdate()->firstOrFail();
            abort_unless((int) $locked->user_id === (int) $applicant->getKey()
                && in_array($locked->verification_status, ['rejected', 'inactive'], true), 403);
            $oldStatus = $locked->verification_status;
            $locked->forceFill([
                'business_name' => trim($data['business_name']),
                'business_description' => isset($data['business_description']) ? trim($data['business_description']) : null,
                'business_address' => isset($data['business_address']) ? trim($data['business_address']) : null,
                'contact_phone' => isset($data['contact_phone']) ? trim($data['contact_phone']) : null,
                'verification_status' => 'pending',
                'reviewed_by' => null,
                'reviewed_at' => null,
                'rejection_reason' => null,
            ])->save();
            SellerStatusEvent::query()->create(['seller_id' => $locked->getKey(), 'actor_id' => $applicant->getKey(), 'from_status' => $oldStatus, 'to_status' => 'pending', 'created_at' => now()]);
            if ($document !== null) {
                $this->storeDocument($locked, $applicant, $document);
            }
            $this->audit->record('marketplace.seller_application_resubmitted', 'seller', (int) $locked->getKey());

            return $locked;
        });
    }

    public function review(User $reviewer, Seller $seller, string $status, ?string $reason): Seller
    {
        abort_unless($reviewer->can('review', Seller::class), 403);
        if ($status === 'rejected' && trim((string) $reason) === '') {
            throw ValidationException::withMessages(['reason' => 'Alasan penolakan wajib diisi.']);
        }

        return DB::transaction(function () use ($reviewer, $seller, $status, $reason): Seller {
            $locked = Seller::query()->whereKey($seller->getKey())->lockForUpdate()->firstOrFail();
            abort_unless((int) $locked->user_id !== (int) $reviewer->getKey(), 403, 'Penjual tidak dapat meninjau pengajuannya sendiri.');
            $allowed = match ($locked->verification_status) {
                'pending' => ['under_review', 'approved', 'rejected'],
                'under_review' => ['approved', 'rejected'],
                'approved' => ['suspended', 'inactive'],
                'suspended', 'inactive' => ['approved', 'inactive'],
                'rejected' => ['under_review'],
                default => [],
            };
            abort_unless(in_array($status, $allowed, true), 409, 'Perubahan status penjual tidak diizinkan.');

            $previous = $locked->verification_status;
            $locked->forceFill([
                'verification_status' => $status,
                'reviewed_by' => $reviewer->getKey(),
                'reviewed_at' => now(),
                'rejection_reason' => $status === 'rejected' ? trim((string) $reason) : null,
            ])->save();
            SellerStatusEvent::query()->create([
                'seller_id' => $locked->getKey(), 'actor_id' => $reviewer->getKey(),
                'from_status' => $previous, 'to_status' => $status,
                'reason' => $reason === null ? null : trim($reason), 'created_at' => now(),
            ]);
            if ($status === 'approved') {
                $sellerRole = Role::query()->where('name', 'Seller')->firstOrFail();
                $locked->user->roles()->syncWithoutDetaching([$sellerRole->getKey() => ['assigned_at' => now(), 'assigned_by' => $reviewer->getKey()]]);
            }
            $this->audit->record('marketplace.seller_status_changed', 'seller', (int) $locked->getKey(), ['from_status' => $previous, 'to_status' => $status]);
            $message = match ($status) {
                'approved' => 'Pengajuan penjual Anda disetujui. Kini Anda dapat mengelola produk.',
                'rejected' => 'Pengajuan penjual Anda belum disetujui. Buka status pengajuan untuk membaca alasannya.',
                'suspended' => 'Status penjual Anda ditangguhkan. Produk tidak lagi tampil di katalog.',
                'under_review' => 'Pengajuan penjual Anda sedang ditinjau.',
                default => 'Status penjual Anda diperbarui.',
            };
            $this->notifier->toUser($locked->user, 'Pembaruan status penjual', $message, 'seller', (int) $locked->getKey());

            return $locked;
        });
    }

    private function storeDocument(Seller $seller, User $actor, UploadedFile $file): void
    {
        $extension = strtolower($file->extension());
        $path = 'marketplace/sellers/'.$seller->getKey().'/'.Str::random(48).'.'.$extension;
        $contents = file_get_contents($file->getRealPath());
        if (! is_string($contents) || ! Storage::disk('local')->put($path, $contents)) {
            throw new RuntimeException('Dokumen penjual tidak dapat disimpan secara privat.');
        }

        $seller->documents()->create([
            'uploaded_by' => $actor->getKey(),
            'document_type' => 'supporting',
            'private_file_path' => $path,
            'original_filename' => null,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'file_size' => strlen($contents),
            'file_hash' => hash('sha256', $contents),
            'uploaded_at' => now(),
        ]);
    }
}
