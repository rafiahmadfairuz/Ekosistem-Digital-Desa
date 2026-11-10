<?php

namespace Tests\Feature;

use App\Contracts\WifiGateway;
use App\Models\BumdesStaff;
use App\Models\CheckoutGroup;
use App\Models\InstallationRequest;
use App\Models\InternetPackage;
use App\Models\InternetSubscription;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Region;
use App\Models\Resident;
use App\Models\Role;
use App\Models\Seller;
use App\Models\ServiceRequest;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use App\Models\Village;
use App\Models\WifiAccessLog;
use App\Models\WifiNetwork;
use App\Services\SimulatedWifiGateway;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternetWifiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    public function test_installation_workflow_creates_invoice_and_only_resident_owner_can_pay(): void
    {
        [$owner, $resident] = $this->residentWithProfile('owner');
        [$other, $otherResident] = $this->residentWithProfile('other');
        $manager = $this->userWithRoles(['BUMDes Manager']);
        BumdesStaff::create(['user_id' => $manager->id, 'employment_status' => 'active']);
        $package = InternetPackage::create(['name' => 'Warga 10', 'monthly_price' => '75000.00', 'validity_days' => 30, 'is_active' => true]);
        $operator = $this->userWithRoles(['BUMDes Operator']);
        BumdesStaff::create(['user_id' => $operator->id, 'employment_status' => 'active']);
        $this->actingAs($operator)->post(route('services.packages.store'), ['name' => 'Illegal', 'monthly_price' => '1', 'validity_days' => 30])->assertForbidden();

        $this->actingAs($owner)->post(route('services.installations.store'), ['package_id' => $package->id, 'installation_address' => 'RT 01/RW 01'])->assertRedirect();
        $installation = InstallationRequest::firstOrFail();
        $this->actingAs($manager)->post(route('services.installations.review', $installation), ['status' => 'approved'])->assertUnprocessable();
        $this->assertSame('submitted', $installation->fresh()->status);
        $this->actingAs($manager)->post(route('services.installations.review', $installation), ['status' => 'reviewing'])->assertRedirect();
        $this->actingAs($manager)->post(route('services.installations.review', $installation->fresh()), ['status' => 'approved'])->assertRedirect();
        $this->actingAs($manager)->post(route('services.installations.review', $installation->fresh()), ['status' => 'scheduled'])->assertSessionHasErrors('scheduled_at');
        $schedule = now()->addDay()->format('Y-m-d\\TH:i');
        $this->actingAs($manager)->post(route('services.installations.review', $installation->fresh()), ['status' => 'scheduled', 'scheduled_at' => $schedule])->assertRedirect();
        $this->actingAs($manager)->post(route('services.installations.review', $installation->fresh()), ['status' => 'installed'])->assertRedirect();
        $this->actingAs($owner)->get(route('services.index'))->assertOk()->assertSee('Pemasangan selesai')->assertSee('Jadwal pemasangan:');
        $this->assertNotNull($installation->fresh()->completed_at);

        $invoice = SubscriptionInvoice::firstOrFail();
        $this->assertSame('active', $invoice->subscription->status);
        $this->assertSame('75000.00', $invoice->amount);
        $this->actingAs($operator)->put(route('services.subscriptions.status', $invoice->subscription), ['status' => 'suspended'])->assertForbidden();
        $this->actingAs($manager)->put(route('services.subscriptions.status', $invoice->subscription), ['status' => 'suspended'])->assertRedirect();
        $this->assertSame('suspended', $invoice->subscription->fresh()->status);
        $this->actingAs($manager)->put(route('services.subscriptions.status', $invoice->subscription), ['status' => 'active'])->assertRedirect();
        $this->actingAs($other)->post(route('services.payments.store', $invoice), ['payment_method' => 'cash'])->assertForbidden();
        $this->actingAs($owner)->post(route('services.payments.store', $invoice), ['payment_method' => 'bank_transfer', 'amount' => '1'])->assertRedirect();
        $this->assertDatabaseHas('payments', ['invoice_id' => $invoice->id, 'amount' => '75000.00', 'status' => 'pending', 'submitted_by' => $owner->id]);
        $payment = Payment::firstOrFail();
        $this->actingAs($other)->get(route('services.index'))->assertOk()->assertDontSee($invoice->invoice_number);
        $this->actingAs($operator)->post(route('services.payments.review', $payment), ['decision' => 'paid'])->assertRedirect();
        $this->assertDatabaseHas('subscription_invoices', ['id' => $invoice->id, 'status' => 'paid']);
        $this->assertDatabaseHas('payment_events', ['payment_id' => $payment->id, 'event_type' => 'payment.confirmed', 'signature_verified' => 0]);
    }

    public function test_active_bumdes_manager_can_confirm_manual_payment(): void
    {
        [$residentUser, $resident] = $this->residentWithProfile('manager-payment-review');
        $manager = $this->userWithRoles(['BUMDes Manager']);
        BumdesStaff::query()->create(['user_id' => $manager->id, 'employment_status' => 'active']);
        $package = InternetPackage::query()->create(['name' => 'Paket Uji', 'monthly_price' => '50000.00', 'validity_days' => 30, 'is_active' => true]);
        $subscription = InternetSubscription::query()->create([
            'resident_id' => $resident->id,
            'package_id' => $package->id,
            'start_date' => today(),
            'status' => 'active',
        ]);
        $invoice = SubscriptionInvoice::query()->create([
            'subscription_id' => $subscription->id,
            'invoice_number' => 'INV-TEST-MANAGER-01',
            'billing_period_start' => today()->startOfMonth(),
            'billing_period_end' => today()->endOfMonth(),
            'amount' => '50000.00',
            'due_date' => today()->addDays(7),
            'status' => 'pending',
        ]);
        $payment = Payment::query()->create([
            'invoice_id' => $invoice->id,
            'payment_method' => 'bank_transfer',
            'payment_provider' => 'manual',
            'amount' => '50000.00',
            'status' => 'pending',
            'submitted_by' => $residentUser->id,
        ]);

        $this->actingAs($manager)->post(route('services.payments.review', $payment), ['decision' => 'paid'])->assertRedirect();

        $this->assertDatabaseHas('payments', ['id' => $payment->id, 'status' => 'paid']);
        $this->assertDatabaseHas('subscription_invoices', ['id' => $invoice->id, 'status' => 'paid']);
    }

    public function test_internet_payment_queue_excludes_marketplace_payments_and_cannot_review_them(): void
    {
        [$residentUser, $resident] = $this->residentWithProfile('payment-queue-separation');
        $manager = $this->userWithRoles(['BUMDes Manager']);
        BumdesStaff::query()->create(['user_id' => $manager->id, 'employment_status' => 'active']);
        $package = InternetPackage::query()->create(['name' => 'Paket Uji', 'monthly_price' => '50000.00', 'validity_days' => 30, 'is_active' => true]);
        $subscription = InternetSubscription::query()->create([
            'resident_id' => $resident->id,
            'package_id' => $package->id,
            'start_date' => today(),
            'status' => 'active',
        ]);
        $invoice = SubscriptionInvoice::query()->create([
            'subscription_id' => $subscription->id,
            'invoice_number' => 'INV-QUEUE-SEPARATION',
            'billing_period_start' => today()->startOfMonth(),
            'billing_period_end' => today()->endOfMonth(),
            'amount' => '50000.00',
            'due_date' => today()->addDays(7),
            'status' => 'pending',
        ]);
        Payment::query()->create([
            'invoice_id' => $invoice->id,
            'payment_method' => 'bank_transfer',
            'payment_provider' => 'manual',
            'amount' => '50000.00',
            'status' => 'pending',
            'submitted_by' => $residentUser->id,
        ]);

        $sellerUser = $this->userWithRoles(['Resident']);
        $seller = Seller::query()->create(['user_id' => $sellerUser->id, 'business_name' => 'Toko Uji', 'verification_status' => 'approved']);
        $group = CheckoutGroup::query()->create([
            'buyer_id' => $residentUser->id,
            'checkout_number' => 'CHK-QUEUE-SEPARATION',
            'request_key' => str_repeat('a', 64),
            'grand_total' => '10000.00',
            'status' => 'pending',
        ]);
        $order = Order::query()->create([
            'checkout_group_id' => $group->id,
            'buyer_id' => $residentUser->id,
            'seller_id' => $seller->id,
            'order_number' => 'ORD-QUEUE-SEPARATION',
            'total_amount' => '10000.00',
            'payment_method' => 'bank_transfer',
            'status' => 'awaiting_payment',
        ]);
        $marketplacePayment = Payment::query()->create([
            'order_id' => $order->id,
            'payment_method' => 'bank_transfer',
            'payment_provider' => 'manual',
            'amount' => '10000.00',
            'status' => 'pending',
            'submitted_by' => $residentUser->id,
        ]);

        $this->actingAs($manager)->get(route('services.index'))
            ->assertOk()
            ->assertSee($invoice->invoice_number)
            ->assertDontSee('ORD-QUEUE-SEPARATION');

        $this->actingAs($manager)->post(route('services.payments.review', $marketplacePayment), ['decision' => 'paid'])->assertNotFound();
        $this->assertDatabaseHas('payments', ['id' => $marketplacePayment->id, 'status' => 'pending']);
    }

    public function test_installation_rejection_and_cancellation_require_a_reason(): void
    {
        [$resident] = $this->residentWithProfile('installation-reason');
        $manager = $this->userWithRoles(['BUMDes Manager']);
        BumdesStaff::query()->create(['user_id' => $manager->id, 'employment_status' => 'active']);
        $package = InternetPackage::query()->create(['name' => 'Paket Uji', 'monthly_price' => '50000.00', 'validity_days' => 30, 'is_active' => true]);
        $this->actingAs($resident)->post(route('services.installations.store'), [
            'package_id' => $package->id,
            'installation_address' => 'RT 01/RW 01',
        ])->assertRedirect();
        $installation = InstallationRequest::query()->firstOrFail();

        $this->actingAs($manager)->post(route('services.installations.review', $installation), [
            'status' => 'rejected',
        ])->assertSessionHasErrors('review_note');

        $this->actingAs($manager)->post(route('services.installations.review', $installation), [
            'status' => 'cancelled',
            'review_note' => 'Warga meminta pembatalan.',
        ])->assertRedirect();

        $this->assertSame('cancelled', $installation->fresh()->status);
        $this->assertSame(2, $installation->events()->count());
    }

    public function test_internet_package_bandwidth_is_entered_in_mbps_and_stored_as_kbps(): void
    {
        $manager = $this->userWithRoles(['BUMDes Manager']);
        BumdesStaff::query()->create(['user_id' => $manager->id, 'employment_status' => 'active']);

        $this->actingAs($manager)->post(route('services.packages.store'), [
            'name' => 'Paket Cepat',
            'monthly_price' => '125000',
            'bandwidth_download_mbps' => '20.5',
            'bandwidth_upload_mbps' => '5.25',
            'validity_days' => 30,
            'is_active' => 1,
        ])->assertRedirect();

        $package = InternetPackage::query()->where('name', 'Paket Cepat')->firstOrFail();
        $this->assertSame(20500, $package->bandwidth_download_kbps);
        $this->assertSame(5250, $package->bandwidth_upload_kbps);

        $this->actingAs($manager)->put(route('services.packages.update', $package), [
            'name' => 'Paket Cepat',
            'monthly_price' => '125000',
            'bandwidth_download_mbps' => '30',
            'bandwidth_upload_mbps' => '10.5',
            'validity_days' => 30,
            'is_active' => 1,
        ])->assertRedirect();

        $this->assertSame(30000, $package->fresh()->bandwidth_download_kbps);
        $this->assertSame(10500, $package->fresh()->bandwidth_upload_kbps);
    }

    public function test_internet_management_page_is_manager_only_and_saves_mbps_values(): void
    {
        $manager = $this->userWithRoles(['BUMDes Manager']);
        BumdesStaff::query()->create(['user_id' => $manager->id, 'employment_status' => 'active']);
        $operator = $this->userWithRoles(['BUMDes Operator']);
        BumdesStaff::query()->create(['user_id' => $operator->id, 'employment_status' => 'active']);
        $resident = $this->userWithRoles(['Resident']);

        $this->actingAs($manager)->get(route('services.management'))
            ->assertOk()
            ->assertSee('Pengaturan Internet & Wi‑Fi')
            ->assertSee('Kecepatan unduh (Mbps)');

        $this->actingAs($operator)->get(route('services.management'))->assertForbidden();
        $this->actingAs($resident)->get(route('services.management'))->assertForbidden();

        $this->actingAs($manager)->post(route('services.packages.store'), [
            'name' => 'Paket 20 Mbps',
            'monthly_price' => '125000',
            'bandwidth_download_mbps' => '20',
            'bandwidth_upload_mbps' => '5.5',
            'validity_days' => 30,
            'is_active' => 1,
        ])->assertRedirect();

        $this->assertDatabaseHas('internet_packages', [
            'name' => 'Paket 20 Mbps',
            'bandwidth_download_kbps' => 20000,
            'bandwidth_upload_kbps' => 5500,
        ]);
    }

    public function test_wifi_eligibility_does_not_require_paid_subscription_and_gateway_is_simulated(): void
    {
        [$residentUser] = $this->residentWithProfile('wifi');
        $manager = $this->userWithRoles(['BUMDes Manager']);
        BumdesStaff::create(['user_id' => $manager->id, 'employment_status' => 'active']);
        $this->actingAs($manager)->post(route('services.wifi.networks.store'), [
            'name' => 'Balai Desa', 'location' => 'Balai Desa', 'ssid' => 'Desa-Publik', 'status' => 'active',
            'eligibility' => 'authenticated_residents', 'session_minutes' => 45, 'terms' => 'Gunakan dengan sopan.',
        ])->assertRedirect();
        $network = WifiNetwork::firstOrFail();
        $this->assertInstanceOf(SimulatedWifiGateway::class, app(WifiGateway::class));

        $this->actingAs($residentUser)->post(route('services.wifi.sessions.store', $network), ['terms_accepted' => '1'])->assertRedirect()->assertSessionHas('status', fn ($message) => str_contains($message, 'belum mengaktifkan akses internet'));
        $this->assertDatabaseHas('wifi_access_logs', ['user_id' => $residentUser->id, 'wifi_network_id' => $network->id, 'authentication_status' => 'success']);
        $this->assertSame(0, InternetSubscription::count());
        $this->assertNull(WifiAccessLog::firstOrFail()->ip_address);
    }

    public function test_resident_complaint_is_encrypted_and_hidden_from_other_residents(): void
    {
        [$owner] = $this->residentWithProfile('complaint-owner');
        [$stranger] = $this->residentWithProfile('complaint-stranger');
        $this->actingAs($owner)->post(route('services.requests.store'), ['service_type' => 'complaint', 'subject' => 'Koneksi sering putus', 'description' => 'Alamat rumah dan detail kendala rahasia'])->assertRedirect();
        $item = ServiceRequest::firstOrFail();
        $this->assertStringNotContainsString('detail kendala rahasia', $item->getRawOriginal('description'));
        $this->actingAs($stranger)->get(route('services.index'))->assertOk()->assertDontSee('Koneksi sering putus');
    }

    public function test_monthly_invoice_command_keeps_a_single_invoice_per_subscription_and_period(): void
    {
        [$owner, $resident] = $this->residentWithProfile('monthly');
        $package = InternetPackage::create(['name' => 'Warga Bulanan', 'monthly_price' => '12000.00', 'validity_days' => 30, 'is_active' => true]);
        $subscription = InternetSubscription::create(['resident_id' => $resident->id, 'package_id' => $package->id, 'start_date' => today()->startOfMonth(), 'status' => 'active']);
        $month = today()->format('Y-m');
        $this->artisan('internet:invoices:generate', ['--month' => $month])->assertExitCode(0);
        $this->artisan('internet:invoices:generate', ['--month' => $month])->assertExitCode(0);
        $this->assertDatabaseCount('subscription_invoices', 1);
        $invoice = SubscriptionInvoice::firstOrFail();
        $this->assertSame(today()->startOfMonth()->toDateString(), $invoice->billing_period_start->toDateString());
        $this->assertSame('12000.00', $invoice->amount);
    }

    public function test_wifi_log_retention_supports_dry_run_then_prunes_only_expired_rows(): void
    {
        [$user] = $this->residentWithProfile('retention');
        $network = WifiNetwork::create(['name' => 'Balai', 'network_type' => 'public_wifi', 'location' => 'Balai desa', 'ssid' => 'Publik', 'status' => 'active', 'access_policy' => ['eligibility' => 'authenticated_residents']]);
        $expired = WifiAccessLog::create(['user_id' => $user->id, 'wifi_network_id' => $network->id, 'authentication_status' => 'success', 'connected_at' => now()->subDays(100)]);
        $recent = WifiAccessLog::create(['user_id' => $user->id, 'wifi_network_id' => $network->id, 'authentication_status' => 'success', 'connected_at' => now()->subDays(2)]);

        $this->artisan('wifi:logs:prune', ['--dry-run' => true])->assertExitCode(0);
        $this->assertDatabaseHas('wifi_access_logs', ['id' => $expired->id]);
        $this->artisan('wifi:logs:prune')->assertExitCode(0);
        $this->assertDatabaseMissing('wifi_access_logs', ['id' => $expired->id]);
        $this->assertDatabaseHas('wifi_access_logs', ['id' => $recent->id]);
    }

    public function test_resident_without_profile_gets_a_profile_link_instead_of_an_empty_service_page(): void
    {
        $resident = $this->userWithRoles(['Resident']);

        $this->actingAs($resident)->get(route('services.index'))
            ->assertOk()
            ->assertSee('Akun Anda belum memiliki profil warga.')
            ->assertSee('Buat profil warga')
            ->assertSee(route('profile.create'));
    }

    /** @return array{User, Resident} */
    private function residentWithProfile(string $suffix): array
    {
        $user = $this->userWithRoles(['Resident']);
        $village = Village::create(['code' => 'V'.strtoupper(substr(md5($suffix), 0, 8)), 'name' => 'Desa Layanan']);
        $rw = Region::create(['village_id' => $village->id, 'region_type' => 'rw', 'region_code' => 'RW-01', 'name' => 'RW 01']);
        $rt = Region::create(['village_id' => $village->id, 'parent_id' => $rw->id, 'region_type' => 'rt', 'region_code' => 'RT-01', 'name' => 'RT 01']);
        $resident = (new Resident)->forceFill(['user_id' => $user->id, 'region_id' => $rt->id, 'full_name_ciphertext' => 'enc', 'region_verified_at' => now()]);
        $resident->save();

        return [$user, $resident];
    }

    /** @param list<string> $roles */
    private function userWithRoles(array $roles): User
    {
        $user = User::factory()->active()->create();
        $user->roles()->attach(Role::whereIn('name', $roles)->pluck('id'));

        return $user->load('roles');
    }
}
