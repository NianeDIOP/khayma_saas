<?php

namespace Tests\Feature\App\Restaurant;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReportTest extends TestCase
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

    #[Test]
    public function reports_index_loads(): void
    {
        $response = $this->tenantGet('/restaurant/reports');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Restaurant/Reports/Index'));
    }

    #[Test]
    public function reports_index_with_date_filter(): void
    {
        $response = $this->actingAs($this->owner)->get('/app/restaurant/reports?_tenant=test-co&date_from=2025-01-01&date_to=2025-12-31');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('App/Restaurant/Reports/Index')
            ->has('stats')
            ->has('topDishes')
        );
    }

    #[Test]
    public function reports_require_authentication(): void
    {
        $response = $this->get('/app/restaurant/reports?_tenant=test-co');
        $response->assertRedirect('/login');
    }
}
