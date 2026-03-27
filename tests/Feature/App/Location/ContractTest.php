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

class ContractTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Company $company;
    private RentalAsset $asset;
    private Customer $customer;

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
        $this->asset    = RentalAsset::factory()->create(['company_id' => $this->company->id, 'status' => 'available']);
        $this->customer = Customer::factory()->create(['company_id' => $this->company->id]);
    }

    private function tenantGet(string $path) { $sep = str_contains($path, '?') ? '&' : '?'; return $this->actingAs($this->owner)->get('/app' . $path . $sep . '_tenant=test-co'); }
    private function tenantPost(string $path, array $data = []) { $sep = str_contains($path, '?') ? '&' : '?'; return $this->actingAs($this->owner)->post('/app' . $path . $sep . '_tenant=test-co', $data); }
    private function tenantPatch(string $path, array $data = []) { $sep = str_contains($path, '?') ? '&' : '?'; return $this->actingAs($this->owner)->patch('/app' . $path . $sep . '_tenant=test-co', $data); }

    #[Test]
    public function contracts_index_loads(): void
    {
        $this->tenantGet('/location/contracts')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Location/Contracts/Index'));
    }

    #[Test]
    public function contracts_index_filters_by_status(): void
    {
        RentalContract::factory()->create([
            'company_id' => $this->company->id, 'rental_asset_id' => $this->asset->id,
            'customer_id' => $this->customer->id, 'user_id' => $this->owner->id, 'status' => 'active',
        ]);
        $asset2 = RentalAsset::factory()->create(['company_id' => $this->company->id]);
        RentalContract::factory()->completed()->create([
            'company_id' => $this->company->id, 'rental_asset_id' => $asset2->id,
            'customer_id' => $this->customer->id, 'user_id' => $this->owner->id,
        ]);

        $this->tenantGet('/location/contracts?status=active')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Location/Contracts/Index')->has('contracts.data', 1));
    }

    #[Test]
    public function contracts_create_form_loads(): void
    {
        $this->tenantGet('/location/contracts/create')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Location/Contracts/Form')->has('assets')->has('customers'));
    }

    #[Test]
    public function store_contract(): void
    {
        $response = $this->tenantPost('/location/contracts', [
            'rental_asset_id'   => $this->asset->id,
            'customer_id'       => $this->customer->id,
            'start_date'        => '2026-04-01',
            'end_date'          => '2026-06-30',
            'total_amount'      => 450000,
            'deposit_amount'    => 150000,
            'payment_frequency' => 'monthly',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('rental_contracts', [
            'company_id'      => $this->company->id,
            'rental_asset_id' => $this->asset->id,
            'customer_id'     => $this->customer->id,
            'total_amount'    => 450000,
            'status'          => 'active',
        ]);

        // Asset should be marked as rented
        $this->asset->refresh();
        $this->assertEquals('rented', $this->asset->status);
    }

    #[Test]
    public function store_contract_generates_payment_schedule(): void
    {
        $this->tenantPost('/location/contracts', [
            'rental_asset_id'   => $this->asset->id,
            'customer_id'       => $this->customer->id,
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-03-31',
            'total_amount'      => 300000,
            'deposit_amount'    => 0,
            'payment_frequency' => 'monthly',
        ]);

        $contract = RentalContract::where('company_id', $this->company->id)->first();
        $this->assertNotNull($contract);

        $payments = RentalPayment::where('rental_contract_id', $contract->id)->get();
        $this->assertGreaterThanOrEqual(3, $payments->count());
        $this->assertEquals('pending', $payments->first()->status);
    }

    #[Test]
    public function store_contract_one_time_payment(): void
    {
        $this->tenantPost('/location/contracts', [
            'rental_asset_id'   => $this->asset->id,
            'customer_id'       => $this->customer->id,
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-12-31',
            'total_amount'      => 500000,
            'payment_frequency' => 'one_time',
        ]);

        $contract = RentalContract::where('company_id', $this->company->id)->first();
        $payments = RentalPayment::where('rental_contract_id', $contract->id)->get();
        $this->assertEquals(1, $payments->count());
        $this->assertEquals(500000, $payments->first()->amount);
    }

    #[Test]
    public function store_contract_validates_required(): void
    {
        $this->tenantPost('/location/contracts', [])
            ->assertSessionHasErrors(['rental_asset_id', 'customer_id', 'start_date', 'end_date', 'total_amount', 'payment_frequency']);
    }

    #[Test]
    public function store_contract_validates_end_after_start(): void
    {
        $this->tenantPost('/location/contracts', [
            'rental_asset_id' => $this->asset->id, 'customer_id' => $this->customer->id,
            'start_date' => '2026-06-01', 'end_date' => '2026-01-01',
            'total_amount' => 100000, 'payment_frequency' => 'monthly',
        ])->assertSessionHasErrors(['end_date']);
    }

    #[Test]
    public function show_contract(): void
    {
        $contract = RentalContract::factory()->create([
            'company_id' => $this->company->id, 'rental_asset_id' => $this->asset->id,
            'customer_id' => $this->customer->id, 'user_id' => $this->owner->id,
        ]);

        $this->tenantGet("/location/contracts/{$contract->id}")->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Location/Contracts/Show')->has('contract'));
    }

    #[Test]
    public function update_contract_status_to_completed(): void
    {
        $contract = RentalContract::factory()->create([
            'company_id' => $this->company->id, 'rental_asset_id' => $this->asset->id,
            'customer_id' => $this->customer->id, 'user_id' => $this->owner->id, 'status' => 'active',
        ]);
        $this->asset->update(['status' => 'rented']);

        $this->tenantPatch("/location/contracts/{$contract->id}/status", ['status' => 'completed'])->assertRedirect();

        $contract->refresh();
        $this->assertEquals('completed', $contract->status);

        $this->asset->refresh();
        $this->assertEquals('available', $this->asset->status);
    }

    #[Test]
    public function update_contract_status_to_cancelled_frees_asset(): void
    {
        $contract = RentalContract::factory()->create([
            'company_id' => $this->company->id, 'rental_asset_id' => $this->asset->id,
            'customer_id' => $this->customer->id, 'user_id' => $this->owner->id, 'status' => 'active',
        ]);
        $this->asset->update(['status' => 'rented']);

        $this->tenantPatch("/location/contracts/{$contract->id}/status", ['status' => 'cancelled'])->assertRedirect();

        $this->asset->refresh();
        $this->assertEquals('available', $this->asset->status);
    }

    #[Test]
    public function renew_contract(): void
    {
        $contract = RentalContract::factory()->create([
            'company_id' => $this->company->id, 'rental_asset_id' => $this->asset->id,
            'customer_id' => $this->customer->id, 'user_id' => $this->owner->id, 'status' => 'active',
        ]);

        $this->tenantPost("/location/contracts/{$contract->id}/renew", [
            'start_date'        => '2026-07-01',
            'end_date'          => '2026-12-31',
            'total_amount'      => 600000,
            'deposit_amount'    => 100000,
            'payment_frequency' => 'monthly',
        ])->assertRedirect();

        $contract->refresh();
        $this->assertEquals('renewed', $contract->status);

        $newContract = RentalContract::where('renewed_from_id', $contract->id)->first();
        $this->assertNotNull($newContract);
        $this->assertEquals('active', $newContract->status);
        $this->assertEquals(600000, $newContract->total_amount);
    }

    #[Test]
    public function return_deposit(): void
    {
        $contract = RentalContract::factory()->create([
            'company_id' => $this->company->id, 'rental_asset_id' => $this->asset->id,
            'customer_id' => $this->customer->id, 'user_id' => $this->owner->id,
            'deposit_amount' => 200000, 'deposit_returned' => false,
        ]);

        $this->tenantPost("/location/contracts/{$contract->id}/return-deposit", [
            'deposit_returned_amount' => 180000,
        ])->assertRedirect();

        $contract->refresh();
        $this->assertTrue($contract->deposit_returned);
        $this->assertEquals(180000, $contract->deposit_returned_amount);
    }

    #[Test]
    public function return_deposit_validates_max(): void
    {
        $contract = RentalContract::factory()->create([
            'company_id' => $this->company->id, 'rental_asset_id' => $this->asset->id,
            'customer_id' => $this->customer->id, 'user_id' => $this->owner->id,
            'deposit_amount' => 100000,
        ]);

        $this->tenantPost("/location/contracts/{$contract->id}/return-deposit", [
            'deposit_returned_amount' => 150000,
        ])->assertSessionHasErrors(['deposit_returned_amount']);
    }

    #[Test]
    public function store_contract_generates_reference(): void
    {
        $this->tenantPost('/location/contracts', [
            'rental_asset_id'   => $this->asset->id,
            'customer_id'       => $this->customer->id,
            'start_date'        => '2026-01-01',
            'end_date'          => '2026-06-30',
            'total_amount'      => 300000,
            'payment_frequency' => 'monthly',
        ]);

        $contract = RentalContract::where('company_id', $this->company->id)->first();
        $this->assertStringStartsWith('LOC-', $contract->reference);
    }
}
