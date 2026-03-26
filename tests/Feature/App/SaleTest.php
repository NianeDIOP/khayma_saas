<?php

namespace Tests\Feature\App;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Depot;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SaleTest extends TestCase
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
    private function tenantDelete(string $path) { return $this->actingAs($this->owner)->delete('/app' . $path . '?_tenant=test-co'); }

    #[Test]
    public function sales_index_loads(): void
    {
        $response = $this->tenantGet('/sales');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Sales/Index'));
    }

    #[Test]
    public function sales_scoped_to_tenant(): void
    {
        $other = Company::create(['name' => 'Other', 'slug' => 'other', 'email' => 'o@o.sn', 'subscription_status' => 'active', 'is_active' => true]);
        Sale::factory()->create(['company_id' => $other->id, 'user_id' => $this->owner->id]);
        Sale::factory()->create(['company_id' => $this->company->id, 'user_id' => $this->owner->id]);
        $response = $this->tenantGet('/sales');
        $response->assertInertia(fn ($page) => $page->has('sales.data', 1));
    }

    #[Test]
    public function create_sale_form_loads(): void
    {
        $response = $this->tenantGet('/sales/create');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Sales/Create'));
    }

    #[Test]
    public function store_sale_creates_sale_and_items(): void
    {
        $product = Product::factory()->create(['company_id' => $this->company->id, 'selling_price' => 500]);
        Depot::factory()->create(['company_id' => $this->company->id, 'is_default' => true]);

        $response = $this->tenantPost('/sales', [
            'type'            => 'counter',
            'discount_amount' => 0,
            'tax_amount'      => 0,
            'payment_method'  => 'cash',
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2, 'unit_price' => 500, 'discount' => 0],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sales', ['company_id' => $this->company->id, 'total' => 1000]);
        $this->assertDatabaseHas('sale_items', ['product_id' => $product->id, 'quantity' => 2]);
    }

    #[Test]
    public function store_sale_validates_items_required(): void
    {
        $response = $this->tenantPost('/sales', ['items' => []]);
        $response->assertSessionHasErrors(['items']);
    }

    #[Test]
    public function show_sale_displays_details(): void
    {
        $sale = Sale::factory()->create(['company_id' => $this->company->id, 'user_id' => $this->owner->id]);
        $response = $this->tenantGet('/sales/' . $sale->id);
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Sales/Show'));
    }

    #[Test]
    public function delete_sale_soft_deletes_and_cancels(): void
    {
        $sale = Sale::factory()->create(['company_id' => $this->company->id, 'user_id' => $this->owner->id]);
        $response = $this->tenantDelete('/sales/' . $sale->id);
        $response->assertRedirect();
        $this->assertSoftDeleted('sales', ['id' => $sale->id]);
    }

    #[Test]
    public function sales_require_authentication(): void
    {
        $response = $this->get('/app/sales?_tenant=test-co');
        $response->assertRedirect('/login');
    }
}
