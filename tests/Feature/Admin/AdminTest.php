<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        return User::factory()->create(['is_super_admin' => true]);
    }

    private function makeUser(): User
    {
        return User::factory()->create(['is_super_admin' => false]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    #[Test]
    public function guest_cannot_access_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    #[Test]
    public function non_admin_gets_403(): void
    {
        $this->actingAs($this->makeUser())
             ->get('/admin')
             ->assertStatus(403);
    }

    #[Test]
    public function admin_can_access_dashboard(): void
    {
        $this->actingAs($this->makeAdmin())
             ->get('/admin')
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p->component('Admin/Dashboard')->has('stats'));
    }

    #[Test]
    public function admin_can_list_companies(): void
    {
        Company::factory()->count(3)->create();

        $this->actingAs($this->makeAdmin())
             ->get('/admin/companies')
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p->component('Admin/Companies/Index')->has('companies'));
    }

    #[Test]
    public function admin_can_view_company(): void
    {
        $company = Company::factory()->create();

        $this->actingAs($this->makeAdmin())
             ->get("/admin/companies/{$company->id}")
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p->component('Admin/Companies/Show')->has('company'));
    }

    #[Test]
    public function admin_can_toggle_company_active(): void
    {
        $company = Company::factory()->create(['is_active' => true]);

        $this->actingAs($this->makeAdmin())
             ->patch("/admin/companies/{$company->id}/toggle")
             ->assertRedirect();

        $this->assertFalse($company->fresh()->is_active);
    }

    #[Test]
    public function admin_can_update_subscription_status(): void
    {
        $company = Company::factory()->create(['subscription_status' => 'trial']);

        $this->actingAs($this->makeAdmin())
             ->patch("/admin/companies/{$company->id}/subscription", [
                 'subscription_status' => 'suspended',
             ])
             ->assertRedirect();

        $this->assertEquals('suspended', $company->fresh()->subscription_status);
    }

    #[Test]
    public function admin_can_list_users(): void
    {
        User::factory()->count(3)->create();

        $this->actingAs($this->makeAdmin())
             ->get('/admin/users')
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p->component('Admin/Users/Index')->has('users'));
    }

    #[Test]
    public function admin_dashboard_stats_are_correct(): void
    {
        Company::factory()->count(2)->create(['is_active' => true, 'subscription_status' => 'active']);
        Company::factory()->count(1)->create(['is_active' => true, 'subscription_status' => 'trial']);

        $response = $this->actingAs($this->makeAdmin())
             ->get('/admin')
             ->assertStatus(200);

        $response->assertInertia(fn ($p) =>
            $p->component('Admin/Dashboard')
              ->where('stats.companies_total', 3)
              ->where('stats.companies_active', 2)
              ->where('stats.companies_trial', 1)
        );
    }
}
