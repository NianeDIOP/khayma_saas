<?php

namespace Tests\Feature\App\Quincaillerie;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quote;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QuoteTest extends TestCase
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
    private function tenantPatch(string $path, array $data = []) { return $this->actingAs($this->owner)->patch('/app' . $path . '?_tenant=test-co', $data); }

    #[Test]
    public function quotes_index_loads(): void
    {
        $this->tenantGet('/quincaillerie/quotes')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Quincaillerie/Quotes/Index'));
    }

    #[Test]
    public function quotes_create_form_loads(): void
    {
        $this->tenantGet('/quincaillerie/quotes/create')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Quincaillerie/Quotes/Form'));
    }

    #[Test]
    public function store_quote_creates_with_items(): void
    {
        $unit    = Unit::factory()->create(['company_id' => $this->company->id]);
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $unit->id]);

        $response = $this->tenantPost('/quincaillerie/quotes', [
            'discount_amount' => 0,
            'valid_until'     => now()->addDays(30)->toDateString(),
            'items'           => [
                ['product_id' => $product->id, 'unit_id' => $unit->id, 'quantity' => 3, 'unit_price' => 1000, 'discount' => 0],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('quotes', ['company_id' => $this->company->id, 'total' => 3000]);
        $this->assertDatabaseHas('quote_items', ['product_id' => $product->id, 'quantity' => 3, 'total' => 3000]);
    }

    #[Test]
    public function store_quote_validates_items_required(): void
    {
        $this->tenantPost('/quincaillerie/quotes', ['items' => []])
            ->assertSessionHasErrors(['items']);
    }

    #[Test]
    public function show_quote(): void
    {
        $quote = Quote::factory()->create(['company_id' => $this->company->id, 'user_id' => $this->owner->id]);
        $this->tenantGet('/quincaillerie/quotes/' . $quote->id)->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Quincaillerie/Quotes/Show'));
    }

    #[Test]
    public function update_status_from_draft_to_sent(): void
    {
        $quote = Quote::factory()->create(['company_id' => $this->company->id, 'user_id' => $this->owner->id, 'status' => 'draft']);
        $this->tenantPatch('/quincaillerie/quotes/' . $quote->id . '/status', ['status' => 'sent'])
            ->assertRedirect();
        $this->assertDatabaseHas('quotes', ['id' => $quote->id, 'status' => 'sent']);
    }

    #[Test]
    public function convert_accepted_quote_to_sale(): void
    {
        $unit    = Unit::factory()->create(['company_id' => $this->company->id]);
        $product = Product::factory()->create(['company_id' => $this->company->id, 'unit_id' => $unit->id]);

        $quote = Quote::factory()->accepted()->create(['company_id' => $this->company->id, 'user_id' => $this->owner->id, 'subtotal' => 5000, 'total' => 5000]);
        $quote->items()->create(['product_id' => $product->id, 'unit_id' => $unit->id, 'quantity' => 5, 'unit_price' => 1000, 'discount' => 0, 'total' => 5000]);

        $this->tenantPost('/quincaillerie/quotes/' . $quote->id . '/convert')
            ->assertRedirect();

        $this->assertDatabaseHas('quotes', ['id' => $quote->id, 'status' => 'converted']);
        $this->assertDatabaseHas('sales', ['company_id' => $this->company->id, 'total' => 5000]);
    }

    #[Test]
    public function destroy_draft_quote(): void
    {
        $quote = Quote::factory()->create(['company_id' => $this->company->id, 'user_id' => $this->owner->id, 'status' => 'draft']);
        $this->actingAs($this->owner)->delete('/app/quincaillerie/quotes/' . $quote->id . '?_tenant=test-co')
            ->assertRedirect();
        $this->assertSoftDeleted('quotes', ['id' => $quote->id]);
    }
}
