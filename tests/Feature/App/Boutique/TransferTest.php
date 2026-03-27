<?php

namespace Tests\Feature\App\Boutique;

use App\Models\Company;
use App\Models\Depot;
use App\Models\DepotTransfer;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockItem;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TransferTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Company $company;
    private Depot $depotA;
    private Depot $depotB;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->company = Company::create(['name' => 'Test Co', 'slug' => 'test-co', 'email' => 'test@co.sn', 'subscription_status' => 'trial', 'trial_ends_at' => now()->addDays(14), 'is_active' => true]);
        $this->owner = User::factory()->create(['is_super_admin' => false]);
        $this->company->users()->attach($this->owner->id, ['role' => 'owner', 'joined_at' => now()]);
        $this->depotA = Depot::factory()->default()->create(['company_id' => $this->company->id, 'name' => 'Depot A']);
        $this->depotB = Depot::factory()->create(['company_id' => $this->company->id, 'name' => 'Depot B']);
        $this->unit   = Unit::factory()->create(['company_id' => $this->company->id]);
    }

    private function tenantGet(string $path)  { return $this->actingAs($this->owner)->get('/app' . $path . '?_tenant=test-co'); }
    private function tenantPost(string $path, array $data = []) { return $this->actingAs($this->owner)->post('/app' . $path . '?_tenant=test-co', $data); }

    #[Test]
    public function transfers_index_loads(): void
    {
        $this->tenantGet('/boutique/transfers')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Boutique/Transfers/Index'));
    }

    #[Test]
    public function transfers_create_form_loads(): void
    {
        $this->tenantGet('/boutique/transfers/create')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Boutique/Transfers/Form'));
    }

    #[Test]
    public function store_transfer_moves_stock(): void
    {
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $this->unit->id]);
        StockItem::create(['company_id' => $this->company->id, 'product_id' => $product->id, 'depot_id' => $this->depotA->id, 'quantity' => 100]);

        $response = $this->tenantPost('/boutique/transfers', [
            'from_depot_id' => $this->depotA->id,
            'to_depot_id'   => $this->depotB->id,
            'items'         => [
                ['product_id' => $product->id, 'quantity' => 30],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('depot_transfers', ['company_id' => $this->company->id, 'status' => 'completed']);
        $this->assertDatabaseHas('depot_transfer_items', ['product_id' => $product->id, 'quantity' => 30]);

        // Check stock moved
        $this->assertEquals(70, StockItem::where('product_id', $product->id)->where('depot_id', $this->depotA->id)->value('quantity'));
        $this->assertEquals(30, StockItem::where('product_id', $product->id)->where('depot_id', $this->depotB->id)->value('quantity'));

        // Check stock movements logged
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'depot_id' => $this->depotA->id, 'type' => 'transfer']);
        $this->assertDatabaseHas('stock_movements', ['product_id' => $product->id, 'depot_id' => $this->depotB->id, 'type' => 'transfer']);
    }

    #[Test]
    public function store_transfer_validates_different_depots(): void
    {
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $this->unit->id]);

        $this->tenantPost('/boutique/transfers', [
            'from_depot_id' => $this->depotA->id,
            'to_depot_id'   => $this->depotA->id,
            'items'         => [
                ['product_id' => $product->id, 'quantity' => 10],
            ],
        ])->assertSessionHasErrors(['to_depot_id']);
    }

    #[Test]
    public function store_transfer_validates_items_required(): void
    {
        $this->tenantPost('/boutique/transfers', [
            'from_depot_id' => $this->depotA->id,
            'to_depot_id'   => $this->depotB->id,
            'items'         => [],
        ])->assertSessionHasErrors(['items']);
    }

    #[Test]
    public function store_transfer_with_variant_stock(): void
    {
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $this->unit->id]);
        $variant = ProductVariant::factory()->create(['company_id' => $this->company->id, 'product_id' => $product->id]);
        StockItem::create(['company_id' => $this->company->id, 'product_id' => $product->id, 'depot_id' => $this->depotA->id, 'quantity' => 50]);
        \App\Models\VariantStockItem::create(['company_id' => $this->company->id, 'product_variant_id' => $variant->id, 'depot_id' => $this->depotA->id, 'quantity' => 20]);

        $response = $this->tenantPost('/boutique/transfers', [
            'from_depot_id' => $this->depotA->id,
            'to_depot_id'   => $this->depotB->id,
            'items'         => [
                ['product_id' => $product->id, 'variant_id' => $variant->id, 'quantity' => 10],
            ],
        ]);

        $response->assertRedirect();
        $this->assertEquals(10, \App\Models\VariantStockItem::where('product_variant_id', $variant->id)->where('depot_id', $this->depotA->id)->value('quantity'));
        $this->assertEquals(10, \App\Models\VariantStockItem::where('product_variant_id', $variant->id)->where('depot_id', $this->depotB->id)->value('quantity'));
    }

    #[Test]
    public function show_transfer(): void
    {
        $transfer = DepotTransfer::factory()->create([
            'company_id'   => $this->company->id,
            'from_depot_id' => $this->depotA->id,
            'to_depot_id'  => $this->depotB->id,
            'user_id'      => $this->owner->id,
        ]);

        $this->tenantGet('/boutique/transfers/' . $transfer->id)->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Boutique/Transfers/Show'));
    }

    #[Test]
    public function index_filters_by_status(): void
    {
        DepotTransfer::factory()->create(['company_id' => $this->company->id, 'from_depot_id' => $this->depotA->id, 'to_depot_id' => $this->depotB->id, 'user_id' => $this->owner->id, 'status' => 'completed']);
        DepotTransfer::factory()->create(['company_id' => $this->company->id, 'from_depot_id' => $this->depotA->id, 'to_depot_id' => $this->depotB->id, 'user_id' => $this->owner->id, 'status' => 'pending']);

        $this->actingAs($this->owner)
            ->get('/app/boutique/transfers?_tenant=test-co&status=pending')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->has('transfers.data', 1));
    }
}
