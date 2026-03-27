<?php

namespace Tests\Feature\App\Location;

use App\Models\Company;
use App\Models\Customer;
use App\Models\RentalAsset;
use App\Models\RentalContract;
use App\Models\RentalPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->company = Company::create([
            'name' => 'Test Co', 'slug' => 'test-co', 'email' => 'test@co.sn',
            'subscription_status' => 'trial', 'trial_ends_at' => now()->addDays(14), 'is_active' => true,
        ]);
        $this->owner = User::factory()->create(['is_super_admin' => false]);
        $this->company->users()->attach($this->owner->id, ['role' => 'owner', 'joined_at' => now()]);
    }

    private function tenantGet(string $path) { $sep = str_contains($path, '?') ? '&' : '?'; return $this->actingAs($this->owner)->get('/app' . $path . $sep . '_tenant=test-co'); }

    #[Test]
    public function reports_index_loads(): void
    {
        $this->tenantGet('/location/reports')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Location/Reports/Index')
                ->has('totalAssets')->has('availableAssets')->has('rentedAssets')
                ->has('totalRevenue')->has('occupancyRate')->has('totalDebts'));
    }

    #[Test]
    public function reports_counts_assets_by_status(): void
    {
        RentalAsset::factory()->count(3)->create(['company_id' => $this->company->id, 'status' => 'available']);
        RentalAsset::factory()->count(2)->create(['company_id' => $this->company->id, 'status' => 'rented']);
        RentalAsset::factory()->create(['company_id' => $this->company->id, 'status' => 'maintenance']);

        $this->tenantGet('/location/reports')->assertOk()
            ->assertInertia(fn ($p) => $p
                ->where('totalAssets', 6)
                ->where('availableAssets', 3)
                ->where('rentedAssets', 2)
                ->where('maintenanceAssets', 1));
    }

    #[Test]
    public function reports_calculates_occupancy_rate(): void
    {
        RentalAsset::factory()->count(3)->create(['company_id' => $this->company->id, 'status' => 'available']);
        RentalAsset::factory()->count(2)->create(['company_id' => $this->company->id, 'status' => 'rented']);

        $this->tenantGet('/location/reports')->assertOk()
            ->assertInertia(function ($p) {
                $rate = $p->toArray()['props']['occupancyRate'];
                $this->assertEquals(40, (int) $rate);
            });
    }

    #[Test]
    public function reports_calculates_revenue(): void
    {
        $asset    = RentalAsset::factory()->create(['company_id' => $this->company->id]);
        $customer = Customer::factory()->create(['company_id' => $this->company->id]);
        $contract = RentalContract::factory()->create([
            'company_id' => $this->company->id, 'rental_asset_id' => $asset->id,
            'customer_id' => $customer->id, 'user_id' => $this->owner->id,
        ]);

        RentalPayment::factory()->create([
            'company_id' => $this->company->id, 'rental_contract_id' => $contract->id,
            'amount' => 100000, 'amount_paid' => 100000, 'status' => 'paid',
            'payment_date' => now()->toDateString(),
        ]);
        RentalPayment::factory()->create([
            'company_id' => $this->company->id, 'rental_contract_id' => $contract->id,
            'amount' => 100000, 'amount_paid' => 50000, 'status' => 'partial',
            'payment_date' => now()->toDateString(),
        ]);

        $this->tenantGet('/location/reports')->assertOk()
            ->assertInertia(function ($p) {
                $revenue = $p->toArray()['props']['totalRevenue'];
                $this->assertEquals(150000, (int) $revenue);
            });
    }

    #[Test]
    public function reports_counts_contracts(): void
    {
        $asset    = RentalAsset::factory()->create(['company_id' => $this->company->id]);
        $customer = Customer::factory()->create(['company_id' => $this->company->id]);

        RentalContract::factory()->count(2)->create([
            'company_id' => $this->company->id, 'rental_asset_id' => $asset->id,
            'customer_id' => $customer->id, 'user_id' => $this->owner->id, 'status' => 'active',
        ]);
        RentalContract::factory()->completed()->create([
            'company_id' => $this->company->id, 'rental_asset_id' => $asset->id,
            'customer_id' => $customer->id, 'user_id' => $this->owner->id,
        ]);

        $this->tenantGet('/location/reports')->assertOk()
            ->assertInertia(fn ($p) => $p
                ->where('activeContracts', 2)
                ->where('completedContracts', 1));
    }

    #[Test]
    public function reports_calculates_debts(): void
    {
        $asset    = RentalAsset::factory()->create(['company_id' => $this->company->id]);
        $customer = Customer::factory()->create(['company_id' => $this->company->id]);
        $contract = RentalContract::factory()->create([
            'company_id' => $this->company->id, 'rental_asset_id' => $asset->id,
            'customer_id' => $customer->id, 'user_id' => $this->owner->id,
        ]);

        RentalPayment::factory()->create([
            'company_id' => $this->company->id, 'rental_contract_id' => $contract->id,
            'amount' => 100000, 'amount_paid' => 30000, 'status' => 'partial',
        ]);
        RentalPayment::factory()->create([
            'company_id' => $this->company->id, 'rental_contract_id' => $contract->id,
            'amount' => 80000, 'amount_paid' => 0, 'status' => 'pending',
        ]);

        $this->tenantGet('/location/reports')->assertOk()
            ->assertInertia(function ($p) {
                $totalDebts = $p->toArray()['props']['totalDebts'];
                $this->assertEquals(150000, (int) $totalDebts);
            });
    }

    #[Test]
    public function reports_date_filter(): void
    {
        $asset    = RentalAsset::factory()->create(['company_id' => $this->company->id]);
        $customer = Customer::factory()->create(['company_id' => $this->company->id]);
        $contract = RentalContract::factory()->create([
            'company_id' => $this->company->id, 'rental_asset_id' => $asset->id,
            'customer_id' => $customer->id, 'user_id' => $this->owner->id,
        ]);

        RentalPayment::factory()->create([
            'company_id' => $this->company->id, 'rental_contract_id' => $contract->id,
            'amount' => 100000, 'amount_paid' => 100000, 'status' => 'paid',
            'payment_date' => '2026-01-15',
        ]);
        RentalPayment::factory()->create([
            'company_id' => $this->company->id, 'rental_contract_id' => $contract->id,
            'amount' => 200000, 'amount_paid' => 200000, 'status' => 'paid',
            'payment_date' => '2026-03-15',
        ]);

        $this->tenantGet('/location/reports?start_date=2026-01-01&end_date=2026-01-31')->assertOk()
            ->assertInertia(function ($p) {
                $revenue = $p->toArray()['props']['totalRevenue'];
                $this->assertEquals(100000, (int) $revenue);
            });
    }

    #[Test]
    public function reports_returns_top_debtors(): void
    {
        $this->tenantGet('/location/reports')->assertOk()
            ->assertInertia(fn ($p) => $p->has('topDebtors'));
    }

    #[Test]
    public function reports_returns_assets_by_type(): void
    {
        RentalAsset::factory()->create(['company_id' => $this->company->id, 'type' => 'vehicle']);
        RentalAsset::factory()->count(2)->create(['company_id' => $this->company->id, 'type' => 'real_estate']);

        $this->tenantGet('/location/reports')->assertOk()
            ->assertInertia(fn ($p) => $p->has('assetsByType'));
    }
}
