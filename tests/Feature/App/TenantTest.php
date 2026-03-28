<?php

namespace Tests\Feature\App;

use App\Models\Company;
use App\Models\Module;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Mail\WelcomeMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TenantTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->company = Company::create([
            'name'                => 'Test Co',
            'slug'                => 'test-co',
            'email'               => 'test@co.sn',
            'phone'               => '+221770000000',
            'sector'              => 'boutique',
            'subscription_status' => 'trial',
            'trial_ends_at'       => now()->addDays(14),
            'is_active'           => true,
        ]);

        $this->owner = User::factory()->create(['is_super_admin' => false]);
        $this->company->users()->attach($this->owner->id, [
            'role'      => 'owner',
            'joined_at' => now(),
        ]);
    }

    private function tenantGet(string $path): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->owner)
                     ->get('/app' . $path . '?_tenant=test-co');
    }

    private function tenantPut(string $path, array $data): \Illuminate\Testing\TestResponse
    {
        return $this->actingAs($this->owner)
                     ->put('/app' . $path . '?_tenant=test-co', $data);
    }

    // ── Dashboard ─────────────────────────────────────────────

    #[Test]
    public function dashboard_loads_for_tenant_user(): void
    {
        $response = $this->tenantGet('/');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('App/Dashboard')
                 ->has('stats')
                 ->has('recentUsers')
                 ->has('activeModules')
        );
    }

    #[Test]
    public function dashboard_shows_correct_stats(): void
    {
        $response = $this->tenantGet('/');

        $response->assertInertia(fn ($page) =>
            $page->component('App/Dashboard')
                 ->where('stats.users_count', 1)
                 ->where('stats.modules_count', 0)
                 ->where('stats.subscription', 'trial')
        );
    }

    #[Test]
    public function dashboard_requires_authentication(): void
    {
        $response = $this->get('/app?_tenant=test-co');

        $response->assertRedirect('/login');
    }

    // ── Onboarding ────────────────────────────────────────────

    #[Test]
    public function onboarding_page_loads(): void
    {
        $response = $this->tenantGet('/onboarding');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('App/Onboarding')
                 ->has('company')
                 ->has('steps')
        );
    }

    #[Test]
    public function onboarding_can_update_company_profile(): void
    {
        $response = $this->tenantPut('/onboarding', [
            'email'   => 'new@co.sn',
            'phone'   => '+221771111111',
            'address' => 'Dakar Centre',
            'sector'  => 'restaurant',
            'ninea'   => '123456789',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->company->refresh();
        $this->assertEquals('new@co.sn', $this->company->email);
        $this->assertEquals('+221771111111', $this->company->phone);
        $this->assertEquals('Dakar Centre', $this->company->address);
        $this->assertEquals('restaurant', $this->company->sector);
        $this->assertEquals('123456789', $this->company->ninea);
    }

    #[Test]
    public function onboarding_update_queues_welcome_email(): void
    {
        Mail::fake();

        $response = $this->tenantPut('/onboarding', [
            'email'   => 'welcome@co.sn',
            'phone'   => '+221771111111',
            'address' => 'Dakar',
            'sector'  => 'restaurant',
            'ninea'   => 'NIN-123',
        ]);

        $response->assertRedirect();

        Mail::assertQueued(WelcomeMail::class, fn (WelcomeMail $mail) =>
            (int) $mail->company->id === (int) $this->company->id
            && (int) $mail->user->id === (int) $this->owner->id
        );
    }

    #[Test]
    public function onboarding_validates_email_format(): void
    {
        $response = $this->tenantPut('/onboarding', [
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors('email');
    }

    // ── Settings ──────────────────────────────────────────────

    #[Test]
    public function settings_page_loads(): void
    {
        $response = $this->tenantGet('/settings');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('App/Settings')
                 ->has('company')
                 ->where('company.name', 'Test Co')
                 ->where('company.currency', 'XOF')
        );
    }

    #[Test]
    public function settings_can_update_company(): void
    {
        $response = $this->tenantPut('/settings', [
            'name'            => 'Test Co Renamed',
            'email'           => 'admin@co.sn',
            'phone'           => '+221772222222',
            'address'         => 'Thiès',
            'sector'          => 'services',
            'ninea'           => 'NINEA999',
            'currency'        => 'EUR',
            'timezone'        => 'Europe/Paris',
            'primary_color'   => '#3B82F6',
            'secondary_color' => '#EF4444',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->company->refresh();
        $this->assertEquals('Test Co Renamed', $this->company->name);
        $this->assertEquals('EUR', $this->company->currency);
        $this->assertEquals('Europe/Paris', $this->company->timezone);
    }

    #[Test]
    public function settings_requires_company_name(): void
    {
        $response = $this->tenantPut('/settings', [
            'name'     => '',
            'currency' => 'XOF',
            'timezone' => 'Africa/Dakar',
        ]);

        $response->assertSessionHasErrors('name');
    }

    // ── Tenant resolution ─────────────────────────────────────

    #[Test]
    public function invalid_tenant_returns_404(): void
    {
        $response = $this->actingAs($this->owner)
                         ->get('/app?_tenant=non-existent');

        $response->assertNotFound();
    }

    #[Test]
    public function inactive_tenant_returns_404(): void
    {
        $this->company->update(['is_active' => false]);

        $response = $this->actingAs($this->owner)
                         ->get('/app?_tenant=test-co');

        $response->assertNotFound();
    }

    // ── Subscription check ────────────────────────────────────

    #[Test]
    public function expired_trial_blocks_access(): void
    {
        $this->company->update([
            'subscription_status' => 'trial',
            'trial_ends_at'       => now()->subDay(),
        ]);

        $response = $this->actingAs($this->owner)
                         ->get('/app?_tenant=test-co');

        $response->assertStatus(402);
    }
}
