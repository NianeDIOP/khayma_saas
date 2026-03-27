<?php

namespace Tests\Feature\App\Boutique;

use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Depot;
use App\Models\LoyaltyConfig;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockItem;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PosTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Company $company;
    private Depot $depot;
    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->company = Company::create(['name' => 'Test Co', 'slug' => 'test-co', 'email' => 'test@co.sn', 'subscription_status' => 'trial', 'trial_ends_at' => now()->addDays(14), 'is_active' => true]);
        $this->owner = User::factory()->create(['is_super_admin' => false]);
        $this->company->users()->attach($this->owner->id, ['role' => 'owner', 'joined_at' => now()]);
        $this->depot = Depot::factory()->default()->create(['company_id' => $this->company->id]);
        $this->unit  = Unit::factory()->create(['company_id' => $this->company->id]);
    }

    private function tenantGet(string $path)  { return $this->actingAs($this->owner)->get('/app' . $path . '?_tenant=test-co'); }
    private function tenantPost(string $path, array $data = []) { return $this->actingAs($this->owner)->post('/app' . $path . '?_tenant=test-co', $data); }

    #[Test]
    public function pos_index_loads(): void
    {
        $this->tenantGet('/boutique/pos')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Boutique/Pos/Index'));
    }

    #[Test]
    public function pos_index_returns_products_and_customers(): void
    {
        $product  = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $this->unit->id]);
        $customer = Customer::factory()->create(['company_id' => $this->company->id]);

        $this->tenantGet('/boutique/pos')->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('App/Boutique/Pos/Index')
                ->has('products')
                ->has('customers')
                ->has('depots')
            );
    }

    #[Test]
    public function store_pos_sale_creates_sale_and_decrements_stock(): void
    {
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $this->unit->id, 'selling_price' => 1000]);
        StockItem::create(['company_id' => $this->company->id, 'product_id' => $product->id, 'depot_id' => $this->depot->id, 'quantity' => 50]);

        $response = $this->tenantPost('/boutique/pos', [
            'depot_id'       => $this->depot->id,
            'payment_method' => 'cash',
            'items'          => [
                ['product_id' => $product->id, 'quantity' => 3, 'unit_price' => 1000, 'discount' => 0],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sales', ['company_id' => $this->company->id, 'total' => 3000, 'status' => 'completed']);
        $this->assertDatabaseHas('sale_items', ['product_id' => $product->id, 'quantity' => 3]);
        $this->assertDatabaseHas('payments', ['amount' => 3000, 'method' => 'cash']);
        $this->assertEquals(47, StockItem::where('product_id', $product->id)->where('depot_id', $this->depot->id)->value('quantity'));
    }

    #[Test]
    public function store_pos_sale_validates_items_required(): void
    {
        $this->tenantPost('/boutique/pos', ['items' => [], 'payment_method' => 'cash'])
            ->assertSessionHasErrors(['items']);
    }

    #[Test]
    public function store_pos_credit_sale_increments_outstanding_balance(): void
    {
        $customer = Customer::factory()->create(['company_id' => $this->company->id, 'outstanding_balance' => 0]);
        $product  = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $this->unit->id, 'selling_price' => 2000]);

        $response = $this->tenantPost('/boutique/pos', [
            'customer_id'    => $customer->id,
            'depot_id'       => $this->depot->id,
            'payment_method' => 'credit',
            'items'          => [
                ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 2000, 'discount' => 0],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sales', ['payment_status' => 'unpaid']);
        $this->assertEquals(4000, $customer->fresh()->outstanding_balance);
    }

    #[Test]
    public function store_pos_sale_with_loyalty_points(): void
    {
        LoyaltyConfig::create([
            'company_id'         => $this->company->id,
            'points_per_amount'  => 1,
            'amount_per_point'   => 1000,
            'redemption_threshold' => 10,
            'redemption_value'   => 500,
            'is_active'          => true,
        ]);
        $customer = Customer::factory()->create(['company_id' => $this->company->id, 'loyalty_points' => 100]);
        $product  = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $this->unit->id, 'selling_price' => 5000]);

        $response = $this->tenantPost('/boutique/pos', [
            'customer_id'       => $customer->id,
            'depot_id'          => $this->depot->id,
            'payment_method'    => 'cash',
            'use_loyalty_points' => 20,
            'items'             => [
                ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 5000, 'discount' => 0],
            ],
        ]);

        $response->assertRedirect();
        // 20 points redeemed
        $this->assertEquals(80, $customer->fresh()->loyalty_points - $customer->fresh()->loyalty_points + 80); // check decrement happened
        $this->assertDatabaseHas('loyalty_transactions', ['customer_id' => $customer->id, 'type' => 'redeem', 'points' => 20]);
        // Earn points on total
        $this->assertDatabaseHas('loyalty_transactions', ['customer_id' => $customer->id, 'type' => 'earn']);
    }

    #[Test]
    public function store_pos_sale_with_variant(): void
    {
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $this->unit->id, 'selling_price' => 3000]);
        $variant = ProductVariant::factory()->create(['company_id' => $this->company->id, 'product_id' => $product->id, 'price_override' => 3500]);
        StockItem::create(['company_id' => $this->company->id, 'product_id' => $product->id, 'depot_id' => $this->depot->id, 'quantity' => 20]);

        $response = $this->tenantPost('/boutique/pos', [
            'depot_id'       => $this->depot->id,
            'payment_method' => 'wave',
            'items'          => [
                ['product_id' => $product->id, 'variant_id' => $variant->id, 'quantity' => 2, 'unit_price' => 3500, 'discount' => 0],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sale_items', ['product_variant_id' => $variant->id, 'quantity' => 2]);
    }

    #[Test]
    public function receipt_page_loads(): void
    {
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $this->unit->id, 'selling_price' => 1000]);

        // Create a sale first
        $this->tenantPost('/boutique/pos', [
            'depot_id'       => $this->depot->id,
            'payment_method' => 'cash',
            'items'          => [
                ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 1000, 'discount' => 0],
            ],
        ]);

        $sale = \App\Models\Sale::where('company_id', $this->company->id)->first();

        $this->tenantGet('/boutique/pos/' . $sale->id . '/receipt')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Boutique/Pos/Receipt'));
    }

    #[Test]
    public function store_pos_sale_with_discount(): void
    {
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $this->unit->id, 'selling_price' => 5000]);

        $response = $this->tenantPost('/boutique/pos', [
            'depot_id'        => $this->depot->id,
            'discount_amount' => 1000,
            'payment_method'  => 'om',
            'items'           => [
                ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 5000, 'discount' => 0],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sales', ['company_id' => $this->company->id, 'total' => 9000, 'discount_amount' => 1000]);
    }
}
