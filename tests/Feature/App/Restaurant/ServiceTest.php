<?php

namespace Tests\Feature\App\Restaurant;

use App\Models\Company;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ServiceTest extends TestCase
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
    public function services_index_loads(): void
    {
        $response = $this->tenantGet('/restaurant/services');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Restaurant/Services/Index'));
    }

    #[Test]
    public function store_service_with_valid_data(): void
    {
        $response = $this->tenantPost('/restaurant/services', [
            'name'       => 'Midi',
            'start_time' => '12:00',
            'end_time'   => '15:00',
            'is_active'  => true,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('services', ['company_id' => $this->company->id, 'name' => 'Midi']);
    }

    #[Test]
    public function store_service_validates_required_fields(): void
    {
        $response = $this->tenantPost('/restaurant/services', ['name' => '', 'start_time' => '', 'end_time' => '']);
        $response->assertSessionHasErrors(['name', 'start_time', 'end_time']);
    }

    #[Test]
    public function update_service(): void
    {
        $service = Service::factory()->create(['company_id' => $this->company->id, 'name' => 'Matin']);
        $response = $this->tenantPut('/restaurant/services/' . $service->id, [
            'name' => 'Matin modifié', 'start_time' => '07:00', 'end_time' => '11:00', 'is_active' => true,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('services', ['id' => $service->id, 'name' => 'Matin modifié']);
    }

    #[Test]
    public function delete_service(): void
    {
        $service = Service::factory()->create(['company_id' => $this->company->id]);
        $response = $this->tenantDelete('/restaurant/services/' . $service->id);
        $response->assertRedirect();
        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }
}
