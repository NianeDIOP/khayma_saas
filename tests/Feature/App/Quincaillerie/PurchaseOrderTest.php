<?php

namespace Tests\Feature\App\Quincaillerie;

use App\Models\Company;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
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
    private function tenantPatch(string $path, array $data = []) { return $this->actingAs($this->owner)->patch('/app' . $path . '?_tenant=test-co', $data); }

    #[Test]
    public function purchase_orders_index_loads(): void
    {
        $this->tenantGet('/quincaillerie/purchase-orders')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Quincaillerie/PurchaseOrders/Index'));
    }

    #[Test]
    public function purchase_orders_create_loads(): void
    {
        $this->tenantGet('/quincaillerie/purchase-orders/create')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Quincaillerie/PurchaseOrders/Form'));
    }

    #[Test]
    public function store_purchase_order_with_items(): void
    {
        $supplier = Supplier::factory()->create(['company_id' => $this->company->id]);
        $unit     = Unit::factory()->create(['company_id' => $this->company->id]);
        $product  = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $unit->id]);

        $response = $this->tenantPost('/quincaillerie/purchase-orders', [
            'supplier_id'   => $supplier->id,
            'expected_date' => now()->addDays(7)->toDateString(),
            'items'         => [
                ['product_id' => $product->id, 'unit_id' => $unit->id, 'quantity' => 10, 'unit_price' => 500],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('purchase_orders', ['company_id' => $this->company->id, 'total' => 5000]);
        $this->assertDatabaseHas('purchase_order_items', ['product_id' => $product->id, 'quantity' => 10]);
    }

    #[Test]
    public function show_purchase_order(): void
    {
        $supplier = Supplier::factory()->create(['company_id' => $this->company->id]);
        $po = PurchaseOrder::factory()->create(['company_id' => $this->company->id, 'supplier_id' => $supplier->id, 'user_id' => $this->owner->id]);

        $this->tenantGet('/quincaillerie/purchase-orders/' . $po->id)->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Quincaillerie/PurchaseOrders/Show'));
    }

    #[Test]
    public function update_status_to_sent(): void
    {
        $supplier = Supplier::factory()->create(['company_id' => $this->company->id]);
        $po = PurchaseOrder::factory()->create(['company_id' => $this->company->id, 'supplier_id' => $supplier->id, 'user_id' => $this->owner->id, 'status' => 'draft']);

        $this->tenantPatch('/quincaillerie/purchase-orders/' . $po->id . '/status', ['status' => 'sent'])
            ->assertRedirect();
        $this->assertDatabaseHas('purchase_orders', ['id' => $po->id, 'status' => 'sent']);
    }

    #[Test]
    public function receive_purchase_order_updates_stock(): void
    {
        $supplier = Supplier::factory()->create(['company_id' => $this->company->id]);
        $unit     = Unit::factory()->create(['company_id' => $this->company->id]);
        $product  = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $unit->id]);

        $po = PurchaseOrder::factory()->sent()->create(['company_id' => $this->company->id, 'supplier_id' => $supplier->id, 'user_id' => $this->owner->id]);
        $po->items()->create(['product_id' => $product->id, 'unit_id' => $unit->id, 'quantity' => 20, 'unit_price' => 500, 'total' => 10000, 'received_quantity' => 0]);

        $this->tenantPost('/quincaillerie/purchase-orders/' . $po->id . '/receive', [
            'items' => [
                ['purchase_order_item_id' => $po->items->first()->id, 'received_qty' => 15],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('purchase_order_items', ['id' => $po->items->first()->id, 'received_quantity' => 15]);
    }
}
