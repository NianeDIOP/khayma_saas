<?php

namespace Tests\Feature\App;

use App\Models\Company;
use App\Models\Depot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DepotTest extends TestCase
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
    public function depots_index_loads(): void
    {
        Depot::factory()->create(['company_id' => $this->company->id]);
        $response = $this->tenantGet('/depots');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Depots/Index')->has('depots.data', 1));
    }

    #[Test]
    public function depots_scoped_to_tenant(): void
    {
        $other = Company::create(['name' => 'Other', 'slug' => 'other', 'email' => 'o@o.sn', 'subscription_status' => 'active', 'is_active' => true]);
        Depot::factory()->create(['company_id' => $other->id]);
        Depot::factory()->create(['company_id' => $this->company->id]);
        $response = $this->tenantGet('/depots');
        $response->assertInertia(fn ($page) => $page->has('depots.data', 1));
    }

    #[Test]
    public function store_depot_with_valid_data(): void
    {
        $response = $this->tenantPost('/depots', ['name' => 'Magasin Central', 'address' => 'Dakar']);
        $response->assertRedirect();
        $this->assertDatabaseHas('depots', ['company_id' => $this->company->id, 'name' => 'Magasin Central']);
    }

    #[Test]
    public function store_depot_validates_required_name(): void
    {
        $response = $this->tenantPost('/depots', ['name' => '']);
        $response->assertSessionHasErrors(['name']);
    }

    #[Test]
    public function store_depot_sets_default_resets_others(): void
    {
        $old = Depot::factory()->create(['company_id' => $this->company->id, 'is_default' => true]);
        $response = $this->tenantPost('/depots', ['name' => 'New Default', 'is_default' => true]);
        $response->assertRedirect();
        $this->assertDatabaseHas('depots', ['id' => $old->id, 'is_default' => false]);
        $this->assertDatabaseHas('depots', ['name' => 'New Default', 'is_default' => true]);
    }

    #[Test]
    public function update_depot(): void
    {
        $depot = Depot::factory()->create(['company_id' => $this->company->id, 'name' => 'Old']);
        $response = $this->tenantPut('/depots/' . $depot->id, ['name' => 'New']);
        $response->assertRedirect();
        $this->assertDatabaseHas('depots', ['id' => $depot->id, 'name' => 'New']);
    }

    #[Test]
    public function delete_depot_soft_deletes(): void
    {
        $depot = Depot::factory()->create(['company_id' => $this->company->id]);
        $response = $this->tenantDelete('/depots/' . $depot->id);
        $response->assertRedirect();
        $this->assertSoftDeleted('depots', ['id' => $depot->id]);
    }

    #[Test]
    public function depots_require_authentication(): void
    {
        $response = $this->get('/app/depots?_tenant=test-co');
        $response->assertRedirect('/login');
    }
}
