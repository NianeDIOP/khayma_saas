<?php

namespace Tests\Feature\App\Quincaillerie;

use App\Models\Company;
use App\Models\Depot;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InventoryTest extends TestCase
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
    public function inventories_index_loads(): void
    {
        $this->tenantGet('/quincaillerie/inventories')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Quincaillerie/Inventories/Index'));
    }

    #[Test]
    public function inventories_create_loads(): void
    {
        Depot::factory()->create(['company_id' => $this->company->id]);
        $this->tenantGet('/quincaillerie/inventories/create')->assertOk();
    }

    #[Test]
    public function store_inventory_with_lines(): void
    {
        $depot   = Depot::factory()->create(['company_id' => $this->company->id]);
        $unit    = Unit::factory()->create(['company_id' => $this->company->id]);
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $unit->id]);

        $response = $this->tenantPost('/quincaillerie/inventories', [
            'depot_id' => $depot->id,
            'notes'    => 'Inventaire test',
            'lines'    => [
                ['product_id' => $product->id, 'system_quantity' => 10, 'physical_quantity' => 8],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('inventories', ['company_id' => $this->company->id, 'status' => 'in_progress']);
        $this->assertDatabaseHas('inventory_lines', ['product_id' => $product->id, 'gap' => -2]);
    }

    #[Test]
    public function show_inventory(): void
    {
        $depot = Depot::factory()->create(['company_id' => $this->company->id]);
        $inv = Inventory::factory()->create(['company_id' => $this->company->id, 'depot_id' => $depot->id, 'user_id' => $this->owner->id]);

        $this->tenantGet('/quincaillerie/inventories/' . $inv->id)->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Quincaillerie/Inventories/Show'));
    }

    #[Test]
    public function validate_inventory_adjusts_stock(): void
    {
        $depot   = Depot::factory()->create(['company_id' => $this->company->id]);
        $unit    = Unit::factory()->create(['company_id' => $this->company->id]);
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $unit->id]);
        $inv     = Inventory::factory()->create(['company_id' => $this->company->id, 'depot_id' => $depot->id, 'user_id' => $this->owner->id]);
        $inv->lines()->create(['product_id' => $product->id, 'system_quantity' => 10, 'physical_quantity' => 7, 'gap' => -3]);

        $this->tenantPost('/quincaillerie/inventories/' . $inv->id . '/validate')
            ->assertRedirect();

        $this->assertDatabaseHas('inventories', ['id' => $inv->id, 'status' => 'validated']);
    }
}
