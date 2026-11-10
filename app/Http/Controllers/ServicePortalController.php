<?php

namespace App\Http\Controllers;

use App\Contracts\WifiGateway;
use App\Models\InstallationRequest;
use App\Models\InternetPackage;
use App\Models\InternetSubscription;
use App\Models\NetworkDevice;
use App\Models\Payment;
use App\Models\ServiceRequest;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use App\Models\WifiAccessLog;
use App\Models\WifiNetwork;
use App\Services\AuditLogger;
use App\Services\InAppNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServicePortalController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $resident = $user->resident;
        $staff = $this->isActiveStaff($user);
        $packages = $resident ? InternetPackage::query()->where('is_active', true)->orderBy('monthly_price')->get() : collect();
        $installations = InstallationRequest::with(['package', 'events'])->when(! $staff, fn ($q) => $q->where('resident_id', $resident?->id))->latest()->get();
        $subscriptions = $resident ? InternetSubscription::with(['package', 'invoices.payments'])->where('resident_id', $resident->id)->latest()->get() : collect();
        $serviceRequests = ServiceRequest::with('assignee')->when(! $staff, fn ($q) => $q->where('resident_id', $resident?->id))->latest()->get();
        $networks = $resident ? WifiNetwork::where('network_type', 'public_wifi')->where('status', 'active')->get() : collect();
        $accessLogs = WifiAccessLog::with('network')->where('user_id', $user->id)->latest('connected_at')->limit(20)->get();
        $staffInstallations = $staff ? InstallationRequest::with(['resident.user', 'package'])->whereIn('status', ['submitted', 'reviewing', 'approved', 'scheduled'])->latest()->get() : collect();
        // The shared payments table also contains marketplace order payments.
        // This queue is specifically for reviewing internet subscription invoices.
        $staffPayments = $staff ? Payment::query()
            ->with(['invoice.subscription.resident.user'])
            ->whereNotNull('invoice_id')
            ->where('status', 'pending')
            ->latest()
            ->get() : collect();
        $staffRequests = $staff ? ServiceRequest::with('resident.user')->whereIn('status', ['submitted', 'in_progress', 'waiting_resident'])->latest()->get() : collect();

        return view('services.index', compact('user', 'resident', 'staff', 'packages', 'installations', 'subscriptions', 'serviceRequests', 'networks', 'accessLogs', 'staffInstallations', 'staffPayments', 'staffRequests'));
    }

    public function management(Request $request)
    {
        $this->authorize('manage', InternetPackage::class);

        $subscriptions = InternetSubscription::query()->with(['resident.user', 'package', 'invoices'])->latest()->get();
        $packages = InternetPackage::query()->orderBy('monthly_price')->get();
        $networks = WifiNetwork::query()->where('network_type', 'public_wifi')->with('devices')->latest()->get();

        return view('services.management', compact('subscriptions', 'packages', 'networks'));
    }

    public function storePackage(Request $request, AuditLogger $audit)
    {
        $this->authorize('manage', InternetPackage::class);
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'description' => ['nullable', 'string', 'max:5000'], 'monthly_price' => ['required', 'numeric', 'min:0', 'max:999999999999.99'], 'bandwidth_download_mbps' => ['nullable', 'numeric', 'min:0.001', 'max:4294967.295'], 'bandwidth_upload_mbps' => ['nullable', 'numeric', 'min:0.001', 'max:4294967.295'], 'validity_days' => ['required', 'integer', 'between:1,365'], 'is_active' => ['sometimes', 'boolean']]);
        $data = $this->packageDataWithStoredBandwidth($data);
        $data['is_active'] = $request->boolean('is_active');
        $item = InternetPackage::create($data);
        $audit->record('internet_package.created', 'internet_package', $item->id);

        return back()->with('status', 'Paket internet berhasil ditambahkan.');
    }

    public function updatePackage(Request $request, InternetPackage $internetPackage, AuditLogger $audit)
    {
        $this->authorize('manage', InternetPackage::class);
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'description' => ['nullable', 'string', 'max:5000'], 'monthly_price' => ['required', 'numeric', 'min:0', 'max:999999999999.99'], 'bandwidth_download_mbps' => ['nullable', 'numeric', 'min:0.001', 'max:4294967.295'], 'bandwidth_upload_mbps' => ['nullable', 'numeric', 'min:0.001', 'max:4294967.295'], 'validity_days' => ['required', 'integer', 'between:1,365'], 'is_active' => ['sometimes', 'boolean']]);
        $data = $this->packageDataWithStoredBandwidth($data);
        $data['is_active'] = $request->boolean('is_active');
        $internetPackage->update($data);
        $audit->record('internet_package.updated', 'internet_package', $internetPackage->id);

        return back()->with('status', 'Paket internet diperbarui.');
    }

    /** @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function packageDataWithStoredBandwidth(array $data): array
    {
        foreach (['download', 'upload'] as $direction) {
            $field = "bandwidth_{$direction}_mbps";
            $data["bandwidth_{$direction}_kbps"] = isset($data[$field]) ? (int) round((float) $data[$field] * 1000) : null;
            unset($data[$field]);
        }

        return $data;
    }

    public function requestInstallation(Request $request, AuditLogger $audit, InAppNotificationService $notifications)
    {
        $this->authorize('create', InstallationRequest::class);
        $data = $request->validate(['package_id' => ['required', 'integer', 'exists:internet_packages,id'], 'installation_address' => ['required', 'string', 'max:2000'], 'contact_phone' => ['nullable', 'string', 'max:30']]);
        $package = InternetPackage::whereKey($data['package_id'])->where('is_active', true)->firstOrFail();
        $item = DB::transaction(function () use ($request, $package, $data, $audit): InstallationRequest {
            $item = InstallationRequest::create(['resident_id' => $request->user()->resident->id, 'package_id' => $package->id, 'installation_address' => $data['installation_address'], 'contact_phone' => $data['contact_phone'] ?? null]);
            $item->events()->create(['actor_id' => $request->user()->id, 'to_status' => 'submitted', 'note' => 'Permohonan diterima.']);
            $audit->record('installation_request.submitted', 'installation_request', (int) $item->id);

            return $item;
        });
        $notifications->toActiveRoles(['BUMDes Manager', 'BUMDes Operator'], 'Permintaan pemasangan internet', 'Ada permintaan pemasangan internet baru yang perlu ditinjau.', 'installation_request', (int) $item->getKey());

        return back()->with('status', 'Permohonan pemasangan berhasil dikirim.');
    }

    public function reviewInstallation(Request $request, InstallationRequest $installationRequest, AuditLogger $audit, InAppNotificationService $notifications)
    {
        $this->authorize('review', InstallationRequest::class);
        $data = $request->validate(['status' => ['required', 'in:reviewing,approved,rejected,scheduled,installed,cancelled'], 'review_note' => ['nullable', 'string', 'max:2000'], 'scheduled_at' => ['nullable', 'date']]);
        $transitions = [
            'submitted' => ['reviewing', 'rejected', 'cancelled'],
            'reviewing' => ['approved', 'rejected', 'cancelled'],
            'approved' => ['scheduled', 'cancelled'],
            'scheduled' => ['installed', 'cancelled'],
        ];
        abort_unless(in_array($data['status'], $transitions[$installationRequest->status] ?? [], true), 422, 'Tahap permohonan harus diproses sesuai urutan.');

        if (in_array($data['status'], ['rejected', 'cancelled'], true)) {
            validator($data, ['review_note' => ['required', 'string', 'min:5', 'max:2000']])->validate();
        }
        if ($data['status'] === 'scheduled') {
            validator($data, ['scheduled_at' => ['required', 'date', 'after_or_equal:now']])->validate();
        }
        DB::transaction(function () use ($request, $installationRequest, $data, $audit): void {
            $item = InstallationRequest::lockForUpdate()->findOrFail($installationRequest->id);
            $old = $item->status;
            $transitions = [
                'submitted' => ['reviewing', 'rejected', 'cancelled'],
                'reviewing' => ['approved', 'rejected', 'cancelled'],
                'approved' => ['scheduled', 'cancelled'],
                'scheduled' => ['installed', 'cancelled'],
            ];
            abort_unless(in_array($data['status'], $transitions[$old] ?? [], true), 409, 'Status permohonan berubah. Muat ulang halaman.');
            $item->status = $data['status'];
            $item->review_note = $data['review_note'] ?? null;
            $item->reviewed_by = $request->user()->id;
            if ($item->status === 'scheduled') {
                $item->scheduled_at = $data['scheduled_at'];
            }
            if ($item->status === 'installed') {
                $item->completed_at = now();
            }
            $item->save();
            $item->events()->create(['actor_id' => $request->user()->id, 'from_status' => $old, 'to_status' => $item->status, 'note' => $data['review_note'] ?? null]);
            if ($item->status === 'installed' && ! $item->subscription()->exists()) {
                $amount = $item->package->monthly_price;
                $free = (float) $amount === 0.0;
                $sub = InternetSubscription::create(['resident_id' => $item->resident_id, 'package_id' => $item->package_id, 'installation_request_id' => $item->id, 'start_date' => today(), 'end_date' => today()->addDays($item->package->validity_days), 'status' => 'active']);
                $sub->invoices()->create(['invoice_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)), 'billing_period_start' => today()->startOfMonth(), 'billing_period_end' => today()->endOfMonth(), 'amount' => $amount, 'due_date' => today()->addDays(7), 'status' => $free ? 'paid' : 'unpaid', 'paid_at' => $free ? now() : null]);
            }
            $audit->record('installation_request.status_changed', 'installation_request', $item->id, ['from' => $old, 'to' => $item->status]);
        });
        $notifications->toUser($installationRequest->resident->user, 'Status pemasangan internet diperbarui', 'Status permintaan pemasangan Anda berubah menjadi '.match ($data['status']) {
            'reviewing' => 'sedang diperiksa.', 'approved' => 'disetujui.', 'scheduled' => 'terjadwal.', 'installed' => 'selesai dipasang.', 'rejected' => 'ditolak.', 'cancelled' => 'dibatalkan.', default => 'diperbarui.',
        }, 'installation_request', (int) $installationRequest->getKey());

        return back()->with('status', match ($data['status']) {
            'reviewing' => 'Permohonan masuk ke tahap pemeriksaan.',
            'approved' => 'Permohonan disetujui. Silakan tentukan jadwal pemasangan.',
            'scheduled' => 'Jadwal pemasangan berhasil disimpan.',
            'installed' => 'Pemasangan selesai. Langganan dan tagihan awal telah dibuat.',
            'rejected' => 'Permohonan ditolak dan alasannya dicatat.',
            'cancelled' => 'Permohonan dibatalkan dan alasannya dicatat.',
        });
    }

    public function submitPayment(Request $request, SubscriptionInvoice $invoice, AuditLogger $audit, InAppNotificationService $notifications)
    {
        $this->authorize('pay', $invoice);
        $data = $request->validate(['payment_method' => ['required', 'in:bank_transfer,cash,other'], 'provider_reference' => ['nullable', 'string', 'max:180']]);
        DB::transaction(function () use ($request, $invoice, $data, $audit): void {
            $item = SubscriptionInvoice::lockForUpdate()->findOrFail($invoice->id);
            abort_unless(in_array($item->status, ['unpaid', 'overdue'], true), 409);
            $payment = $item->payments()->create(['payment_method' => $data['payment_method'], 'payment_provider' => $data['payment_method'] === 'bank_transfer' ? 'manual' : null, 'provider_reference' => $data['provider_reference'] ?? null, 'amount' => $item->amount, 'status' => 'pending', 'submitted_by' => $request->user()->id]);
            $payment->events()->create(['event_type' => 'payment.submitted', 'payload' => ['source' => 'resident_portal'], 'signature_verified' => false]);
            $item->update(['status' => 'pending']);
            $audit->record('invoice.payment_submitted', 'payment', $payment->id);
        });
        $notifications->toActiveRoles(['BUMDes Manager', 'BUMDes Operator'], 'Pembayaran internet menunggu verifikasi', 'Ada pembayaran tagihan internet yang menunggu pemeriksaan.', 'payment', (int) $invoice->payments()->latest('id')->value('id'));

        return back()->with('status', 'Pembayaran dikirim untuk verifikasi. Tagihan belum dianggap lunas.');
    }

    public function confirmPayment(Request $request, Payment $payment, AuditLogger $audit, InAppNotificationService $notifications)
    {
        $this->authorize('confirm', SubscriptionInvoice::class);
        $data = $request->validate(['decision' => ['required', 'in:paid,rejected'], 'note' => ['nullable', 'string', 'max:1000']]);
        DB::transaction(function () use ($payment, $data, $audit): void {
            $item = Payment::query()->whereNotNull('invoice_id')->with('invoice')->lockForUpdate()->findOrFail($payment->id);
            $invoice = SubscriptionInvoice::lockForUpdate()->findOrFail($item->invoice_id);
            abort_unless($item->status === 'pending' && $invoice->status === 'pending', 409);
            $paid = $data['decision'] === 'paid';
            $item->update(['status' => $paid ? 'paid' : 'failed', 'paid_at' => $paid ? now() : null]);
            $item->events()->create(['event_type' => $paid ? 'payment.confirmed' : 'payment.rejected', 'payload' => ['note_present' => filled($data['note'] ?? null)], 'signature_verified' => false]);
            $invoice->update(['status' => $paid ? 'paid' : 'unpaid', 'paid_at' => $paid ? now() : null]);
            $audit->record('invoice.payment_reviewed', 'payment', $item->id, ['decision' => $data['decision']]);
        });
        $payment->loadMissing('invoice.subscription.resident.user');
        $notifications->toUser($payment->invoice->subscription->resident->user, 'Status pembayaran internet diperbarui', $data['decision'] === 'paid' ? 'Pembayaran tagihan internet Anda telah diverifikasi.' : 'Pembayaran belum dapat diverifikasi. Silakan periksa tagihan dan ajukan kembali.', 'payment', (int) $payment->getKey());

        return back()->with('status', $data['decision'] === 'paid' ? 'Pembayaran ditandai lunas setelah pemeriksaan manual.' : 'Pembayaran ditolak dan tagihan menunggu pembayaran.');
    }

    public function updateSubscriptionStatus(Request $request, InternetSubscription $internetSubscription, AuditLogger $audit, InAppNotificationService $notifications)
    {
        $this->authorize('manageStatus', $internetSubscription);
        $data = $request->validate(['status' => ['required', 'in:active,suspended,cancelled']]);
        $allowed = [
            'active' => ['suspended', 'cancelled'],
            'suspended' => ['active', 'cancelled'],
            'pending' => ['active', 'cancelled'],
        ];
        DB::transaction(function () use ($internetSubscription, $data, $allowed, $audit): void {
            $subscription = InternetSubscription::lockForUpdate()->findOrFail($internetSubscription->id);
            abort_unless(in_array($data['status'], $allowed[$subscription->status] ?? [], true), 409, 'Perubahan status langganan tidak diizinkan.');
            $previous = $subscription->status;
            $subscription->update(['status' => $data['status']]);
            $audit->record('internet_subscription.status_changed', 'internet_subscription', $subscription->id, ['from' => $previous, 'to' => $data['status']]);
        });
        $internetSubscription->loadMissing('resident.user');
        $notifications->toUser($internetSubscription->resident->user, 'Status langganan internet diperbarui', 'Status langganan internet Anda telah diperbarui.', 'internet_subscription', (int) $internetSubscription->getKey());

        return back()->with('status', 'Status langganan diperbarui.');
    }

    public function createServiceRequest(Request $request, AuditLogger $audit, InAppNotificationService $notifications)
    {
        $this->authorize('create', ServiceRequest::class);
        $data = $request->validate(['service_type' => ['required', 'in:complaint,technical_support,billing_question,wifi_access,other'], 'subject' => ['required', 'string', 'max:180'], 'description' => ['required', 'string', 'max:10000']]);
        $item = ServiceRequest::create(['resident_id' => $request->user()->resident->id, ...$data]);
        $audit->record('service_request.created', 'service_request', $item->id, ['service_type' => $item->service_type]);
        $notifications->toActiveRoles(['BUMDes Manager', 'BUMDes Operator'], 'Laporan layanan internet baru', 'Ada laporan layanan baru yang menunggu tindak lanjut.', 'service_request', (int) $item->getKey());

        return back()->with('status', 'Laporan berhasil dikirim.');
    }

    public function updateServiceRequest(Request $request, ServiceRequest $serviceRequest, AuditLogger $audit, InAppNotificationService $notifications)
    {
        $this->authorize('manage', ServiceRequest::class);
        $data = $request->validate(['status' => ['required', 'in:in_progress,waiting_resident,resolved,rejected,closed'], 'resolution_note' => ['nullable', 'string', 'max:3000']]);
        if (in_array($data['status'], ['resolved', 'rejected', 'closed'], true)) {
            validator($data, ['resolution_note' => ['required', 'string', 'max:3000']])->validate();
        } abort_unless(in_array($serviceRequest->status, ['submitted', 'in_progress', 'waiting_resident'], true), 409);
        $serviceRequest->update(['status' => $data['status'], 'assigned_to' => $request->user()->id, 'resolution_note' => $data['resolution_note'] ?? null, 'resolved_at' => $data['status'] === 'resolved' ? now() : null]);
        $audit->record('service_request.status_changed', 'service_request', $serviceRequest->id, ['status' => $serviceRequest->status]);
        $notifications->toUser($serviceRequest->resident->user, 'Laporan layanan diperbarui', 'Status laporan layanan Anda telah diperbarui. Buka layanan untuk melihat tindak lanjutnya.', 'service_request', (int) $serviceRequest->getKey());

        return back()->with('status', 'Laporan berhasil diperbarui.');
    }

    public function openWifiSession(Request $request, WifiNetwork $wifiNetwork, WifiGateway $gateway, AuditLogger $audit)
    {
        $request->validate(['terms_accepted' => ['required', 'accepted']]);
        abort_unless($wifiNetwork->network_type === 'public_wifi' && $wifiNetwork->status === 'active', 404);
        $resident = $request->user()->resident;
        abort_unless($resident, 403);
        $policy = $wifiNetwork->access_policy ?? [];
        $eligible = ($policy['eligibility'] ?? 'authenticated_residents') === 'authenticated_residents' || $resident->region_verified_at !== null;
        $minutes = (int) ($policy['session_minutes'] ?? 60);
        abort_unless($eligible && $minutes >= 5 && $minutes <= 1440, 403, 'Akun belum memenuhi syarat Wi‑Fi atau kebijakan tidak valid.');
        abort_if($wifiNetwork->accessLogs()->where('user_id', $request->user()->id)->where('authentication_status', 'success')->whereNull('disconnected_at')->where('connected_at', '>=', now()->subMinutes($minutes))->exists(), 409, 'Sesi simulasi masih aktif.');
        $success = $gateway->openSession($request->user(), $wifiNetwork, $minutes);
        WifiAccessLog::create(['user_id' => $request->user()->id, 'wifi_network_id' => $wifiNetwork->id, 'authentication_status' => $success ? 'success' : 'blocked', 'session_reference' => $success ? (string) Str::uuid() : null, 'connected_at' => now()]);
        $audit->record('public_wifi.session_requested', 'wifi_network', $wifiNetwork->id, ['result' => $success ? 'simulated_success' : 'blocked', 'terms_version' => (int) ($policy['terms_version'] ?? 0)]);

        return back()->with('status', $success ? "Sesi simulasi dibuka selama {$minutes} menit. Ini belum mengaktifkan akses internet melalui router." : 'Jaringan belum dapat membuka sesi.');
    }

    public function storeNetwork(Request $request, AuditLogger $audit)
    {
        abort_unless($request->user()->hasRole('BUMDes Manager') && $this->isActiveStaff($request->user()), 403);
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'location' => ['required', 'string', 'max:255'], 'ssid' => ['required', 'string', 'max:100'], 'status' => ['required', 'in:active,inactive,maintenance'], 'eligibility' => ['required', 'in:authenticated_residents,verified_residents'], 'session_minutes' => ['required', 'integer', 'between:5,1440'], 'terms' => ['required', 'string', 'max:3000']]);
        $network = WifiNetwork::create(['name' => $data['name'], 'network_type' => 'public_wifi', 'location' => $data['location'], 'ssid' => $data['ssid'], 'status' => $data['status'], 'access_policy' => ['eligibility' => $data['eligibility'], 'session_minutes' => (int) $data['session_minutes'], 'terms' => $data['terms'], 'terms_version' => 1]]);
        NetworkDevice::create(['wifi_network_id' => $network->id, 'device_name' => 'Gateway simulasi', 'integration_type' => 'simulated', 'device_status' => 'unknown', 'configuration' => ['enforces_network_access' => false]]);
        $audit->record('public_wifi.network_created', 'wifi_network', $network->id);

        return back()->with('status', 'Jaringan Wi‑Fi ditambahkan dalam mode simulasi.');
    }

    public function updateNetwork(Request $request, WifiNetwork $wifiNetwork, AuditLogger $audit)
    {
        abort_unless($request->user()->hasRole('BUMDes Manager') && $this->isActiveStaff($request->user()), 403);
        abort_unless($wifiNetwork->network_type === 'public_wifi', 404);
        $data = $request->validate(['name' => ['required', 'string', 'max:120'], 'location' => ['required', 'string', 'max:255'], 'ssid' => ['required', 'string', 'max:100'], 'status' => ['required', 'in:active,inactive,maintenance'], 'eligibility' => ['required', 'in:authenticated_residents,verified_residents'], 'session_minutes' => ['required', 'integer', 'between:5,1440'], 'terms' => ['required', 'string', 'max:3000']]);
        $version = (int) data_get($wifiNetwork->access_policy, 'terms_version', 0);
        $wifiNetwork->update(['name' => $data['name'], 'location' => $data['location'], 'ssid' => $data['ssid'], 'status' => $data['status'], 'access_policy' => ['eligibility' => $data['eligibility'], 'session_minutes' => (int) $data['session_minutes'], 'terms' => $data['terms'], 'terms_version' => $version + 1]]);
        $audit->record('public_wifi.network_updated', 'wifi_network', $wifiNetwork->id);

        return back()->with('status', 'Pengaturan Wi‑Fi publik diperbarui.');
    }

    private function isActiveStaff(User $user): bool
    {
        return ($user->hasRole('BUMDes Manager') || $user->hasRole('BUMDes Operator')) && $user->bumdesStaff()->where('employment_status', 'active')->exists();
    }
}
