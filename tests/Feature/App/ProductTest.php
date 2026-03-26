<?php

namespace Tests\Feature\App;

use App\Models\Category;
use App\Models\Company;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ProductTest extends TestCase
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
    private function tenantPut(string $path, array $data = []) { return $this->actingAs($this->owner)->put('/app' . $path . '?_tenant=test-co', $data); }
    private function tenantDelete(string $path) { return $this->actingAs($this->owner)->delete('/app' . $path . '?_tenant=test-co'); }

    #[Test]
    public function products_index_loads(): void
    {
        Product::factory()->create(['company_id' => $this->company->id]);
        $response = $this->tenantGet('/products');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Products/Index')->has('products.data', 1));
    }

    #[Test]
    public function products_index_filters_by_search(): void
    {
        Product::factory()->create(['company_id' => $this->company->id, 'name' => 'Coca Cola']);
        Product::factory()->create(['company_id' => $this->company->id, 'name' => 'Fanta']);
        $response = $this->actingAs($this->owner)->get('/app/products?_tenant=test-co&search=Coca');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->has('products.data', 1));
    }

    #[Test]
    public function products_scoped_to_tenant(): void
    {
        $other = Company::create(['name' => 'Other', 'slug' => 'other', 'email' => 'o@o.sn', 'subscription_status' => 'active', 'is_active' => true]);
        Product::factory()->create(['company_id' => $other->id]);
        Product::factory()->create(['company_id' => $this->company->id]);
        $response = $this->tenantGet('/products');
        $response->assertInertia(fn ($page) => $page->has('products.data', 1));
    }

    #[Test]
    public function create_product_form_loads(): void
    {
        $response = $this->tenantGet('/products/create');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Products/Form'));
    }

    #[Test]
    public function store_product_with_valid_data(): void
    {
        $response = $this->tenantPost('/products', [
            'name' => 'Coca 33cl', 'purchase_price' => 200, 'selling_price' => 350,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['company_id' => $this->company->id, 'name' => 'Coca 33cl']);
    }

    #[Test]
    public function store_product_validates_required(): void
    {
        $response = $this->tenantPost('/products', ['name' => '', 'purchase_price' => '', 'selling_price' => '']);
        $response->assertSessionHasErrors(['name', 'purchase_price', 'selling_price']);
    }

    #[Test]
    public function store_product_validates_barcode_unique_per_company(): void
    {
        Product::factory()->create(['company_id' => $this->company->id, 'barcode' => 'BC001']);
        $response = $this->tenantPost('/products', ['name' => 'Test', 'purchase_price' => 100, 'selling_price' => 200, 'barcode' => 'BC001']);
        $response->assertSessionHasErrors(['barcode']);
    }

    #[Test]
    public function show_product_displays_details(): void
    {
        $product = Product::factory()->create(['company_id' => $this->company->id, 'name' => 'Test Product']);
        $response = $this->tenantGet('/products/' . $product->id);
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Products/Show')->where('product.name', 'Test Product'));
    }

    #[Test]
    public function update_product(): void
    {
        $product = Product::factory()->create(['company_id' => $this->company->id, 'name' => 'Old']);
        $response = $this->tenantPut('/products/' . $product->id, ['name' => 'New', 'purchase_price' => 100, 'selling_price' => 200]);
        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'New']);
    }

    #[Test]
    public function delete_product_soft_deletes(): void
    {
        $product = Product::factory()->create(['company_id' => $this->company->id]);
        $response = $this->tenantDelete('/products/' . $product->id);
        $response->assertRedirect();
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }

    #[Test]
    public function products_require_authentication(): void
    {
        $response = $this->get('/app/products?_tenant=test-co');
        $response->assertRedirect('/login');
    }
}
