<?php

namespace Tests\Feature\App;

use App\Models\Company;
use App\Models\PayDunyaTransaction;
use App\Models\Plan;
use App\Models\User;
use App\Services\PayDunyaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private Company $company;
    private User    $user;
    private Plan    $plan;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        $this->company = Company::factory()->create([
            'slug'                => 'test-payment-co',
            'subscription_status' => 'trial',
        ]);

        $this->user = User::factory()->create();
        $this->company->users()->attach($this->user->id, ['role' => 'owner']);

        $this->plan = Plan::factory()->create([
            'price_monthly'   => 9900,
            'price_quarterly' => 26700,
            'price_yearly'    => 95000,
            'is_active'       => true,
        ]);
    }

    // ── Subscription page ──────────────────────────────────────

    #[Test]
    public function subscription_page_loads_plans(): void
    {
        $this->actingAs($this->user)
             ->get('/app/subscription?_tenant=test-payment-co')
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p
                 ->component('App/Subscription/Index')
                 ->has('plans', 1)
             );
    }

    // ── Payment initiation ─────────────────────────────────────

    #[Test]
    public function initiate_creates_pending_transaction_and_redirects(): void
    {
        // Use fake mode so no real HTTP call
        $this->app->singleton(PayDunyaService::class, function () {
            $mock = $this->createMock(PayDunyaService::class);
            $mock->method('createInvoice')->willReturn([
                'token'       => 'test_tok_abc123',
                'invoice_url' => 'https://app.paydunya.com/sandbox/checkout-invoice/confirm/test_tok_abc123',
            ]);
            return $mock;
        });

        $response = $this->actingAs($this->user)
             ->post("/app/payment/initiate/{$this->plan->id}/monthly?_tenant=test-payment-co");

        $response->assertRedirect();

        $this->assertDatabaseHas('paydunya_transactions', [
            'company_id'     => $this->company->id,
            'plan_id'        => $this->plan->id,
            'billing_period' => 'monthly',
            'amount'         => 9900,
            'paydunya_token' => 'test_tok_abc123',
        ]);
    }

    #[Test]
    public function initiate_rejects_invalid_period(): void
    {
        $this->actingAs($this->user)
             ->post("/app/payment/initiate/{$this->plan->id}/weekly?_tenant=test-payment-co")
             ->assertStatus(422);
    }

    #[Test]
    public function initiate_rejects_inactive_plan(): void
    {
        $inactive = Plan::factory()->create(['is_active' => false]);

        $this->actingAs($this->user)
             ->post("/app/payment/initiate/{$inactive->id}/monthly?_tenant=test-payment-co")
             ->assertStatus(422);
    }

    // ── Callback ───────────────────────────────────────────────

    #[Test]
    public function callback_activates_subscription_on_completed_status(): void
    {
        $tx = PayDunyaTransaction::create([
            'company_id'     => $this->company->id,
            'plan_id'        => $this->plan->id,
            'billing_period' => 'monthly',
            'amount'         => 9900,
            'status'         => 'pending',
            'paydunya_token' => 'tok_callback_abc',
        ]);

        $this->app->singleton(PayDunyaService::class, function () {
            $mock = $this->createMock(PayDunyaService::class);
            $mock->method('confirmPayment')->willReturn([
                'status'    => 'completed',
                'reference' => 'REF_001',
            ]);
            return $mock;
        });

        $this->actingAs($this->user)
             ->get("/app/payment/callback/{$tx->id}?_tenant=test-payment-co")
             ->assertRedirect();

        $this->assertEquals('success', $tx->fresh()->status);
        $this->assertEquals('active', $this->company->fresh()->subscription_status);
        $this->assertDatabaseHas('subscriptions', [
            'company_id'     => $this->company->id,
            'plan_id'        => $this->plan->id,
            'status'         => 'active',
            'billing_period' => 'monthly',
            'amount_paid'    => 9900,
        ]);
    }

    #[Test]
    public function callback_marks_failed_when_payment_not_completed(): void
    {
        $tx = PayDunyaTransaction::create([
            'company_id'     => $this->company->id,
            'plan_id'        => $this->plan->id,
            'billing_period' => 'monthly',
            'amount'         => 9900,
            'status'         => 'pending',
            'paydunya_token' => 'tok_fail_xyz',
        ]);

        $this->app->singleton(PayDunyaService::class, function () {
            $mock = $this->createMock(PayDunyaService::class);
            $mock->method('confirmPayment')->willReturn([
                'status'    => 'cancelled',
                'reference' => '',
            ]);
            return $mock;
        });

        $this->actingAs($this->user)
             ->get("/app/payment/callback/{$tx->id}?_tenant=test-payment-co")
             ->assertRedirect();

        $this->assertEquals('failed', $tx->fresh()->status);
    }

    // ── Cancel ────────────────────────────────────────────────

    #[Test]
    public function cancel_marks_transaction_cancelled(): void
    {
        $tx = PayDunyaTransaction::create([
            'company_id'     => $this->company->id,
            'plan_id'        => $this->plan->id,
            'billing_period' => 'monthly',
            'amount'         => 9900,
            'status'         => 'pending',
        ]);

        $this->actingAs($this->user)
             ->get("/app/payment/cancel/{$tx->id}?_tenant=test-payment-co")
             ->assertRedirect();

        $this->assertEquals('cancelled', $tx->fresh()->status);
    }

    // ── Webhook IPN ───────────────────────────────────────────

    #[Test]
    public function webhook_activates_subscription_from_ipn(): void
    {
        $tx = PayDunyaTransaction::create([
            'company_id'     => $this->company->id,
            'plan_id'        => $this->plan->id,
            'billing_period' => 'yearly',
            'amount'         => 95000,
            'status'         => 'pending',
            'paydunya_token' => 'tok_ipn_webhook',
        ]);

        $this->app->singleton(PayDunyaService::class, function () {
            $mock = $this->createMock(PayDunyaService::class);
            $mock->method('confirmPayment')->willReturn([
                'status'    => 'completed',
                'reference' => 'IPN_REF_001',
            ]);
            return $mock;
        });

        $this->postJson('/payment/webhook', [
            'data' => ['invoice' => ['token' => 'tok_ipn_webhook']],
        ])
        ->assertJson(['status' => 'ok']);

        $this->assertEquals('success', $tx->fresh()->status);
        $this->assertDatabaseHas('subscriptions', [
            'company_id'     => $this->company->id,
            'billing_period' => 'yearly',
            'amount_paid'    => 95000,
        ]);
    }

    #[Test]
    public function webhook_returns_400_for_missing_token(): void
    {
        $this->postJson('/payment/webhook', [])
             ->assertStatus(400);
    }

    // ── Access control ────────────────────────────────────────

    #[Test]
    public function payment_callback_rejects_wrong_company(): void
    {
        $otherCompany = Company::factory()->create(['slug' => 'other-company', 'is_active' => true]);
        $tx = PayDunyaTransaction::create([
            'company_id'     => $otherCompany->id,
            'plan_id'        => $this->plan->id,
            'billing_period' => 'monthly',
            'amount'         => 9900,
            'status'         => 'pending',
        ]);

        $this->actingAs($this->user)
             ->get("/app/payment/callback/{$tx->id}?_tenant=test-payment-co")
             ->assertStatus(403);
    }

    #[Test]
    public function expired_company_can_still_access_subscription_payment_page(): void
    {
        $this->company->update([
            'subscription_status' => 'expired',
            'trial_ends_at' => now()->subDay(),
        ]);

        $this->actingAs($this->user)
             ->get('/app/subscription?_tenant=test-payment-co')
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p->component('App/Subscription/Index'));
    }
}
