<?php

namespace Tests\Feature\App\Location;

use App\Models\Company;
use App\Models\Customer;
use App\Models\RentalAsset;
use App\Models\RentalContract;
use App\Models\RentalPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Company $company;
    private RentalContract $contract;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->company = Company::create([
            'name' => 'Test Co', 'slug' => 'test-co', 'email' => 'test@co.sn',
            'subscription_status' => 'trial', 'trial_ends_at' => now()->addDays(14), 'is_active' => true,
        ]);
        $this->owner = User::factory()->create(['is_super_admin' => false]);
        $this->company->users()->attach($this->owner->id, ['role' => 'owner', 'joined_at' => now()]);

        $asset    = RentalAsset::factory()->create(['company_id' => $this->company->id]);
        $customer = Customer::factory()->create(['company_id' => $this->company->id]);
        $this->contract = RentalContract::factory()->create([
            'company_id' => $this->company->id, 'rental_asset_id' => $asset->id,
            'customer_id' => $customer->id, 'user_id' => $this->owner->id,
        ]);
    }

    private function tenantGet(string $path) { $sep = str_contains($path, '?') ? '&' : '?'; return $this->actingAs($this->owner)->get('/app' . $path . $sep . '_tenant=test-co'); }
    private function tenantPost(string $path, array $data = []) { $sep = str_contains($path, '?') ? '&' : '?'; return $this->actingAs($this->owner)->post('/app' . $path . $sep . '_tenant=test-co', $data); }

    #[Test]
    public function payments_index_loads(): void
    {
        $this->tenantGet('/location/payments')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Location/Payments/Index'));
    }

    #[Test]
    public function payments_index_filters_by_status(): void
    {
        RentalPayment::factory()->create([
            'company_id' => $this->company->id, 'rental_contract_id' => $this->contract->id, 'status' => 'pending',
        ]);
        RentalPayment::factory()->paid()->create([
            'company_id' => $this->company->id, 'rental_contract_id' => $this->contract->id,
        ]);

        $this->tenantGet('/location/payments?status=pending')->assertOk()
            ->assertInertia(fn ($p) => $p->component('App/Location/Payments/Index')->has('payments.data', 1));
    }

    #[Test]
    public function record_full_payment(): void
    {
        $payment = RentalPayment::factory()->create([
            'company_id' => $this->company->id, 'rental_contract_id' => $this->contract->id,
            'amount' => 100000, 'amount_paid' => 0, 'status' => 'pending',
        ]);

        $this->tenantPost("/location/payments/{$payment->id}/record", [
            'amount_paid'  => 100000,
            'payment_date' => '2026-04-15',
            'method'       => 'cash',
        ])->assertRedirect();

        $payment->refresh();
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals(100000, $payment->amount_paid);
        $this->assertEquals('cash', $payment->method);
    }

    #[Test]
    public function record_partial_payment(): void
    {
        $payment = RentalPayment::factory()->create([
            'company_id' => $this->company->id, 'rental_contract_id' => $this->contract->id,
            'amount' => 100000, 'amount_paid' => 0, 'status' => 'pending',
        ]);

        $this->tenantPost("/location/payments/{$payment->id}/record", [
            'amount_paid'  => 50000,
            'payment_date' => '2026-04-15',
            'method'       => 'wave',
        ])->assertRedirect();

        $payment->refresh();
        $this->assertEquals('partial', $payment->status);
        $this->assertEquals(50000, $payment->amount_paid);
    }

    #[Test]
    public function record_payment_accumulates(): void
    {
        $payment = RentalPayment::factory()->create([
            'company_id' => $this->company->id, 'rental_contract_id' => $this->contract->id,
            'amount' => 100000, 'amount_paid' => 30000, 'status' => 'partial',
        ]);

        $this->tenantPost("/location/payments/{$payment->id}/record", [
            'amount_paid'  => 70000,
            'payment_date' => '2026-04-20',
            'method'       => 'om',
        ])->assertRedirect();

        $payment->refresh();
        $this->assertEquals('paid', $payment->status);
        $this->assertEquals(100000, $payment->amount_paid);
    }

    #[Test]
    public function record_payment_validates_required(): void
    {
        $payment = RentalPayment::factory()->create([
            'company_id' => $this->company->id, 'rental_contract_id' => $this->contract->id,
        ]);

        $this->tenantPost("/location/payments/{$payment->id}/record", [])
            ->assertSessionHasErrors(['amount_paid', 'payment_date', 'method']);
    }

    #[Test]
    public function record_payment_validates_method(): void
    {
        $payment = RentalPayment::factory()->create([
            'company_id' => $this->company->id, 'rental_contract_id' => $this->contract->id,
        ]);

        $this->tenantPost("/location/payments/{$payment->id}/record", [
            'amount_paid' => 1000, 'payment_date' => '2026-04-15', 'method' => 'invalid_method',
        ])->assertSessionHasErrors(['method']);
    }

    #[Test]
    public function mark_overdue(): void
    {
        RentalPayment::factory()->create([
            'company_id' => $this->company->id, 'rental_contract_id' => $this->contract->id,
            'status' => 'pending', 'due_date' => now()->subDays(5)->toDateString(),
        ]);
        RentalPayment::factory()->create([
            'company_id' => $this->company->id, 'rental_contract_id' => $this->contract->id,
            'status' => 'pending', 'due_date' => now()->addDays(5)->toDateString(),
        ]);

        $this->tenantPost('/location/payments/mark-overdue')->assertRedirect();

        $this->assertEquals(1, RentalPayment::where('company_id', $this->company->id)->where('status', 'overdue')->count());
        $this->assertEquals(1, RentalPayment::where('company_id', $this->company->id)->where('status', 'pending')->count());
    }
}
