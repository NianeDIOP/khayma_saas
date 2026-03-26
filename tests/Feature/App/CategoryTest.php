<?php

namespace Tests\Feature\App;

use App\Models\Category;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryTest extends TestCase
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

    private function tenantGet(string $path) { return $this->actingAs($this->owner)->get('/app' . $path . '?_tenant=test-co'); }
    private function tenantPost(string $path, array $data = []) { return $this->actingAs($this->owner)->post('/app' . $path . '?_tenant=test-co', $data); }
    private function tenantPut(string $path, array $data = []) { return $this->actingAs($this->owner)->put('/app' . $path . '?_tenant=test-co', $data); }
    private function tenantDelete(string $path) { return $this->actingAs($this->owner)->delete('/app' . $path . '?_tenant=test-co'); }

    #[Test]
    public function categories_index_loads(): void
    {
        Category::factory()->create(['company_id' => $this->company->id, 'name' => 'Cat A']);
        Category::factory()->create(['company_id' => $this->company->id, 'name' => 'Cat B']);

        $response = $this->tenantGet('/categories');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Categories/Index')->has('categories.data', 2));
    }

    #[Test]
    public function categories_index_filters_by_search(): void
    {
        Category::factory()->create(['company_id' => $this->company->id, 'name' => 'Alimentaire']);
        Category::factory()->create(['company_id' => $this->company->id, 'name' => 'Électronique']);

        $response = $this->actingAs($this->owner)->get('/app/categories?_tenant=test-co&search=Aliment');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->has('categories.data', 1));
    }

    #[Test]
    public function categories_scoped_to_tenant(): void
    {
        $otherCompany = Company::create(['name' => 'Other', 'slug' => 'other', 'email' => 'o@o.sn', 'subscription_status' => 'active', 'is_active' => true]);
        Category::factory()->create(['company_id' => $otherCompany->id]);
        Category::factory()->create(['company_id' => $this->company->id]);

        $response = $this->tenantGet('/categories');
        $response->assertInertia(fn ($page) => $page->has('categories.data', 1));
    }

    #[Test]
    public function create_category_form_loads(): void
    {
        $response = $this->tenantGet('/categories/create');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Categories/Form'));
    }

    #[Test]
    public function store_category_with_valid_data(): void
    {
        $response = $this->tenantPost('/categories', ['name' => 'Boissons', 'module' => 'stock']);
        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['company_id' => $this->company->id, 'name' => 'Boissons', 'module' => 'stock']);
    }

    #[Test]
    public function store_category_validates_required_name(): void
    {
        $response = $this->tenantPost('/categories', ['name' => '']);
        $response->assertSessionHasErrors(['name']);
    }

    #[Test]
    public function store_category_validates_unique_name_per_company_module(): void
    {
        Category::factory()->create(['company_id' => $this->company->id, 'name' => 'Boissons', 'module' => 'general']);
        $response = $this->tenantPost('/categories', ['name' => 'Boissons', 'module' => 'general']);
        $response->assertSessionHasErrors(['name']);
    }

    #[Test]
    public function edit_category_form_loads(): void
    {
        $cat = Category::factory()->create(['company_id' => $this->company->id]);
        $response = $this->tenantGet('/categories/' . $cat->id . '/edit');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Categories/Form')->has('category'));
    }

    #[Test]
    public function update_category(): void
    {
        $cat = Category::factory()->create(['company_id' => $this->company->id, 'name' => 'Old']);
        $response = $this->tenantPut('/categories/' . $cat->id, ['name' => 'New', 'module' => 'general']);
        $response->assertRedirect();
        $this->assertDatabaseHas('categories', ['id' => $cat->id, 'name' => 'New']);
    }

    #[Test]
    public function delete_category_soft_deletes(): void
    {
        $cat = Category::factory()->create(['company_id' => $this->company->id]);
        $response = $this->tenantDelete('/categories/' . $cat->id);
        $response->assertRedirect();
        $this->assertSoftDeleted('categories', ['id' => $cat->id]);
    }

    #[Test]
    public function categories_require_authentication(): void
    {
        $response = $this->get('/app/categories?_tenant=test-co');
        $response->assertRedirect('/login');
    }
}
