<?php

namespace Tests\Feature\App\Location;

use App\Models\Company;
use App\Models\RentalAsset;
use App\Models\RentalContract;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AssetTest extends TestCase
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

    private function tenantGet(string $path) { $sep = str_contains($path, '?') ? '&' : '?'; return $this->actingAs($this->owner)->get('/app' . $path . $sep . '_tenant=test-co'); }
    private function tenantPost(string $path, array $data = []) { $sep = str_contains($path, '?') ? '&' : '?'; return $this->actingAs($this->owner)->post('/app' . $path . $sep . '_tenant=test-co', $data); }
    private function tenantPut(string $path, array $data = []) { $sep = str_contains($path, '?') ? '&' : '?'; return $this->actingAs($this->owner)->put('/app' . $path . $sep . '_tenant=test-co', $data); }
    private function tenantDelete(string $path) { $sep = str_contains($path, '?') ? '&' : '?'; return $this->actingAs($this->owner)->delete('/app' . $path . $sep . '_tenant=test-co'); }

    #[Test]
    public function assets_index_loads(): void
    {
        $this->tenantGet('/location/assets')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Location/Assets/Index'));
    }

    #[Test]
    public function assets_index_filters_by_type(): void
    {
        RentalAsset::factory()->create(['company_id' => $this->company->id, 'type' => 'vehicle']);
        RentalAsset::factory()->create(['company_id' => $this->company->id, 'type' => 'real_estate']);

        $this->tenantGet('/location/assets?type=vehicle')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Location/Assets/Index')->has('assets.data', 1));
    }

    #[Test]
    public function assets_index_filters_by_status(): void
    {
        RentalAsset::factory()->create(['company_id' => $this->company->id, 'status' => 'available']);
        RentalAsset::factory()->create(['company_id' => $this->company->id, 'status' => 'rented']);

        $this->tenantGet('/location/assets?status=rented')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Location/Assets/Index')->has('assets.data', 1));
    }

    #[Test]
    public function assets_index_searches_by_name(): void
    {
        RentalAsset::factory()->create(['company_id' => $this->company->id, 'name' => 'Appartement F3']);
        RentalAsset::factory()->create(['company_id' => $this->company->id, 'name' => 'Camion Benne']);

        $this->tenantGet('/location/assets?search=Appartement')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Location/Assets/Index')->has('assets.data', 1));
    }

    #[Test]
    public function assets_create_form_loads(): void
    {
        $this->tenantGet('/location/assets/create')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Location/Assets/Form'));
    }

    #[Test]
    public function store_asset(): void
    {
        $response = $this->tenantPost('/location/assets', [
            'name'        => 'Appartement F3',
            'description' => 'Centre-ville',
            'type'        => 'real_estate',
            'daily_rate'  => null,
            'monthly_rate' => 150000,
            'status'      => 'available',
            'is_active'   => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('rental_assets', [
            'company_id' => $this->company->id,
            'name'       => 'Appartement F3',
            'type'       => 'real_estate',
        ]);
    }

    #[Test]
    public function store_asset_validates_required(): void
    {
        $this->tenantPost('/location/assets', ['name' => '', 'type' => ''])
            ->assertSessionHasErrors(['name', 'type', 'status']);
    }

    #[Test]
    public function store_asset_validates_type_enum(): void
    {
        $this->tenantPost('/location/assets', [
            'name' => 'Test', 'type' => 'invalid', 'status' => 'available',
        ])->assertSessionHasErrors(['type']);
    }

    #[Test]
    public function show_asset(): void
    {
        $asset = RentalAsset::factory()->create(['company_id' => $this->company->id]);

        $this->tenantGet("/location/assets/{$asset->id}")->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Location/Assets/Show')->has('asset'));
    }

    #[Test]
    public function edit_asset(): void
    {
        $asset = RentalAsset::factory()->create(['company_id' => $this->company->id]);

        $this->tenantGet("/location/assets/{$asset->id}/edit")->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Location/Assets/Form')->has('asset'));
    }

    #[Test]
    public function update_asset(): void
    {
        $asset = RentalAsset::factory()->create(['company_id' => $this->company->id, 'name' => 'Old Name']);

        $this->tenantPut("/location/assets/{$asset->id}", [
            'name'   => 'New Name',
            'type'   => 'vehicle',
            'status' => 'available',
            'is_active' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('rental_assets', ['id' => $asset->id, 'name' => 'New Name']);
    }

    #[Test]
    public function destroy_asset(): void
    {
        $asset = RentalAsset::factory()->create(['company_id' => $this->company->id]);

        $this->tenantDelete("/location/assets/{$asset->id}")->assertRedirect();
        $this->assertDatabaseMissing('rental_assets', ['id' => $asset->id]);
    }

    #[Test]
    public function cannot_destroy_asset_with_active_contract(): void
    {
        $asset = RentalAsset::factory()->create(['company_id' => $this->company->id]);
        $customer = \App\Models\Customer::factory()->create(['company_id' => $this->company->id]);

        RentalContract::factory()->create([
            'company_id'      => $this->company->id,
            'rental_asset_id' => $asset->id,
            'customer_id'     => $customer->id,
            'user_id'         => $this->owner->id,
            'status'          => 'active',
        ]);

        $this->tenantDelete("/location/assets/{$asset->id}")->assertRedirect();
        $this->assertDatabaseHas('rental_assets', ['id' => $asset->id]);
    }

    #[Test]
    public function store_asset_with_characteristics(): void
    {
        $this->tenantPost('/location/assets', [
            'name'            => 'Appartement Meublé',
            'type'            => 'real_estate',
            'status'          => 'available',
            'monthly_rate'    => 200000,
            'characteristics' => ['superficie' => '120 m²', 'chambres' => '3'],
            'is_active'       => true,
        ])->assertRedirect();

        $asset = RentalAsset::where('name', 'Appartement Meublé')->first();
        $this->assertNotNull($asset);
        $this->assertEquals('120 m²', $asset->characteristics['superficie']);
    }
}
