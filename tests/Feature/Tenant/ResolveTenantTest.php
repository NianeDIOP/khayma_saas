<?php

namespace Tests\Feature\Tenant;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ResolveTenantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // ── ResolveTenant ─────────────────────────────────────────────────────

    #[Test]
    public function it_resolves_tenant_from_query_param_in_local(): void
    {
        $user    = User::factory()->create();
        $company = Company::factory()->create([
            'slug'                => 'monrestaurant',
            'is_active'           => true,
            'subscription_status' => 'active',
        ]);

        $this->actingAs($user)
             ->get('/app?_tenant=monrestaurant')
             ->assertStatus(200);

        $this->assertTrue(App::bound('currentCompany'));
        $this->assertEquals($company->id, App::make('currentCompany')->id);
    }

    #[Test]
    public function it_returns_404_when_company_not_found(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get('/app?_tenant=inexistant')
             ->assertStatus(404);
    }

    #[Test]
    public function it_returns_404_when_company_is_inactive(): void
    {
        $user = User::factory()->create();
        Company::factory()->create([
            'slug'      => 'inactive-co',
            'is_active' => false,
        ]);

        $this->actingAs($user)
             ->get('/app?_tenant=inactive-co')
             ->assertStatus(404);
    }

    #[Test]
    public function it_returns_404_when_no_tenant_param(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get('/app')
             ->assertStatus(404);
    }

    // ── CheckSubscription ─────────────────────────────────────────────────

    #[Test]
    public function active_subscription_passes_through(): void
    {
        $user = User::factory()->create();
        Company::factory()->create([
            'slug'                => 'actif',
            'is_active'           => true,
            'subscription_status' => 'active',
        ]);

        $this->actingAs($user)
             ->get('/app?_tenant=actif')
             ->assertStatus(200);
    }

    #[Test]
    public function trial_within_validity_passes_through(): void
    {
        $user = User::factory()->create();
        Company::factory()->create([
            'slug'                => 'trial-ok',
            'is_active'           => true,
            'subscription_status' => 'trial',
            'trial_ends_at'       => now()->addDays(3),
        ]);

        $this->actingAs($user)
             ->get('/app?_tenant=trial-ok')
             ->assertStatus(200);
    }

    #[Test]
    public function expired_trial_is_blocked(): void
    {
        $user = User::factory()->create();
        Company::factory()->create([
            'slug'                => 'trial-expired',
            'is_active'           => true,
            'subscription_status' => 'trial',
            'trial_ends_at'       => now()->subDay(),
        ]);

        $this->actingAs($user)
             ->get('/app?_tenant=trial-expired')
             ->assertStatus(402);
    }

    #[Test]
    public function suspended_company_is_blocked(): void
    {
        $user = User::factory()->create();
        Company::factory()->create([
            'slug'                => 'suspended',
            'is_active'           => true,
            'subscription_status' => 'suspended',
        ]);

        $this->actingAs($user)
             ->get('/app?_tenant=suspended')
             ->assertStatus(402);
    }

    #[Test]
    public function grace_period_allows_get_requests(): void
    {
        $user = User::factory()->create();
        Company::factory()->create([
            'slug'                => 'grace',
            'is_active'           => true,
            'subscription_status' => 'grace_period',
        ]);

        $this->actingAs($user)
             ->get('/app?_tenant=grace')
             ->assertStatus(200);
    }
}
