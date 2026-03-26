<?php

namespace Tests\Feature\App\Restaurant;

use App\Models\Company;
use App\Models\Dish;
use App\Models\RestaurantCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DishTest extends TestCase
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
    public function dishes_index_loads(): void
    {
        $response = $this->tenantGet('/restaurant/dishes');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Restaurant/Dishes/Index'));
    }

    #[Test]
    public function dishes_scoped_to_tenant(): void
    {
        $other = Company::create(['name' => 'Other', 'slug' => 'other', 'email' => 'o@o.sn', 'subscription_status' => 'active', 'is_active' => true]);
        $catOther = RestaurantCategory::factory()->create(['company_id' => $other->id]);
        $catOwn = RestaurantCategory::factory()->create(['company_id' => $this->company->id]);
        Dish::factory()->create(['company_id' => $other->id, 'restaurant_category_id' => $catOther->id]);
        Dish::factory()->create(['company_id' => $this->company->id, 'restaurant_category_id' => $catOwn->id]);
        $response = $this->tenantGet('/restaurant/dishes');
        $response->assertInertia(fn ($page) => $page->has('dishes.data', 1));
    }

    #[Test]
    public function create_dish_form_loads(): void
    {
        $response = $this->tenantGet('/restaurant/dishes/create');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Restaurant/Dishes/Form'));
    }

    #[Test]
    public function store_dish_with_valid_data(): void
    {
        $cat = RestaurantCategory::factory()->create(['company_id' => $this->company->id]);
        $response = $this->tenantPost('/restaurant/dishes', [
            'restaurant_category_id' => $cat->id,
            'name'                   => 'Thiéboudienne',
            'price'                  => 3500,
            'is_available'           => true,
            'available_morning'      => false,
            'available_noon'         => true,
            'available_evening'      => true,
            'is_additional'          => false,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('dishes', ['company_id' => $this->company->id, 'name' => 'Thiéboudienne', 'price' => 3500]);
    }

    #[Test]
    public function store_dish_validates_required_fields(): void
    {
        $response = $this->tenantPost('/restaurant/dishes', ['name' => '', 'price' => '']);
        $response->assertSessionHasErrors(['name', 'price', 'restaurant_category_id']);
    }

    #[Test]
    public function update_dish(): void
    {
        $cat = RestaurantCategory::factory()->create(['company_id' => $this->company->id]);
        $dish = Dish::factory()->create(['company_id' => $this->company->id, 'restaurant_category_id' => $cat->id, 'price' => 2000]);
        $response = $this->tenantPut('/restaurant/dishes/' . $dish->id, [
            'restaurant_category_id' => $cat->id,
            'name'                   => $dish->name,
            'price'                  => 4000,
            'is_available'           => true,
            'available_morning'      => false,
            'available_noon'         => true,
            'available_evening'      => false,
            'is_additional'          => false,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('dishes', ['id' => $dish->id, 'price' => 4000]);
    }

    #[Test]
    public function delete_dish_soft_deletes(): void
    {
        $cat = RestaurantCategory::factory()->create(['company_id' => $this->company->id]);
        $dish = Dish::factory()->create(['company_id' => $this->company->id, 'restaurant_category_id' => $cat->id]);
        $response = $this->tenantDelete('/restaurant/dishes/' . $dish->id);
        $response->assertRedirect();
        $this->assertSoftDeleted('dishes', ['id' => $dish->id]);
    }

    #[Test]
    public function dish_effective_price_returns_promo_when_active(): void
    {
        $cat = RestaurantCategory::factory()->create(['company_id' => $this->company->id]);
        $dish = Dish::factory()->withPromo()->create([
            'company_id'             => $this->company->id,
            'restaurant_category_id' => $cat->id,
            'price'                  => 5000,
        ]);
        $this->assertNotEquals(5000, $dish->effective_price);
    }
}
