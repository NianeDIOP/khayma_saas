<?php

namespace Tests\Feature\App\Quincaillerie;

use App\Models\Company;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SupplierReturnTest extends TestCase
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
    public function supplier_returns_index_loads(): void
    {
        $this->tenantGet('/quincaillerie/supplier-returns')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Quincaillerie/SupplierReturns/Index'));
    }

    #[Test]
    public function supplier_returns_create_loads(): void
    {
        $this->tenantGet('/quincaillerie/supplier-returns/create')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Quincaillerie/SupplierReturns/Form'));
    }

    #[Test]
    public function store_supplier_return(): void
    {
        $supplier = Supplier::factory()->create(['company_id' => $this->company->id]);
        $unit     = Unit::factory()->create(['company_id' => $this->company->id]);
        $product  = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $unit->id]);

        $response = $this->tenantPost('/quincaillerie/supplier-returns', [
            'supplier_id' => $supplier->id,
            'product_id'  => $product->id,
            'quantity'    => 5,
            'reason'      => 'Défectueux',
            'date'        => now()->toDateString(),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('supplier_returns', [
            'supplier_id' => $supplier->id,
            'product_id'  => $product->id,
            'quantity'    => 5,
        ]);
    }
}
