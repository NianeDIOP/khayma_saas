<?php

namespace Tests\Feature\App\Quincaillerie;

use App\Models\Company;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SupplierPaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->company = Company::create(['name' => 'Test Co', 'slug' => 'test-co', 'email' => 'test@co.sn', 'subscription_status' => 'trial', 'trial_ends_at' => now()->addDays(14), 'is_active' => true]);
        $this->owner = User::factory()->create(['is_super_admin' => false]);
        $this->company->users()->attach($this->owner->id, ['role' => 'owner', 'joined_at' => now()]);
    }

    private function tenantGet(string $path) { return $this->actingAs($this->owner)->get('/app' . $path . '?_tenant=test-co'); }
    private function tenantPost(string $path, array $data = []) { return $this->actingAs($this->owner)->post('/app' . $path . '?_tenant=test-co', $data); }

    #[Test]
    public function supplier_payments_index_loads(): void
    {
        $this->tenantGet('/quincaillerie/supplier-payments')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Quincaillerie/SupplierPayments/Index'));
    }

    #[Test]
    public function supplier_payments_create_loads(): void
    {
        $this->tenantGet('/quincaillerie/supplier-payments/create')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Quincaillerie/SupplierPayments/Form'));
    }

    #[Test]
    public function store_payment_decreases_outstanding_balance(): void
    {
        $supplier = Supplier::factory()->create(['company_id' => $this->company->id, 'outstanding_balance' => 50000]);

        $response = $this->tenantPost('/quincaillerie/supplier-payments', [
            'supplier_id' => $supplier->id,
            'amount'      => 10000,
            'method'      => 'cash',
            'date'        => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('supplier_payments', ['supplier_id' => $supplier->id, 'amount' => 10000]);
        $this->assertDatabaseHas('suppliers', ['id' => $supplier->id, 'outstanding_balance' => 40000]);
    }
}
