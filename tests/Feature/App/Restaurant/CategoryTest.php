<?php

namespace Tests\Feature\App\Restaurant;

use App\Models\Company;
use App\Models\RestaurantCategory;
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
        $this->company = Company::create(['name' => 'Test Co', 'slug' => 'test-co', 'email' => 'test@co.sn', 'subscription_status' => 'trial', 'trial_ends_at' => now()->addDays(14), 'is_active' => true]);
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
        $response = $this->tenantGet('/restaurant/categories');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Restaurant/Categories/Index'));
    }

    #[Test]
    public function categories_scoped_to_tenant(): void
    {
        $other = Company::create(['name' => 'Other', 'slug' => 'other', 'email' => 'o@o.sn', 'subscription_status' => 'active', 'is_active' => true]);
        RestaurantCategory::factory()->create(['company_id' => $other->id]);
        RestaurantCategory::factory()->create(['company_id' => $this->company->id]);
        $response = $this->tenantGet('/restaurant/categories');
        $response->assertInertia(fn ($page) => $page->has('categories', 1));
    }

    #[Test]
    public function create_category_form_loads(): void
    {
        $response = $this->tenantGet('/restaurant/categories/create');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Restaurant/Categories/Form'));
    }

    #[Test]
    public function store_category_with_valid_data(): void
    {
        $response = $this->tenantPost('/restaurant/categories', [
            'name'       => 'Entrées',
            'sort_order' => 1,
            'is_active'  => true,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('restaurant_categories', ['company_id' => $this->company->id, 'name' => 'Entrées']);
    }

    #[Test]
    public function store_category_validates_name_required(): void
    {
        $response = $this->tenantPost('/restaurant/categories', ['name' => '']);
        $response->assertSessionHasErrors(['name']);
    }

    #[Test]
    public function update_category(): void
    {
        $cat = RestaurantCategory::factory()->create(['company_id' => $this->company->id, 'name' => 'Old']);
        $response = $this->tenantPut('/restaurant/categories/' . $cat->id, ['name' => 'New', 'sort_order' => 5, 'is_active' => true]);
        $response->assertRedirect();
        $this->assertDatabaseHas('restaurant_categories', ['id' => $cat->id, 'name' => 'New']);
    }

    #[Test]
    public function delete_category(): void
    {
        $cat = RestaurantCategory::factory()->create(['company_id' => $this->company->id]);
        $response = $this->tenantDelete('/restaurant/categories/' . $cat->id);
        $response->assertRedirect();
        $this->assertSoftDeleted('restaurant_categories', ['id' => $cat->id]);
    }

    #[Test]
    public function categories_require_authentication(): void
    {
        $response = $this->get('/app/restaurant/categories?_tenant=test-co');
        $response->assertRedirect('/login');
    }
}
