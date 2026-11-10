<?php

namespace Tests\Feature;

use App\Models\BumdesStaff;
use App\Models\BusinessExpense;
use App\Models\InternetPackage;
use App\Models\InternetSubscription;
use App\Models\Payment;
use App\Models\Region;
use App\Models\Resident;
use App\Models\Role;
use App\Models\SubscriptionInvoice;
use App\Models\User;
use App\Models\Village;
use App\Services\NavigationMenu;
use Database\Seeders\RolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesSeeder::class);
    }

    public function test_report_counts_active_accounts_subscribers_paid_income_and_valid_expenses_for_selected_month(): void
    {
        $manager = $this->userWithRoles(['BUMDes Manager']);
        BumdesStaff::query()->create(['user_id' => $manager->id, 'employment_status' => 'active']);
        [$residentUser, $resident] = $this->residentWithProfile();
        $this->userWithRoles(['Village Admin']);
        $package = InternetPackage::query()->create(['name' => 'Bulanan', 'monthly_price' => '150000.00', 'validity_days' => 30, 'is_active' => true]);
        $subscription = InternetSubscription::query()->create([
            'resident_id' => $resident->id,
            'package_id' => $package->id,
            'start_date' => today()->startOfMonth(),
            'end_date' => today()->addDays(20),
            'status' => 'active',
        ]);
        InternetSubscription::query()->create([
            'resident_id' => $resident->id,
            'package_id' => $package->id,
            'start_date' => today()->startOfMonth(),
            'end_date' => today()->addDays(20),
            'status' => 'active',
        ]);
        $invoice = SubscriptionInvoice::query()->create([
            'subscription_id' => $subscription->id,
            'invoice_number' => 'INV-REPORT-PAID-01',
            'billing_period_start' => today()->startOfMonth(),
            'billing_period_end' => today()->endOfMonth(),
            'amount' => '150000.00',
            'due_date' => today()->addDays(7),
            'status' => 'paid',
            'paid_at' => now(),
        ]);
        Payment::query()->create([
            'invoice_id' => $invoice->id,
            'payment_method' => 'cash',
            'amount' => '150000.00',
            'status' => 'paid',
            'submitted_by' => $residentUser->id,
            'paid_at' => now(),
        ]);
        Payment::query()->create([
            'invoice_id' => $invoice->id,
            'payment_method' => 'bank_transfer',
            'amount' => '80000.00',
            'status' => 'pending',
            'submitted_by' => $residentUser->id,
        ]);
        BusinessExpense::query()->create([
            'category' => 'maintenance',
            'description' => 'Perbaikan perangkat jaringan',
            'amount' => '30000.00',
            'expense_date' => today(),
            'status' => 'recorded',
            'recorded_by' => $manager->id,
        ]);
        BusinessExpense::query()->create([
            'category' => 'supplies',
            'description' => 'Catatan yang sudah dibatalkan',
            'amount' => '50000.00',
            'expense_date' => today(),
            'status' => 'voided',
            'recorded_by' => $manager->id,
            'voided_by' => $manager->id,
            'voided_at' => now(),
            'void_reason' => 'Nilai salah dicatat.',
        ]);

        $response = $this->actingAs($manager)->get(route('bumdes.reports.index', ['month' => today()->format('Y-m')]));

        $response->assertOk()->assertSee('Laporan usaha BUMDes')->assertSee('Pendapatan terbayar')->assertSee('Perbaikan perangkat jaringan');
        $routes = collect(app(NavigationMenu::class)->for($manager))->flatten(1)->pluck('route');
        $this->assertTrue($routes->contains('bumdes.reports.index'));
        $response->assertViewHas('summary', function (array $summary): bool {
            return $summary['active_resident_accounts'] === 1
                && $summary['active_subscribers'] === 1
                && $summary['confirmed_income'] === '150000.00'
                && $summary['operating_expenses'] === '30000.00'
                && $summary['operating_balance'] === '120000.00';
        });
    }

    public function test_manager_can_record_and_void_expense_without_deleting_the_audit_history(): void
    {
        $manager = $this->userWithRoles(['BUMDes Manager']);
        BumdesStaff::query()->create(['user_id' => $manager->id, 'employment_status' => 'active']);

        $this->actingAs($manager)->post(route('bumdes.reports.expenses.store'), [
            'category' => 'utilities',
            'description' => 'Tagihan listrik ruang layanan',
            'amount' => '275000.50',
            'expense_date' => today()->toDateString(),
        ])->assertRedirect();

        $expense = BusinessExpense::query()->firstOrFail();
        $this->assertSame('275000.50', $expense->amount);
        $this->assertDatabaseHas('audit_logs', ['action' => 'business_expense.recorded', 'entity_id' => $expense->id]);

        $this->actingAs($manager)->post(route('bumdes.reports.expenses.void', $expense), [
            'reason' => 'Nominal keliru dimasukkan.',
        ])->assertRedirect();

        $this->assertDatabaseHas('business_expenses', [
            'id' => $expense->id,
            'status' => 'voided',
            'void_reason' => 'Nominal keliru dimasukkan.',
        ]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'business_expense.voided', 'entity_id' => $expense->id]);
        $this->actingAs($manager)->post(route('bumdes.reports.expenses.void', $expense), [
            'reason' => 'Percobaan pembatalan kedua.',
        ])->assertStatus(409);
        $this->actingAs($manager)->get(route('bumdes.reports.index', ['month' => today()->format('Y-m')]))
            ->assertOk()
            ->assertSee('Tagihan listrik ruang layanan')
            ->assertSee('Dibatalkan');
    }

    public function test_operator_cannot_view_financial_reports_or_record_or_void_expenses(): void
    {
        $operator = $this->userWithRoles(['BUMDes Operator']);
        BumdesStaff::query()->create(['user_id' => $operator->id, 'employment_status' => 'active']);

        $routes = collect(app(NavigationMenu::class)->for($operator))->flatten(1)->pluck('route');
        $this->assertFalse($routes->contains('bumdes.reports.index'));
        $this->actingAs($operator)->get(route('bumdes.reports.index'))->assertForbidden();
        $this->actingAs($operator)->post(route('bumdes.reports.expenses.store'), [
            'category' => 'utilities',
            'description' => 'Tagihan listrik',
            'amount' => '10000',
            'expense_date' => today()->toDateString(),
        ])->assertForbidden();
        $this->assertDatabaseCount('business_expenses', 0);
    }

    /** @return array{User, Resident} */
    private function residentWithProfile(): array
    {
        $user = $this->userWithRoles(['Resident']);
        $village = Village::query()->create(['code' => 'REPORT01', 'name' => 'Desa Laporan']);
        $rw = Region::query()->create(['village_id' => $village->id, 'region_type' => 'rw', 'region_code' => 'RW-01', 'name' => 'RW 01']);
        $rt = Region::query()->create(['village_id' => $village->id, 'parent_id' => $rw->id, 'region_type' => 'rt', 'region_code' => 'RT-01', 'name' => 'RT 01']);
        $resident = (new Resident)->forceFill([
            'user_id' => $user->id,
            'region_id' => $rt->id,
            'full_name_ciphertext' => 'encrypted-name',
            'region_verified_at' => now(),
        ]);
        $resident->save();

        return [$user, $resident];
    }

    /** @param list<string> $roles */
    private function userWithRoles(array $roles): User
    {
        $user = User::factory()->active()->create();
        $user->roles()->attach(Role::query()->whereIn('name', $roles)->pluck('id'));

        return $user->load('roles');
    }
}
