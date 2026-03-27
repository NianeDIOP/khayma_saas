<?php

namespace Tests\Feature\App\Boutique;

use App\Models\Company;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VariantTest extends TestCase
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
    public function variants_index_loads(): void
    {
        $this->tenantGet('/boutique/variants')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Boutique/Variants/Index'));
    }

    #[Test]
    public function variants_create_form_loads(): void
    {
        $this->tenantGet('/boutique/variants/create')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Boutique/Variants/Form'));
    }

    #[Test]
    public function store_variant(): void
    {
        $unit    = Unit::factory()->create(['company_id' => $this->company->id]);
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $unit->id]);

        $response = $this->tenantPost('/boutique/variants', [
            'product_id'     => $product->id,
            'name'           => 'Rouge XL',
            'sku'            => 'VAR-001',
            'barcode'        => '1234567890123',
            'price_override' => 1500,
            'attributes'     => ['couleur' => 'rouge', 'taille' => 'XL'],
            'is_active'      => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('product_variants', ['company_id' => $this->company->id, 'name' => 'Rouge XL', 'sku' => 'VAR-001']);
    }

    #[Test]
    public function store_variant_validates_name_required(): void
    {
        $this->tenantPost('/boutique/variants', ['name' => ''])
            ->assertSessionHasErrors(['name', 'product_id']);
    }

    #[Test]
    public function store_variant_validates_unique_sku(): void
    {
        $unit    = Unit::factory()->create(['company_id' => $this->company->id]);
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $unit->id]);
        ProductVariant::factory()->create(['company_id' => $this->company->id, 'product_id' => $product->id, 'sku' => 'SKU-DUP']);

        $this->tenantPost('/boutique/variants', [
            'product_id' => $product->id,
            'name'       => 'Test',
            'sku'        => 'SKU-DUP',
            'is_active'  => true,
        ])->assertSessionHasErrors(['sku']);
    }

    #[Test]
    public function edit_variant_loads(): void
    {
        $unit    = Unit::factory()->create(['company_id' => $this->company->id]);
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $unit->id]);
        $variant = ProductVariant::factory()->create(['company_id' => $this->company->id, 'product_id' => $product->id]);

        $this->tenantGet('/boutique/variants/' . $variant->id . '/edit')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Boutique/Variants/Form'));
    }

    #[Test]
    public function update_variant(): void
    {
        $unit    = Unit::factory()->create(['company_id' => $this->company->id]);
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $unit->id]);
        $variant = ProductVariant::factory()->create(['company_id' => $this->company->id, 'product_id' => $product->id]);

        $response = $this->tenantPut('/boutique/variants/' . $variant->id, [
            'product_id' => $product->id,
            'name'       => 'Updated Name',
            'sku'        => 'UPD-001',
            'is_active'  => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'name' => 'Updated Name']);
    }

    #[Test]
    public function destroy_variant(): void
    {
        $unit    = Unit::factory()->create(['company_id' => $this->company->id]);
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $unit->id]);
        $variant = ProductVariant::factory()->create(['company_id' => $this->company->id, 'product_id' => $product->id]);

        $this->tenantDelete('/boutique/variants/' . $variant->id)->assertRedirect();
        $this->assertDatabaseMissing('product_variants', ['id' => $variant->id]);
    }

    #[Test]
    public function index_filters_by_search(): void
    {
        $unit    = Unit::factory()->create(['company_id' => $this->company->id]);
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $unit->id]);
        ProductVariant::factory()->create(['company_id' => $this->company->id, 'product_id' => $product->id, 'name' => 'Bleu Small']);
        ProductVariant::factory()->create(['company_id' => $this->company->id, 'product_id' => $product->id, 'name' => 'Rouge Large']);

        $this->actingAs($this->owner)
            ->get('/app/boutique/variants?_tenant=test-co&search=Bleu')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->has('variants.data', 1));
    }
}
