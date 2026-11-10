<?php

namespace App\Http\Controllers;

use App\Http\Requests\DecideLetterRequest;
use App\Http\Requests\SignLetterRequest;
use App\Http\Requests\StoreLetterTypeRequest;
use App\Http\Requests\SubmitLetterRequest;
use App\Models\AssistedServiceSession;
use App\Models\LetterAttachment;
use App\Models\LetterDocument;
use App\Models\LetterRequest;
use App\Models\LetterType;
use App\Models\Region;
use App\Services\AuditLogger;
use App\Services\CorrespondenceWorkflow;
use App\Services\LetterProfilePrefill;
use App\Services\ResidentProfileData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CorrespondenceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', LetterRequest::class);
        $user = $request->user();
        $query = LetterRequest::query()->with('letterType')->where(function (Builder $scope) use ($user): void {
            // Build this visibility union as deny-by-default. For example, a new
            // Resident without a Resident profile has no owner predicate yet.
            $scope->whereRaw('1 = 0');

            if ($user->hasRole('Resident') && $user->resident !== null) {
                $scope->orWhere('resident_id', $user->resident->getKey());
            }

            if ($user->hasRole('BUMDes Operator') || $user->hasRole('BUMDes Manager')) {
                $scope->orWhere(fn (Builder $assisted) => $assisted->where('created_by', $user->getKey())->where('submission_channel', 'assisted_bumdes'));
            }

            $rtRegions = $user->regionOfficerAssignments()->where('is_active', true)->whereHas('region', fn ($region) => $region->where('region_type', 'rt'))->pluck('region_id');
            if ($user->hasRole('RT') && $rtRegions->isNotEmpty()) {
                $scope->orWhere(fn (Builder $stage) => $stage->where('status', 'rt_review')->whereHas('resident', fn ($resident) => $resident->whereIn('region_id', $rtRegions)));
            }

            $rwRegions = $user->regionOfficerAssignments()->where('is_active', true)->whereHas('region', fn ($region) => $region->where('region_type', 'rw'))->pluck('region_id');
            if ($user->hasRole('RW') && $rwRegions->isNotEmpty()) {
                $rtChildren = Region::query()->where('region_type', 'rt')->whereIn('parent_id', $rwRegions)->pluck('id');
                $scope->orWhere(fn (Builder $stage) => $stage->where('status', 'rw_review')->whereHas('resident', fn ($resident) => $resident->whereIn('region_id', $rtChildren)));
            }

            if ($user->hasRole('Village Admin')) {
                $scope->orWhere('status', 'village_review');
            }
            if ($user->hasRole('Village Head')) {
                $scope->orWhere('status', 'signature_pending');
            }
        });

        $notifications = DB::table('notifications')->where('user_id', $user->getKey())
            ->where('reference_type', 'letter_request')->latest('created_at')->limit(12)->get()
            ->map(function (object $notification) use ($user): ?array {
                $letterRequest = LetterRequest::query()->with(['resident', 'letterType'])->find($notification->reference_id);
                if ($letterRequest === null || ! $user->can('view', $letterRequest)) {
                    return null;
                }

                return [
                    'id' => (int) $notification->id,
                    'message' => (string) $notification->message,
                    'request_number' => $letterRequest->request_number,
                    'url' => route('correspondence.show', $letterRequest),
                    'created_at' => $notification->created_at,
                    'read_at' => $notification->read_at,
                ];
            })->filter()->values();

        return view('correspondence.index', [
            'letters' => $query->latest('updated_at')->simplePaginate(20)->withQueryString(),
            'canCreate' => $user->can('create', LetterRequest::class)
                && (! $user->hasRole('Resident') || $user->resident?->region_verified_at !== null),
            'residentNeedsVerifiedRegion' => $user->hasRole('Resident') && $user->resident?->region_verified_at === null,
            'canManageTypes' => $user->can('manageTypes', LetterRequest::class),
            'canAssist' => ($user->hasRole('BUMDes Operator') || $user->hasRole('BUMDes Manager')) && $user->bumdesStaff()->where('employment_status', 'active')->exists(),
            'notifications' => $notifications,
        ]);
    }

    public function markNotificationRead(Request $request, int $notification): RedirectResponse
    {
        $record = DB::table('notifications')->where('id', $notification)
            ->where('user_id', $request->user()->getKey())
            ->where('reference_type', 'letter_request')->first();
        abort_if($record === null, 404);
        $letterRequest = LetterRequest::query()->with('resident')->findOrFail($record->reference_id);
        $this->authorize('view', $letterRequest);
        DB::table('notifications')->where('id', $record->id)->where('user_id', $request->user()->getKey())
            ->whereNull('read_at')->update(['read_at' => now()]);

        return redirect()->route('correspondence.show', $letterRequest);
    }

    public function create(Request $request, ResidentProfileData $profileData, LetterProfilePrefill $prefill): View|RedirectResponse
    {
        $this->authorize('create', LetterRequest::class);
        $user = $request->user();
        $resident = $user->resident;
        $assisted = ($user->hasRole('BUMDes Operator') || $user->hasRole('BUMDes Manager')) && ! $user->hasRole('Resident');
        $assistanceSession = null;
        if ($assisted) {
            $assistanceSession = AssistedServiceSession::query()->with('resident.user')
                ->whereKey($request->query('assistance_session_id'))
                ->where('operator_id', $user->getKey())
                ->where('service_type', 'correspondence')->where('status', 'in_progress')->first();
            if ($assistanceSession === null) {
                return redirect()->route('bumdes.assistance.index')->with('status', 'Terima permintaan bantuan persuratan terlebih dahulu sebelum menyiapkan draf.');
            }
            $resident = $assistanceSession->resident;
        }
        if ($user->hasRole('Resident') && ($resident === null || $resident->region_verified_at === null)) {
            return redirect()->route('profile.show')->with('status', 'Lengkapi profil warga dan tunggu verifikasi wilayah sebelum mengajukan surat.');
        }

        $selectedType = LetterType::query()->where('is_active', true)->find($request->query('type_id'));
        $profilePrefill = $resident !== null && $selectedType !== null
            ? $prefill->values($selectedType->required_fields ?? [], $profileData->forOwner($resident) + ['email' => $resident->user?->email])
            : [];

        return view('correspondence.create', [
            'types' => LetterType::query()->where('is_active', true)->orderBy('name')->get(),
            'selectedType' => $selectedType,
            'assisted' => $assisted,
            'assistanceSession' => $assistanceSession,
            'assistedResidentUsername' => $assisted ? $resident->user?->username : null,
            'profilePrefill' => $profilePrefill,
        ]);
    }

    public function store(SubmitLetterRequest $request, CorrespondenceWorkflow $workflow): RedirectResponse
    {
        $data = $request->validated();
        $type = LetterType::query()->findOrFail($data['letter_type_id']);
        $assistanceSessionId = ($request->user()->hasRole('BUMDes Operator') || $request->user()->hasRole('BUMDes Manager')) && ! $request->user()->hasRole('Resident')
            ? (int) $data['assistance_session_id']
            : null;
        $letterRequest = $workflow->create(
            $request->user(),
            $type,
            $data['fields'],
            $data['attachments'] ?? [],
            null,
            $assistanceSessionId,
        );

        return redirect()->route('correspondence.show', $letterRequest)->with('status', $letterRequest->status === 'draft'
            ? 'Draf bantuan dibuat. Minta warga masuk ke akunnya untuk memeriksa dan mengonfirmasi permohonan.'
            : 'Permohonan surat berhasil dikirim untuk pemeriksaan RT.');
    }

    public function show(Request $request, LetterRequest $letterRequest, CorrespondenceWorkflow $workflow): View
    {
        $letterRequest->load(['resident.region', 'letterType', 'creator', 'approvals.reviewer', 'attachments', 'documents.signer', 'versions']);
        $this->authorize('view', $letterRequest);
        $isResident = (int) $letterRequest->resident->user_id === (int) $request->user()->getKey();
        $approvals = $isResident ? $letterRequest->approvals->where('resident_visible', true) : $letterRequest->approvals;

        return view('correspondence.show', [
            'letterRequest' => $letterRequest,
            'fields' => $workflow->fields($letterRequest),
            'approvals' => $approvals,
            'isResident' => $isResident,
            'canReview' => $request->user()->can('review', $letterRequest),
            'canSign' => $request->user()->can('sign', $letterRequest),
            'canRevise' => $request->user()->can('update', $letterRequest),
            'canConfirm' => $letterRequest->status === 'draft' && $letterRequest->submission_channel === 'assisted_bumdes' && $isResident,
        ]);
    }

    public function confirm(Request $request, LetterRequest $letterRequest, CorrespondenceWorkflow $workflow): RedirectResponse
    {
        abort_unless((int) $letterRequest->resident?->user_id === (int) $request->user()->getKey(), 404);
        $workflow->confirmAssisted($request->user(), $letterRequest);

        return redirect()->route('correspondence.show', $letterRequest)->with('status', 'Permohonan bantuan telah Anda konfirmasi dan dikirim ke RT.');
    }

    public function revise(SubmitLetterRequest $request, LetterRequest $letterRequest, CorrespondenceWorkflow $workflow): RedirectResponse
    {
        $workflow->revise($request->user(), $letterRequest, $request->validated('fields'), $request->validated('attachments') ?? []);

        return redirect()->route('correspondence.show', $letterRequest)->with('status', 'Perbaikan berhasil dikirim kembali ke tahap pemeriksaan RT.');
    }

    public function decide(DecideLetterRequest $request, LetterRequest $letterRequest, CorrespondenceWorkflow $workflow): RedirectResponse
    {
        $workflow->decide($request->user(), $letterRequest, $request->validated('decision'), $request->validated('comments'));

        return redirect()->route('correspondence.show', $letterRequest)->with('status', 'Keputusan pemeriksaan berhasil dicatat.');
    }

    public function sign(SignLetterRequest $request, LetterRequest $letterRequest, CorrespondenceWorkflow $workflow): RedirectResponse
    {
        $workflow->sign($request->user(), $letterRequest, $request->validated('decision'), $request->validated('comments'));

        return redirect()->route('correspondence.show', $letterRequest)->with('status', $request->validated('decision') === 'signed'
            ? 'Persetujuan tanda tangan prototipe telah dicatat. Dokumen ini bukan tanda tangan elektronik tersertifikasi.'
            : 'Permohonan ditolak pada tahap Kepala Desa.');
    }

    public function downloadDocument(Request $request, LetterRequest $letterRequest, LetterDocument $document)
    {
        abort_unless((int) $document->letter_request_id === (int) $letterRequest->getKey(), 404);
        $this->authorize('downloadDocument', [$letterRequest, $document]);
        abort_unless(Storage::disk('local')->exists($document->private_file_path), 404);

        return Storage::disk('local')->download($document->private_file_path, $letterRequest->request_number.'-v'.$document->version.'.pdf', ['Content-Type' => 'application/pdf']);
    }

    public function downloadAttachment(Request $request, LetterRequest $letterRequest, LetterAttachment $attachment)
    {
        abort_unless((int) $attachment->letter_request_id === (int) $letterRequest->getKey(), 404);
        $this->authorize('view', $letterRequest);
        abort_unless(Storage::disk('local')->exists($attachment->private_file_path), 404);

        $extension = match ($attachment->mime_type) {
            'application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png', default => 'bin',
        };

        return Storage::disk('local')->download($attachment->private_file_path, 'lampiran-'.$attachment->getKey().'.'.$extension, ['Content-Type' => $attachment->mime_type, 'X-Content-Type-Options' => 'nosniff']);
    }

    public function types(Request $request): View
    {
        $this->authorize('manageTypes', LetterRequest::class);

        return view('correspondence.types', ['types' => LetterType::query()->orderBy('name')->paginate(20)]);
    }

    public function storeType(StoreLetterTypeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $this->assertUniqueKeys($data);
        $type = LetterType::query()->create($data + ['is_active' => false]);
        app(AuditLogger::class)->record('letter.type_created', 'letter_type', (int) $type->getKey(), ['is_active' => $type->is_active]);

        return redirect()->route('correspondence.types')->with('status', 'Jenis surat berhasil dibuat.');
    }

    public function updateType(StoreLetterTypeRequest $request, LetterType $letterType): RedirectResponse
    {
        $data = $request->validated();
        $this->assertUniqueKeys($data);
        $data['is_active'] = $request->boolean('is_active');
        $letterType->fill($data)->save();
        app(AuditLogger::class)->record('letter.type_updated', 'letter_type', (int) $letterType->getKey(), ['is_active' => $letterType->is_active]);

        return redirect()->route('correspondence.types')->with('status', 'Jenis surat berhasil diperbarui.');
    }

    /** @param array<string, mixed> $data */
    private function assertUniqueKeys(array $data): void
    {
        foreach (['required_fields', 'required_attachments'] as $list) {
            $keys = collect($data[$list] ?? [])->pluck('key');
            if ($keys->unique()->count() !== $keys->count()) {
                throw ValidationException::withMessages([$list => 'Kunci isian atau lampiran harus unik.']);
            }
        }

        $labels = collect($data['required_fields'] ?? [])->pluck('label');
        if ($labels->unique()->count() !== $labels->count()) {
            throw ValidationException::withMessages(['required_fields' => 'Label isian harus unik agar informasi surat tidak tertukar.']);
        }
    }
}
