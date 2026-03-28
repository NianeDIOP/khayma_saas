<?php

namespace Tests\Feature\App;

use App\Models\Company;
use App\Models\Expense;
use App\Models\Sale;
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
        $this->company = Company::create([
            'name'                => 'Report Co',
            'slug'                => 'report-co',
            'email'               => 'report@co.sn',
            'subscription_status' => 'trial',
            'trial_ends_at'       => now()->addDays(14),
            'is_active'           => true,
        ]);
        $this->owner = User::factory()->create(['is_super_admin' => false]);
        $this->company->users()->attach($this->owner->id, ['role' => 'owner', 'joined_at' => now()]);
    }

    private function tenantGet(string $path, array $params = [])
    {
        $query = http_build_query(array_merge(['_tenant' => 'report-co'], $params));
        return $this->actingAs($this->owner)->get('/app' . $path . '?' . $query);
    }

    // ── Global Reports ────────────────────────────────────────────

    #[Test]
    public function guest_cannot_access_global_reports(): void
    {
        $response = $this->get('/app/reports?_tenant=report-co');
        $response->assertRedirect();
    }

    #[Test]
    public function owner_can_access_global_reports(): void
    {
        $response = $this->tenantGet('/reports');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('App/Reports/Overview')
            ->has('kpis')
            ->has('chartLabels')
            ->has('chartSales')
            ->has('chartExpenses')
            ->has('topProducts')
            ->has('salesByPayment')
            ->has('expensesByCategory')
            ->has('customerDebts')
            ->has('filters')
        );
    }

    #[Test]
    public function reports_accept_period_filters(): void
    {
        foreach (['today', 'week', 'month', 'year'] as $period) {
            $response = $this->tenantGet('/reports', ['period' => $period]);
            $response->assertOk();
            $response->assertInertia(fn ($page) => $page
                ->component('App/Reports/Overview')
                ->where('filters.period', $period)
            );
        }
    }

    #[Test]
    public function reports_kpis_count_only_tenant_data(): void
    {
        $other = Company::create([
            'name' => 'Other Co', 'slug' => 'other-co', 'email' => 'o@o.sn',
            'subscription_status' => 'active', 'is_active' => true,
        ]);

        Sale::factory()->create(['company_id' => $other->id,        'user_id' => $this->owner->id, 'total' => 50000]);
        Sale::factory()->create(['company_id' => $this->company->id, 'user_id' => $this->owner->id, 'total' => 10000]);

        $response = $this->tenantGet('/reports', ['period' => 'month']);
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('App/Reports/Overview')
            ->where('kpis.total_orders', 1)
        );
    }

    #[Test]
    public function reports_chart_labels_match_period(): void
    {
        $response = $this->tenantGet('/reports', ['period' => 'today']);
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('App/Reports/Overview')
            ->has('chartLabels', 1)
        );
    }

    // ── Dashboard ─────────────────────────────────────────────────

    #[Test]
    public function dashboard_returns_chart_props(): void
    {
        $response = $this->tenantGet('');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('App/Dashboard')
            ->has('kpis')
            ->has('chartDays')
            ->has('chartValues')
            ->has('payLabels')
            ->has('payValues')
        );
    }

    #[Test]
    public function dashboard_chart_covers_seven_days(): void
    {
        $response = $this->tenantGet('');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('chartDays', 7)
            ->has('chartValues', 7)
        );
    }

    #[Test]
    public function dashboard_kpis_scoped_to_tenant(): void
    {
        $other = Company::create([
            'name' => 'Outsider', 'slug' => 'outsider', 'email' => 'x@x.sn',
            'subscription_status' => 'active', 'is_active' => true,
        ]);

        Sale::factory()->create(['company_id' => $other->id,        'user_id' => $this->owner->id, 'total' => 99999]);
        Sale::factory()->create(['company_id' => $this->company->id, 'user_id' => $this->owner->id, 'total' => 5000]);
        Expense::factory()->create(['company_id' => $this->company->id, 'user_id' => $this->owner->id, 'amount' => 1000, 'date' => now()]);

        $response = $this->tenantGet('');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->where('kpis.sales_today', 5000)
            ->where('kpis.expenses_today', 1000)
        );
    }
}
