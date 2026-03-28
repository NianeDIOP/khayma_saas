<?php

namespace Tests\Feature\App;

use App\Models\Company;
use App\Models\Depot;
use App\Models\Product;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\User;
use App\Mail\LowStockAlertMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StockTest extends TestCase
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
    public function stock_index_loads(): void
    {
        $response = $this->tenantGet('/stock');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Stock/Index'));
    }

    #[Test]
    public function stock_movements_loads(): void
    {
        $response = $this->tenantGet('/stock/movements');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Stock/Movements'));
    }

    #[Test]
    public function create_movement_form_loads(): void
    {
        $response = $this->tenantGet('/stock/movements/create');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Stock/MovementForm'));
    }

    #[Test]
    public function store_movement_in_increments_stock(): void
    {
        $product = Product::factory()->create(['company_id' => $this->company->id]);
        $depot = Depot::factory()->create(['company_id' => $this->company->id]);

        $response = $this->tenantPost('/stock/movements', [
            'product_id' => $product->id,
            'depot_id'   => $depot->id,
            'type'       => 'in',
            'quantity'   => 50,
            'unit_cost'  => 100,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('stock_items', [
            'product_id' => $product->id,
            'depot_id'   => $depot->id,
            'quantity'    => 50,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type'       => 'in',
            'quantity'    => 50,
        ]);
    }

    #[Test]
    public function store_movement_out_decrements_stock(): void
    {
        $product = Product::factory()->create(['company_id' => $this->company->id]);
        $depot = Depot::factory()->create(['company_id' => $this->company->id]);
        StockItem::create([
            'company_id' => $this->company->id,
            'product_id' => $product->id,
            'depot_id'   => $depot->id,
            'quantity'   => 100,
        ]);

        $response = $this->tenantPost('/stock/movements', [
            'product_id' => $product->id,
            'depot_id'   => $depot->id,
            'type'       => 'out',
            'quantity'   => 30,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('stock_items', [
            'product_id' => $product->id,
            'depot_id'   => $depot->id,
            'quantity'    => 70,
        ]);
    }

    #[Test]
    public function low_stock_alert_email_is_queued_for_owner(): void
    {
        Mail::fake();

        $product = Product::factory()->create([
            'company_id'       => $this->company->id,
            'min_stock_alert'  => 10,
        ]);
        $depot = Depot::factory()->create(['company_id' => $this->company->id]);

        StockItem::create([
            'company_id' => $this->company->id,
            'product_id' => $product->id,
            'depot_id'   => $depot->id,
            'quantity'   => 20,
        ]);

        $response = $this->tenantPost('/stock/movements', [
            'product_id' => $product->id,
            'depot_id'   => $depot->id,
            'type'       => 'out',
            'quantity'   => 12,
        ]);

        $response->assertRedirect();

        Mail::assertQueued(LowStockAlertMail::class, fn (LowStockAlertMail $mail) =>
            $mail->companyName === $this->company->name
        );
    }

    #[Test]
    public function store_movement_validates_required_fields(): void
    {
        $response = $this->tenantPost('/stock/movements', []);
        $response->assertSessionHasErrors(['product_id', 'depot_id', 'type', 'quantity']);
    }

    #[Test]
    public function stock_requires_authentication(): void
    {
        $response = $this->get('/app/stock?_tenant=test-co');
        $response->assertRedirect('/login');
    }
}
