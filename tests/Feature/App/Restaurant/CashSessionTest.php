<?php

namespace Tests\Feature\App\Restaurant;

use App\Models\CashSession;
use App\Models\Company;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CashSessionTest extends TestCase
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

    #[Test]
    public function cash_sessions_index_loads(): void
    {
        $response = $this->tenantGet('/restaurant/cash-sessions');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Restaurant/CashSessions/Index'));
    }

    #[Test]
    public function open_cash_session(): void
    {
        $response = $this->tenantPost('/restaurant/cash-sessions/open', [
            'opening_amount' => 25000,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('cash_sessions', [
            'company_id'     => $this->company->id,
            'opening_amount' => 25000,
            'user_id'        => $this->owner->id,
        ]);
    }

    #[Test]
    public function cannot_open_second_session_while_one_is_open(): void
    {
        CashSession::factory()->create([
            'company_id' => $this->company->id,
            'user_id'    => $this->owner->id,
            'opened_at'  => now(),
            'closed_at'  => null,
        ]);

        $response = $this->tenantPost('/restaurant/cash-sessions/open', [
            'opening_amount' => 10000,
        ]);
        $response->assertRedirect();
        $response->assertSessionHasErrors('session');
    }

    #[Test]
    public function close_cash_session(): void
    {
        $session = CashSession::factory()->create([
            'company_id'     => $this->company->id,
            'user_id'        => $this->owner->id,
            'opened_at'      => now(),
            'closed_at'      => null,
            'opening_amount' => 5000,
        ]);

        $response = $this->tenantPost('/restaurant/cash-sessions/' . $session->id . '/close', [
            'closing_amount' => 45000,
        ]);
        $response->assertRedirect();
        $session->refresh();
        $this->assertNotNull($session->closed_at);
        $this->assertEquals(45000, $session->closing_amount);
    }
}
