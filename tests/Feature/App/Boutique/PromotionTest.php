<?php

namespace Tests\Feature\App\Boutique;

use App\Models\Company;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PromotionTest extends TestCase
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
    public function promotions_index_loads(): void
    {
        $this->tenantGet('/boutique/promotions')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Boutique/Promotions/Index'));
    }

    #[Test]
    public function promotions_create_form_loads(): void
    {
        $this->tenantGet('/boutique/promotions/create')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Boutique/Promotions/Form'));
    }

    #[Test]
    public function store_percentage_promotion(): void
    {
        $unit    = Unit::factory()->create(['company_id' => $this->company->id]);
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $unit->id]);

        $response = $this->tenantPost('/boutique/promotions', [
            'product_id' => $product->id,
            'name'       => 'Promo été',
            'type'       => 'percentage',
            'value'      => 15,
            'start_date' => now()->toDateString(),
            'end_date'   => now()->addDays(30)->toDateString(),
            'is_active'  => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('promotions', ['company_id' => $this->company->id, 'name' => 'Promo été', 'type' => 'percentage', 'value' => 15]);
    }

    #[Test]
    public function store_fixed_promotion(): void
    {
        $unit    = Unit::factory()->create(['company_id' => $this->company->id]);
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $unit->id]);

        $response = $this->tenantPost('/boutique/promotions', [
            'product_id' => $product->id,
            'name'       => 'Remise fixe',
            'type'       => 'fixed',
            'value'      => 500,
            'start_date' => now()->toDateString(),
            'end_date'   => now()->addDays(10)->toDateString(),
            'is_active'  => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('promotions', ['type' => 'fixed', 'value' => 500]);
    }

    #[Test]
    public function store_validates_required_fields(): void
    {
        $this->tenantPost('/boutique/promotions', [])
            ->assertSessionHasErrors(['product_id', 'name', 'type', 'value', 'start_date', 'end_date']);
    }

    #[Test]
    public function store_validates_end_date_after_start(): void
    {
        $unit    = Unit::factory()->create(['company_id' => $this->company->id]);
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $unit->id]);

        $this->tenantPost('/boutique/promotions', [
            'product_id' => $product->id,
            'name'       => 'Bad dates',
            'type'       => 'percentage',
            'value'      => 10,
            'start_date' => now()->addDays(10)->toDateString(),
            'end_date'   => now()->toDateString(),
            'is_active'  => true,
        ])->assertSessionHasErrors(['end_date']);
    }

    #[Test]
    public function edit_promotion_loads(): void
    {
        $unit    = Unit::factory()->create(['company_id' => $this->company->id]);
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $unit->id]);
        $promo   = Promotion::factory()->create(['company_id' => $this->company->id, 'product_id' => $product->id]);

        $this->tenantGet('/boutique/promotions/' . $promo->id . '/edit')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Boutique/Promotions/Form'));
    }

    #[Test]
    public function update_promotion(): void
    {
        $unit    = Unit::factory()->create(['company_id' => $this->company->id]);
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $unit->id]);
        $promo   = Promotion::factory()->create(['company_id' => $this->company->id, 'product_id' => $product->id]);

        $response = $this->tenantPut('/boutique/promotions/' . $promo->id, [
            'product_id' => $product->id,
            'name'       => 'Updated Promo',
            'type'       => 'fixed',
            'value'      => 1000,
            'start_date' => now()->toDateString(),
            'end_date'   => now()->addDays(15)->toDateString(),
            'is_active'  => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('promotions', ['id' => $promo->id, 'name' => 'Updated Promo', 'value' => 1000]);
    }

    #[Test]
    public function destroy_promotion(): void
    {
        $unit    = Unit::factory()->create(['company_id' => $this->company->id]);
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $unit->id]);
        $promo   = Promotion::factory()->create(['company_id' => $this->company->id, 'product_id' => $product->id]);

        $this->tenantDelete('/boutique/promotions/' . $promo->id)->assertRedirect();
        $this->assertDatabaseMissing('promotions', ['id' => $promo->id]);
    }

    #[Test]
    public function index_filters_active_only(): void
    {
        $unit    = Unit::factory()->create(['company_id' => $this->company->id]);
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $unit->id]);
        Promotion::factory()->create(['company_id' => $this->company->id, 'product_id' => $product->id, 'is_active' => true]);
        Promotion::factory()->expired()->create(['company_id' => $this->company->id, 'product_id' => $product->id]);

        $this->actingAs($this->owner)
            ->get('/app/boutique/promotions?_tenant=test-co&active_only=1')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->has('promotions.data', 1));
    }
}
