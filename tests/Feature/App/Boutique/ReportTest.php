<?php

namespace Tests\Feature\App\Boutique;

use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Depot;
use App\Models\LoyaltyConfig;
use App\Models\LoyaltyTransaction;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockItem;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReportTest extends TestCase
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

    private function tenantGet(string $path) { return $this->actingAs($this->owner)->get('/app' . $path . '?_tenant=test-co'); }

    #[Test]
    public function reports_index_loads(): void
    {
        $this->tenantGet('/boutique/reports')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Boutique/Reports/Index'));
    }

    #[Test]
    public function reports_return_sales_kpis(): void
    {
        $category = Category::create(['company_id' => $this->company->id, 'name' => 'Cat 1', 'module' => 'general']);
        $product  = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $this->unit->id, 'category_id' => $category->id, 'selling_price' => 2000, 'purchase_price' => 1000]);

        $sale = Sale::create([
            'company_id'      => $this->company->id,
            'user_id'         => $this->owner->id,
            'reference'       => 'RPT-001',
            'type'            => 'counter',
            'status'          => 'completed',
            'subtotal'        => 4000,
            'discount_amount' => 0,
            'tax_amount'      => 0,
            'total'           => 4000,
            'payment_status'  => 'paid',
        ]);

        SaleItem::create(['sale_id' => $sale->id, 'company_id' => $this->company->id, 'product_id' => $product->id, 'quantity' => 2, 'unit_price' => 2000, 'discount' => 0, 'total' => 4000]);
        Payment::create(['company_id' => $this->company->id, 'sale_id' => $sale->id, 'amount' => 4000, 'method' => 'cash']);

        $this->tenantGet('/boutique/reports')->assertOk()
            ->assertInertia(fn ($p) => $p
                ->component('App/Boutique/Reports/Index')
                ->where('totalOrders', 1)
            );
    }

    #[Test]
    public function reports_filter_by_date_range(): void
    {
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $this->unit->id, 'selling_price' => 1000, 'purchase_price' => 500]);

        // Sale within range
        $sale1 = Sale::create([
            'company_id' => $this->company->id, 'user_id' => $this->owner->id, 'reference' => 'RPT-R1',
            'type' => 'counter', 'status' => 'completed', 'subtotal' => 1000, 'discount_amount' => 0, 'tax_amount' => 0,
            'total' => 1000, 'payment_status' => 'paid', 'created_at' => now(),
        ]);

        // Sale outside range
        $sale2 = Sale::create([
            'company_id' => $this->company->id, 'user_id' => $this->owner->id, 'reference' => 'RPT-R2',
            'type' => 'counter', 'status' => 'completed', 'subtotal' => 5000, 'discount_amount' => 0, 'tax_amount' => 0,
            'total' => 5000, 'payment_status' => 'paid',
        ]);
        // Force created_at to 3 months ago
        Sale::where('id', $sale2->id)->update(['created_at' => now()->subMonths(3)]);

        $this->actingAs($this->owner)
            ->get('/app/boutique/reports?_tenant=test-co&start_date=' . now()->startOfMonth()->toDateString() . '&end_date=' . now()->toDateString())
            ->assertOk()
            ->assertInertia(fn ($p) => $p->where('totalOrders', 1));
    }

    #[Test]
    public function reports_include_loyalty_stats(): void
    {
        $customer = Customer::factory()->create(['company_id' => $this->company->id, 'loyalty_points' => 100]);
        LoyaltyConfig::create(['company_id' => $this->company->id, 'points_per_amount' => 1, 'amount_per_point' => 1000, 'redemption_threshold' => 10, 'redemption_value' => 500, 'is_active' => true]);
        LoyaltyTransaction::create(['company_id' => $this->company->id, 'customer_id' => $customer->id, 'type' => 'earn', 'points' => 50, 'description' => 'test']);
        LoyaltyTransaction::create(['company_id' => $this->company->id, 'customer_id' => $customer->id, 'type' => 'redeem', 'points' => 20, 'monetary_value' => 1000, 'description' => 'test']);

        $this->tenantGet('/boutique/reports')->assertOk()
            ->assertInertia(fn ($p) => $p
                ->where('loyaltyEarned', 50)
                ->where('loyaltyRedeemed', 20)
            );
    }

    #[Test]
    public function reports_include_stock_alerts(): void
    {
        $product = Product::factory()->create([
            'company_id'      => $this->company->id,
            'unit_id'         => $this->unit->id,
            'min_stock_alert' => 10,
        ]);
        StockItem::create(['company_id' => $this->company->id, 'product_id' => $product->id, 'depot_id' => $this->depot->id, 'quantity' => 5]);

        $this->tenantGet('/boutique/reports')->assertOk()
            ->assertInertia(fn ($p) => $p->has('stockAlerts', 1));
    }

    #[Test]
    public function reports_include_customer_debts(): void
    {
        Customer::factory()->create(['company_id' => $this->company->id, 'outstanding_balance' => 5000]);
        Customer::factory()->create(['company_id' => $this->company->id, 'outstanding_balance' => 0]);

        $this->tenantGet('/boutique/reports')->assertOk()
            ->assertInertia(fn ($p) => $p->has('customerDebts', 1));
    }

    #[Test]
    public function reports_include_stock_by_depot(): void
    {
        $this->tenantGet('/boutique/reports')->assertOk()
            ->assertInertia(fn ($p) => $p->has('stockByDepot'));
    }
}
