<?php

namespace Tests\Feature\App\Quincaillerie;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreditTest extends TestCase
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
    public function credits_index_loads(): void
    {
        $this->tenantGet('/quincaillerie/credits')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Quincaillerie/Credits/Index'));
    }

    #[Test]
    public function add_payment_to_unpaid_sale(): void
    {
        $customer = Customer::factory()->create(['company_id' => $this->company->id]);
        $sale = Sale::factory()->create([
            'company_id'     => $this->company->id,
            'customer_id'    => $customer->id,
            'user_id'        => $this->owner->id,
            'payment_status' => 'unpaid',
            'total'          => 10000,
        ]);

        $this->tenantPost('/quincaillerie/credits/' . $sale->id . '/payment', [
            'amount' => 5000,
            'method' => 'cash',
        ])->assertRedirect();

        $this->assertDatabaseHas('payments', ['sale_id' => $sale->id, 'amount' => 5000]);
        $this->assertDatabaseHas('sales', ['id' => $sale->id, 'payment_status' => 'partial']);
    }

    #[Test]
    public function full_payment_marks_sale_paid(): void
    {
        $customer = Customer::factory()->create(['company_id' => $this->company->id]);
        $sale = Sale::factory()->create([
            'company_id'     => $this->company->id,
            'customer_id'    => $customer->id,
            'user_id'        => $this->owner->id,
            'payment_status' => 'unpaid',
            'total'          => 10000,
        ]);

        $this->tenantPost('/quincaillerie/credits/' . $sale->id . '/payment', [
            'amount' => 10000,
            'method' => 'wave',
        ])->assertRedirect();

        $this->assertDatabaseHas('sales', ['id' => $sale->id, 'payment_status' => 'paid']);
    }
}
