<?php

namespace App\Http\Controllers\Bumdes;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssistedServiceMessageRequest;
use App\Http\Requests\StoreAssistedServiceSessionRequest;
use App\Models\AssistedServiceLog;
use App\Models\AssistedServiceMessage;
use App\Models\AssistedServiceSession;
use App\Models\InstallationRequest;
use App\Models\InternetSubscription;
use App\Models\LetterRequest;
use App\Models\Order;
use App\Models\ServiceRequest;
use App\Services\AuditLogger;
use App\Services\InAppNotificationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AssistedServiceSessionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AssistedServiceSession::class);
        $user = $request->user();
        $query = AssistedServiceSession::query()->with(['resident.user', 'operator']);

        if ($user->hasRole('Resident')) {
            $query->whereHas('resident', fn (Builder $resident) => $resident->where('user_id', $user->getKey()));
        } elseif (! $user->hasRole('BUMDes Manager')) {
            $query->where(fn (Builder $scope): Builder => $scope->where('status', 'pending')->orWhere('operator_id', $user->getKey()));
        }

        if ($request->filled('status') && in_array($request->string('status')->toString(), ['pending', 'in_progress', 'awaiting_resident_confirmation', 'completed', 'cancelled', 'rejected'], true)) {
            $query->where('status', $request->string('status')->toString());
        }
        $scope = clone $query;
        $activityCounts = (clone $scope)->reorder()->selectRaw('service_type, COUNT(*) as total')->groupBy('service_type')->get();
        $totals = [
            'all' => (clone $scope)->count(),
            'pending' => (clone $scope)->where('status', 'pending')->count(),
            'in_progress' => (clone $scope)->whereIn('status', ['in_progress', 'awaiting_resident_confirmation'])->count(),
            'completed' => (clone $scope)->where('status', 'completed')->count(),
            'cancelled' => (clone $scope)->where('status', 'cancelled')->count(),
        ];
        $correspondenceQuery = LetterRequest::query()->where('submission_channel', 'assisted_bumdes');
        if ($user->hasRole('Resident')) {
            $correspondenceQuery->where('resident_id', $user->resident?->getKey());
        } elseif (! $user->hasRole('BUMDes Manager')) {
            $correspondenceQuery->where('created_by', $user->getKey());
        }

        return view('bumdes.assistance.index', [
            'sessions' => $query->latest('requested_at')->paginate(15)->withQueryString(),
            'totals' => $totals,
            'activityCounts' => $activityCounts,
            'assistedCorrespondenceCount' => $correspondenceQuery->count(),
            'internetAssistanceCount' => (clone $scope)->whereIn('service_type', ['internet_subscription', 'internet_complaint'])->count(),
            'marketplaceAssistanceCount' => (clone $scope)->where('service_type', 'marketplace')->count(),
            'isResident' => $user->hasRole('Resident'),
            'canCreate' => $user->can('create', AssistedServiceSession::class),
            'serviceTypes' => $this->serviceLabels(),
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', AssistedServiceSession::class);

        return view('bumdes.assistance.create', ['serviceTypes' => $this->serviceLabels()]);
    }

    public function store(StoreAssistedServiceSessionRequest $request, AuditLogger $audit, InAppNotificationService $notifications): RedirectResponse
    {
        $data = $request->validated();
        $resident = $request->user()->resident;
        abort_unless($resident !== null && $resident->region_verified_at !== null, 403);

        $session = DB::transaction(function () use ($request, $data, $resident): AssistedServiceSession {
            $session = AssistedServiceSession::query()->create([
                'session_reference' => 'BAS-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'resident_id' => $resident->getKey(),
                'service_type' => $data['service_type'],
                'status' => 'pending',
                'notes' => $this->cleanNotes($data['notes'] ?? null),
                'requested_at' => now(),
            ]);
            $this->log($session, $request->user()->getKey(), 'assistance_requested');

            return $session;
        });

        $audit->record('assisted_service.requested', 'assisted_service_session', (int) $session->getKey(), ['service_type' => $session->service_type], $request);
        $notifications->toActiveRoles(['BUMDes Manager', 'BUMDes Operator'], 'Permintaan bantuan warga', 'Ada permintaan bantuan digital yang menunggu petugas.', 'assisted_service_session', (int) $session->getKey());

        return redirect()->route('bumdes.assistance.show', $session)->with('status', 'Permintaan bantuan dikirim. Petugas BUMDes akan meninjau permintaan ini.');
    }

    public function accept(Request $request, AssistedServiceSession $session, AuditLogger $audit, InAppNotificationService $notifications): RedirectResponse
    {
        $this->authorize('accept', $session);
        $request->validate(['identity_verified' => ['required', 'accepted']]);

        DB::transaction(function () use ($request, $session): void {
            $locked = AssistedServiceSession::query()->whereKey($session->getKey())->lockForUpdate()->firstOrFail();
            $this->authorize('accept', $locked);
            $locked->forceFill([
                'operator_id' => $request->user()->getKey(),
                'status' => 'in_progress',
                'accepted_at' => now(),
                'started_at' => now(),
                'identity_verified_at' => now(),
            ])->save();
            $this->log($locked, $request->user()->getKey(), 'request_accepted_and_identity_verified');
        });
        $audit->record('assisted_service.accepted', 'assisted_service_session', (int) $session->getKey(), [], $request);
        $notifications->toUser($session->resident->user, 'Permintaan bantuan diterima', 'Petugas BUMDes menerima permintaan Anda dan akan menghubungi melalui ruang percakapan.', 'assisted_service_session', (int) $session->getKey());

        return redirect()->route('bumdes.assistance.show', $session)->with('status', 'Permintaan diterima. Identitas warga telah Anda verifikasi sesuai prosedur desa.');
    }

    public function reject(Request $request, AssistedServiceSession $session, AuditLogger $audit, InAppNotificationService $notifications): RedirectResponse
    {
        $this->authorize('reject', $session);
        $validated = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);

        DB::transaction(function () use ($request, $session, $validated): void {
            $locked = AssistedServiceSession::query()->whereKey($session->getKey())->lockForUpdate()->firstOrFail();
            $this->authorize('reject', $locked);
            $locked->forceFill([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejection_reason' => trim($validated['reason']),
            ])->save();
            $this->log($locked, $request->user()->getKey(), 'request_rejected');
        });
        $audit->record('assisted_service.rejected', 'assisted_service_session', (int) $session->getKey(), [], $request);
        $notifications->toUser($session->resident->user, 'Permintaan bantuan tidak dapat diterima', 'Petugas memperbarui status permintaan bantuan. Buka sesi untuk melihat keterangannya.', 'assisted_service_session', (int) $session->getKey());

        return redirect()->route('bumdes.assistance.show', $session)->with('status', 'Permintaan ditolak dengan alasan.');
    }

    public function show(Request $request, AssistedServiceSession $session): View
    {
        if ($request->user()->hasRole('Resident') && (int) $session->resident?->user_id !== (int) $request->user()->getKey()) {
            abort(404);
        }
        $this->authorize('view', $session);
        $session->load(['resident.user', 'operator', 'logs.actor']);
        $letter = null;
        if ($session->reference_type === 'letter_request' && $session->reference_id !== null) {
            $candidate = LetterRequest::query()->with('resident')->find($session->reference_id);
            if ($candidate !== null && (int) $candidate->resident_id === (int) $session->resident_id
                && $candidate->submission_channel === 'assisted_bumdes'
                && (int) $candidate->created_by === (int) $session->operator_id) {
                $letter = $candidate;
            }
        }

        $referenceRecords = $request->user()->can('attachReference', $session) ? $this->referenceRecords($session) : [];
        $canViewConversation = $request->user()->can('viewConversation', $session);
        $messages = null;
        if ($canViewConversation) {
            $messages = $session->messages()->with('sender')->orderByDesc('created_at')->orderByDesc('id')
                ->paginate(30)->withQueryString();
            $messages->setCollection($messages->getCollection()->reverse()->values());
        }

        return view('bumdes.assistance.show', [
            'session' => $session,
            'letter' => $letter,
            'referenceTypes' => match ($session->service_type) {
                'internet_subscription' => ['installation_request' => 'Permohonan pemasangan internet', 'internet_subscription' => 'Langganan internet'],
                'internet_complaint' => ['service_request' => 'Laporan kendala internet'],
                'marketplace' => ['order' => 'Pesanan pasar desa'],
                default => [],
            },
            'isResident' => $request->user()->hasRole('Resident'),
            'serviceLabels' => $this->serviceLabels(),
            'statusLabels' => $this->statusLabels(),
            'canRequestConfirmation' => $request->user()->can('requestConfirmation', $session),
            'canCancel' => $request->user()->can('cancel', $session),
            'canConfirm' => $request->user()->can('confirm', $session),
            'canAccept' => $request->user()->can('accept', $session),
            'canReject' => $request->user()->can('reject', $session),
            'referenceRecords' => $referenceRecords,
            'messages' => $messages,
            'canViewConversation' => $canViewConversation,
            'canSendMessage' => $request->user()->can('sendMessage', $session),
        ]);
    }

    public function sendMessage(StoreAssistedServiceMessageRequest $request, AssistedServiceSession $session, AuditLogger $audit, InAppNotificationService $notifications): RedirectResponse
    {
        $data = $request->validated();
        $message = DB::transaction(function () use ($request, $session, $data): AssistedServiceMessage {
            $locked = AssistedServiceSession::query()->whereKey($session->getKey())->lockForUpdate()->firstOrFail();
            $this->authorize('sendMessage', $locked);
            $message = $locked->messages()->create([
                'sender_id' => $request->user()->getKey(),
                'body' => $data['body'],
                'created_at' => now(),
            ]);
            $this->log($locked, $request->user()->getKey(), 'message_sent');

            return $message;
        });

        $audit->record('assisted_service.message_sent', 'assisted_service_message', (int) $message->getKey(), [], $request);
        if ($request->user()->hasRole('Resident')) {
            if ($session->operator) {
                $notifications->toUser($session->operator, 'Pesan baru dari warga', 'Warga mengirim pesan pada sesi bantuan yang Anda tangani.', 'assisted_service_session', (int) $session->getKey());
            }
        } else {
            $notifications->toUser($session->resident->user, 'Pesan baru dari petugas BUMDes', 'Petugas mengirim pesan pada sesi bantuan Anda.', 'assisted_service_session', (int) $session->getKey());
        }

        return redirect()->to(route('bumdes.assistance.show', $session).'#conversation')->with('status', 'Pesan bantuan terkirim.');
    }

    public function requestConfirmation(Request $request, AssistedServiceSession $session, AuditLogger $audit, InAppNotificationService $notifications): RedirectResponse
    {
        $this->authorize('requestConfirmation', $session);

        DB::transaction(function () use ($request, $session): void {
            $locked = AssistedServiceSession::query()->whereKey($session->getKey())->lockForUpdate()->firstOrFail();
            $this->authorize('requestConfirmation', $locked);
            $locked->forceFill(['status' => 'awaiting_resident_confirmation', 'awaiting_confirmation_at' => now()])->save();
            $this->log($locked, $request->user()->getKey(), 'resident_confirmation_requested');
        });
        $audit->record('assisted_service.confirmation_requested', 'assisted_service_session', (int) $session->getKey(), [], $request);
        $notifications->toUser($session->resident->user, 'Bantuan menunggu tinjauan Anda', 'Silakan tinjau sesi bantuan dan konfirmasikan melalui akun Anda.', 'assisted_service_session', (int) $session->getKey());

        return redirect()->route('bumdes.assistance.show', $session)->with('status', 'Minta warga meninjau sesi dan mengonfirmasi melalui akunnya sendiri.');
    }

    public function attachReference(Request $request, AssistedServiceSession $session, AuditLogger $audit): RedirectResponse
    {
        $this->authorize('attachReference', $session);
        $data = $request->validate([
            'reference' => ['required', 'string', 'regex:/\\A(installation_request|internet_subscription|service_request|order):[1-9][0-9]*\\z/'],
        ]);
        [$referenceType, $referenceId] = explode(':', $data['reference'], 2);
        $referenceId = (int) $referenceId;

        $expectedTypes = match ($session->service_type) {
            'internet_subscription' => ['installation_request', 'internet_subscription'],
            'internet_complaint' => ['service_request'],
            'marketplace' => ['order'],
            default => [],
        };
        abort_unless(in_array($referenceType, $expectedTypes, true), 422, 'Catatan ini tidak sesuai dengan jenis bantuan.');

        $ownerId = (int) $session->resident_id;
        $recordExists = match ($referenceType) {
            'installation_request' => InstallationRequest::query()->whereKey($referenceId)->where('resident_id', $ownerId)->exists(),
            'internet_subscription' => InternetSubscription::query()->whereKey($referenceId)->where('resident_id', $ownerId)->exists(),
            'service_request' => ServiceRequest::query()->whereKey($referenceId)->where('resident_id', $ownerId)->exists(),
            'order' => Order::query()->whereKey($referenceId)->where('buyer_id', $session->resident->user_id)->exists(),
            default => false,
        };
        abort_unless($recordExists, 404);

        DB::transaction(function () use ($request, $session, $referenceType, $referenceId): void {
            $locked = AssistedServiceSession::query()->whereKey($session->getKey())->lockForUpdate()->firstOrFail();
            $this->authorize('attachReference', $locked);
            $locked->forceFill(['reference_type' => $referenceType, 'reference_id' => $referenceId])->save();
            $this->log($locked, $request->user()->getKey(), 'service_record_linked');
        });
        $audit->record('assisted_service.reference_linked', 'assisted_service_session', (int) $session->getKey(), ['reference_type' => $referenceType], $request);

        return redirect()->route('bumdes.assistance.show', $session)->with('status', 'Rujukan layanan milik warga berhasil ditautkan. Data layanan tidak diubah.');
    }

    public function confirm(Request $request, AssistedServiceSession $session, AuditLogger $audit, InAppNotificationService $notifications): RedirectResponse
    {
        if ($request->user()->hasRole('Resident') && (int) $session->resident?->user_id !== (int) $request->user()->getKey()) {
            abort(404);
        }
        $this->authorize('confirm', $session);
        abort_if($session->reference_type === 'letter_request', 409, 'Konfirmasi permohonan surat dilakukan pada halaman surat agar data permohonan dan sesi tercatat bersama.');

        DB::transaction(function () use ($request, $session): void {
            $locked = AssistedServiceSession::query()->whereKey($session->getKey())->lockForUpdate()->firstOrFail();
            $this->authorize('confirm', $locked);
            $locked->forceFill([
                'status' => 'completed',
                'resident_confirmed_at' => now(),
                'completed_at' => now(),
            ])->save();
            $this->log($locked, $request->user()->getKey(), 'resident_confirmed_and_completed');
        });
        $audit->record('assisted_service.resident_confirmed', 'assisted_service_session', (int) $session->getKey(), [], $request);
        if ($session->operator) {
            $notifications->toUser($session->operator, 'Bantuan dikonfirmasi warga', 'Warga telah meninjau dan mengonfirmasi sesi bantuan.', 'assisted_service_session', (int) $session->getKey());
        }

        return redirect()->route('bumdes.assistance.show', $session)->with('status', 'Konfirmasi Anda tercatat. Simpan nomor sesi sebagai bukti bantuan.');
    }

    public function cancel(Request $request, AssistedServiceSession $session, AuditLogger $audit, InAppNotificationService $notifications): RedirectResponse
    {
        $validated = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:500']]);
        $this->authorize('cancel', $session);

        DB::transaction(function () use ($request, $session, $validated): void {
            $locked = AssistedServiceSession::query()->whereKey($session->getKey())->lockForUpdate()->firstOrFail();
            $this->authorize('cancel', $locked);
            $locked->forceFill([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => trim($validated['reason']),
            ])->save();
            $this->log($locked, $request->user()->getKey(), 'session_cancelled');
        });
        $audit->record('assisted_service.cancelled', 'assisted_service_session', (int) $session->getKey(), [], $request);
        if (! $request->user()->hasRole('Resident')) {
            $notifications->toUser($session->resident->user, 'Sesi bantuan dibatalkan', 'Petugas memperbarui status sesi bantuan Anda.', 'assisted_service_session', (int) $session->getKey());
        }

        return redirect()->route('bumdes.assistance.show', $session)->with('status', 'Sesi dibatalkan dan riwayatnya tetap tersimpan.');
    }

    public function reports(Request $request): View
    {
        $this->authorize('viewReports', AssistedServiceSession::class);
        $validated = $request->validate([
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $from = $validated['from'] ?? today()->startOfMonth()->toDateString();
        $to = $validated['to'] ?? today()->toDateString();
        $base = AssistedServiceSession::query()->whereDate('started_at', '>=', $from)->whereDate('started_at', '<=', $to);
        $counts = (clone $base)->selectRaw('service_type, status, COUNT(*) as total')->groupBy('service_type', 'status')->get();
        $operatorCounts = (clone $base)->with('operator:id,username')
            ->selectRaw('operator_id, COUNT(*) as total')->groupBy('operator_id')->get();

        return view('bumdes.assistance.reports', [
            'from' => $from,
            'to' => $to,
            'total' => (clone $base)->count(),
            'completed' => (clone $base)->where('status', 'completed')->count(),
            'cancelled' => (clone $base)->where('status', 'cancelled')->count(),
            'inProgress' => (clone $base)->whereIn('status', ['in_progress', 'awaiting_resident_confirmation'])->count(),
            'counts' => $counts,
            'operatorCounts' => $operatorCounts,
            'serviceLabels' => $this->serviceLabels(),
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    private function log(AssistedServiceSession $session, ?int $actorId, string $action): void
    {
        AssistedServiceLog::query()->create([
            'session_id' => $session->getKey(),
            'actor_id' => $actorId,
            'action' => $action,
            'metadata' => [],
            'created_at' => now(),
        ]);
    }

    private function cleanNotes(?string $notes): ?string
    {
        if ($notes === null || trim($notes) === '') {
            return null;
        }

        return trim($notes);
    }

    /** @return array<string, string> */
    private function serviceLabels(): array
    {
        return [
            'correspondence' => 'Bantuan permohonan surat',
            'internet_subscription' => 'Bantuan langganan internet',
            'internet_complaint' => 'Bantuan laporan kendala internet',
            'marketplace' => 'Panduan pasar desa',
            'general' => 'Panduan layanan digital lainnya',
        ];
    }

    /** @return array<string, array<int, array{id: int, label: string}>> */
    private function referenceRecords(AssistedServiceSession $session): array
    {
        if ($session->operator_id === null || $session->status !== 'in_progress') {
            return [];
        }

        $records = [];
        if ($session->service_type === 'internet_subscription') {
            $records['installation_request'] = InstallationRequest::query()->with('package')
                ->where('resident_id', $session->resident_id)->latest('requested_at')->limit(100)->get()
                ->map(fn (InstallationRequest $record): array => [
                    'id' => (int) $record->id,
                    'label' => 'Pemasangan · '.($record->package?->name ?? 'Paket internet').' · '.$record->status.' · #'.$record->id,
                ])->all();
            $records['internet_subscription'] = InternetSubscription::query()->with('package')
                ->where('resident_id', $session->resident_id)->latest('created_at')->limit(100)->get()
                ->map(fn (InternetSubscription $record): array => [
                    'id' => (int) $record->id,
                    'label' => 'Langganan · '.($record->package?->name ?? 'Paket internet').' · '.$record->status.' · #'.$record->id,
                ])->all();
        } elseif ($session->service_type === 'internet_complaint') {
            $records['service_request'] = ServiceRequest::query()->where('resident_id', $session->resident_id)
                ->latest('created_at')->limit(100)->get()
                ->map(fn (ServiceRequest $record): array => [
                    'id' => (int) $record->id,
                    'label' => 'Kendala · '.$record->subject.' · '.$record->status.' · #'.$record->id,
                ])->all();
        } elseif ($session->service_type === 'marketplace') {
            $records['order'] = Order::query()->where('buyer_id', $session->resident->user_id)
                ->latest('created_at')->limit(100)->get()
                ->map(fn (Order $record): array => [
                    'id' => (int) $record->id,
                    'label' => 'Pesanan '.$record->order_number.' · '.$record->status.' · #'.$record->id,
                ])->all();
        }

        return $records;
    }

    /** @return array<string, string> */
    private function statusLabels(): array
    {
        return [
            'in_progress' => 'Sedang dibantu',
            'pending' => 'Menunggu penerimaan petugas',
            'awaiting_resident_confirmation' => 'Menunggu konfirmasi warga',
            'completed' => 'Selesai dikonfirmasi',
            'cancelled' => 'Dibatalkan',
            'rejected' => 'Permintaan ditolak',
        ];
    }
}
