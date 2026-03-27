<?php

namespace Tests\Feature\App\Boutique;

use App\Models\Company;
use App\Models\Customer;
use App\Models\LoyaltyConfig;
use App\Models\LoyaltyTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LoyaltyTest extends TestCase
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
    private function tenantPut(string $path, array $data = []) { return $this->actingAs($this->owner)->put('/app' . $path . '?_tenant=test-co', $data); }

    #[Test]
    public function loyalty_index_loads_and_creates_default_config(): void
    {
        $this->tenantGet('/boutique/loyalty')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Boutique/Loyalty/Index')->has('config'));

        $this->assertDatabaseHas('loyalty_configs', ['company_id' => $this->company->id]);
    }

    #[Test]
    public function loyalty_index_shows_transactions_and_stats(): void
    {
        $customer = Customer::factory()->create(['company_id' => $this->company->id, 'loyalty_points' => 50]);
        LoyaltyConfig::create(['company_id' => $this->company->id, 'points_per_amount' => 1, 'amount_per_point' => 1000, 'redemption_threshold' => 10, 'redemption_value' => 500, 'is_active' => true]);
        LoyaltyTransaction::create(['company_id' => $this->company->id, 'customer_id' => $customer->id, 'type' => 'earn', 'points' => 50, 'description' => 'Test earn']);

        $this->tenantGet('/boutique/loyalty')->assertOk()
            ->assertInertia(fn ($p) => $p
                ->has('transactions')
                ->has('topCustomers')
                ->where('totalEarned', 50)
            );
    }

    #[Test]
    public function update_loyalty_config(): void
    {
        LoyaltyConfig::create(['company_id' => $this->company->id, 'points_per_amount' => 1, 'amount_per_point' => 1000, 'redemption_threshold' => 100, 'redemption_value' => 500, 'is_active' => false]);

        $response = $this->tenantPut('/boutique/loyalty/config', [
            'points_per_amount'    => 2,
            'amount_per_point'     => 500,
            'redemption_threshold' => 50,
            'redemption_value'     => 1000,
            'is_active'            => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('loyalty_configs', [
            'company_id'         => $this->company->id,
            'points_per_amount'  => 2,
            'amount_per_point'   => 500,
            'redemption_threshold' => 50,
            'is_active'          => true,
        ]);
    }

    #[Test]
    public function update_config_validates_fields(): void
    {
        $this->tenantPut('/boutique/loyalty/config', [])
            ->assertSessionHasErrors(['points_per_amount', 'amount_per_point', 'redemption_threshold', 'redemption_value']);
    }

    #[Test]
    public function loyalty_config_calculates_points_correctly(): void
    {
        $config = LoyaltyConfig::create([
            'company_id'         => $this->company->id,
            'points_per_amount'  => 1,
            'amount_per_point'   => 1000,
            'redemption_threshold' => 10,
            'redemption_value'   => 500,
            'is_active'          => true,
        ]);

        // 5000 FCFA / 1000 * 1 = 5 points
        $this->assertEquals(5, $config->calculatePoints(5000));
        // 10 points * (500/10) = 500 FCFA
        $this->assertEquals(500.0, $config->calculateRedemptionValue(10));
    }

    #[Test]
    public function index_filters_transactions_by_type(): void
    {
        $customer = Customer::factory()->create(['company_id' => $this->company->id]);
        LoyaltyConfig::create(['company_id' => $this->company->id, 'points_per_amount' => 1, 'amount_per_point' => 1000, 'redemption_threshold' => 10, 'redemption_value' => 500, 'is_active' => true]);
        LoyaltyTransaction::create(['company_id' => $this->company->id, 'customer_id' => $customer->id, 'type' => 'earn', 'points' => 10, 'description' => 'Earn']);
        LoyaltyTransaction::create(['company_id' => $this->company->id, 'customer_id' => $customer->id, 'type' => 'redeem', 'points' => 5, 'description' => 'Redeem']);

        $this->actingAs($this->owner)
            ->get('/app/boutique/loyalty?_tenant=test-co&type=earn')
            ->assertOk()
            ->assertInertia(fn ($p) => $p->has('transactions.data', 1));
    }
}
