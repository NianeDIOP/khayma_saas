<?php

namespace Tests\Feature\App;

use App\Models\Company;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UnitTest extends TestCase
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
    public function units_index_loads(): void
    {
        Unit::factory()->create(['company_id' => $this->company->id]);
        $response = $this->tenantGet('/units');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Units/Index')->has('units.data', 1));
    }

    #[Test]
    public function units_scoped_to_tenant(): void
    {
        $other = Company::create(['name' => 'Other', 'slug' => 'other', 'email' => 'o@o.sn', 'subscription_status' => 'active', 'is_active' => true]);
        Unit::factory()->create(['company_id' => $other->id]);
        Unit::factory()->create(['company_id' => $this->company->id]);
        $response = $this->tenantGet('/units');
        $response->assertInertia(fn ($page) => $page->has('units.data', 1));
    }

    #[Test]
    public function store_unit_with_valid_data(): void
    {
        $response = $this->tenantPost('/units', ['name' => 'Kilogramme', 'symbol' => 'kg']);
        $response->assertRedirect();
        $this->assertDatabaseHas('units', ['company_id' => $this->company->id, 'name' => 'Kilogramme', 'symbol' => 'kg']);
    }

    #[Test]
    public function store_unit_validates_required(): void
    {
        $response = $this->tenantPost('/units', ['name' => '', 'symbol' => '']);
        $response->assertSessionHasErrors(['name', 'symbol']);
    }

    #[Test]
    public function store_unit_validates_unique_name(): void
    {
        Unit::factory()->create(['company_id' => $this->company->id, 'name' => 'Pièce']);
        $response = $this->tenantPost('/units', ['name' => 'Pièce', 'symbol' => 'pc']);
        $response->assertSessionHasErrors(['name']);
    }

    #[Test]
    public function update_unit(): void
    {
        $unit = Unit::factory()->create(['company_id' => $this->company->id, 'name' => 'Old']);
        $response = $this->tenantPut('/units/' . $unit->id, ['name' => 'New', 'symbol' => 'nw']);
        $response->assertRedirect();
        $this->assertDatabaseHas('units', ['id' => $unit->id, 'name' => 'New']);
    }

    #[Test]
    public function delete_unit_soft_deletes(): void
    {
        $unit = Unit::factory()->create(['company_id' => $this->company->id]);
        $response = $this->tenantDelete('/units/' . $unit->id);
        $response->assertRedirect();
        $this->assertSoftDeleted('units', ['id' => $unit->id]);
    }

    #[Test]
    public function units_require_authentication(): void
    {
        $response = $this->get('/app/units?_tenant=test-co');
        $response->assertRedirect('/login');
    }
}
