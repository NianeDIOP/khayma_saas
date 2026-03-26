<?php

namespace Tests\Feature\App\Restaurant;

use App\Models\CashSession;
use App\Models\Company;
use App\Models\Dish;
use App\Models\Order;
use App\Models\RestaurantCategory;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderTest extends TestCase
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
    public function orders_index_loads(): void
    {
        $response = $this->tenantGet('/restaurant/orders');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Restaurant/Orders/Index'));
    }

    #[Test]
    public function create_order_form_loads(): void
    {
        $response = $this->tenantGet('/restaurant/orders/create');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Restaurant/Orders/POS'));
    }

    #[Test]
    public function store_order_creates_order_and_items(): void
    {
        $cat  = RestaurantCategory::factory()->create(['company_id' => $this->company->id]);
        $dish = Dish::factory()->create(['company_id' => $this->company->id, 'restaurant_category_id' => $cat->id, 'price' => 3000]);

        $response = $this->tenantPost('/restaurant/orders', [
            'type'            => 'table',
            'table_number'    => '5',
            'payment_method'  => 'cash',
            'payment_status'  => 'paid',
            'discount_amount' => 0,
            'items'           => [
                ['dish_id' => $dish->id, 'quantity' => 2],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'company_id' => $this->company->id,
            'type'       => 'table',
            'total'      => 6000,
        ]);
        $this->assertDatabaseHas('order_items', [
            'dish_id'    => $dish->id,
            'quantity'   => 2,
            'unit_price' => 3000,
            'total'      => 6000,
        ]);
    }

    #[Test]
    public function store_order_validates_items_required(): void
    {
        $response = $this->tenantPost('/restaurant/orders', [
            'type'           => 'table',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'items'          => [],
        ]);
        $response->assertSessionHasErrors(['items']);
    }

    #[Test]
    public function show_order_displays_details(): void
    {
        $order = Order::factory()->create(['company_id' => $this->company->id, 'user_id' => $this->owner->id]);
        $response = $this->tenantGet('/restaurant/orders/' . $order->id);
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Restaurant/Orders/Show'));
    }

    #[Test]
    public function cancel_order_with_reason(): void
    {
        $order = Order::factory()->create(['company_id' => $this->company->id, 'user_id' => $this->owner->id, 'status' => 'completed']);
        $response = $this->tenantPost('/restaurant/orders/' . $order->id . '/cancel', [
            'cancel_reason' => 'Client parti',
        ]);
        $response->assertRedirect();
        $order->refresh();
        $this->assertEquals('cancelled', $order->status);
        $this->assertEquals('Client parti', $order->cancel_reason);
    }

    #[Test]
    public function orders_require_authentication(): void
    {
        $response = $this->get('/app/restaurant/orders?_tenant=test-co');
        $response->assertRedirect('/login');
    }
}
