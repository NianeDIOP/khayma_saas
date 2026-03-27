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

class CalendarTest extends TestCase
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
    public function calendar_index_loads(): void
    {
        $this->tenantGet('/location/calendar')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Location/Calendar/Index')
                ->has('assets')->has('contracts')->has('expiringContracts')->has('overduePayments'));
    }

    #[Test]
    public function calendar_shows_active_contracts(): void
    {
        $asset    = RentalAsset::factory()->create(['company_id' => $this->company->id]);
        $customer = Customer::factory()->create(['company_id' => $this->company->id]);

        RentalContract::factory()->create([
            'company_id' => $this->company->id, 'rental_asset_id' => $asset->id,
            'customer_id' => $customer->id, 'user_id' => $this->owner->id, 'status' => 'active',
        ]);
        RentalContract::factory()->completed()->create([
            'company_id' => $this->company->id, 'rental_asset_id' => $asset->id,
            'customer_id' => $customer->id, 'user_id' => $this->owner->id,
        ]);

        $this->tenantGet('/location/calendar')->assertOk()
            ->assertInertia(fn ($p) => $p->has('contracts', 1));
    }

    #[Test]
    public function calendar_detects_expiring_contracts(): void
    {
        $asset    = RentalAsset::factory()->create(['company_id' => $this->company->id]);
        $customer = Customer::factory()->create(['company_id' => $this->company->id]);

        // Expiring in 3 days
        RentalContract::factory()->create([
            'company_id' => $this->company->id, 'rental_asset_id' => $asset->id,
            'customer_id' => $customer->id, 'user_id' => $this->owner->id,
            'status' => 'active', 'end_date' => now()->addDays(3)->toDateString(),
        ]);

        $this->tenantGet('/location/calendar')->assertOk()
            ->assertInertia(fn ($p) => $p->has('expiringContracts', 1));
    }

    #[Test]
    public function calendar_shows_overdue_payments(): void
    {
        $asset    = RentalAsset::factory()->create(['company_id' => $this->company->id]);
        $customer = Customer::factory()->create(['company_id' => $this->company->id]);
        $contract = RentalContract::factory()->create([
            'company_id' => $this->company->id, 'rental_asset_id' => $asset->id,
            'customer_id' => $customer->id, 'user_id' => $this->owner->id,
        ]);

        RentalPayment::factory()->overdue()->create([
            'company_id' => $this->company->id, 'rental_contract_id' => $contract->id,
        ]);

        $this->tenantGet('/location/calendar')->assertOk()
            ->assertInertia(fn ($p) => $p->has('overduePayments', 1));
    }
}
