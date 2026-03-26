<?php

namespace Tests\Feature\App;

use App\Models\Company;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerTest extends TestCase
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

    private function tenantGet(string $path)
    {
        return $this->actingAs($this->owner)->get('/app' . $path . '?_tenant=test-co');
    }

    private function tenantPost(string $path, array $data = [])
    {
        return $this->actingAs($this->owner)->post('/app' . $path . '?_tenant=test-co', $data);
    }

    private function tenantPut(string $path, array $data = [])
    {
        return $this->actingAs($this->owner)->put('/app' . $path . '?_tenant=test-co', $data);
    }

    private function tenantDelete(string $path)
    {
        return $this->actingAs($this->owner)->delete('/app' . $path . '?_tenant=test-co');
    }

    // ── INDEX ──────────────────────────────────────────────

    #[Test]
    public function customers_index_loads(): void
    {
        Customer::factory()->create(['company_id' => $this->company->id, 'name' => 'Awa Diop']);
        Customer::factory()->create(['company_id' => $this->company->id, 'name' => 'Moussa Ba']);

        $response = $this->tenantGet('/customers');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('App/Customers/Index')
                 ->has('customers.data', 2)
        );
    }

    #[Test]
    public function customers_index_filters_by_search(): void
    {
        Customer::factory()->create(['company_id' => $this->company->id, 'name' => 'Awa Diop']);
        Customer::factory()->create(['company_id' => $this->company->id, 'name' => 'Moussa Ba']);

        $response = $this->actingAs($this->owner)
            ->get('/app/customers?_tenant=test-co&search=Awa');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('customers.data', 1)
        );
    }

    #[Test]
    public function customers_index_filters_by_category(): void
    {
        Customer::factory()->create(['company_id' => $this->company->id, 'category' => 'vip']);
        Customer::factory()->create(['company_id' => $this->company->id, 'category' => 'normal']);

        $response = $this->actingAs($this->owner)
            ->get('/app/customers?_tenant=test-co&category=vip');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('customers.data', 1)
        );
    }

    #[Test]
    public function customers_are_scoped_to_tenant(): void
    {
        $otherCompany = Company::create([
            'name' => 'Other Co', 'slug' => 'other-co', 'email' => 'x@y.sn',
            'subscription_status' => 'active', 'is_active' => true,
        ]);
        Customer::factory()->create(['company_id' => $otherCompany->id, 'name' => 'Invisible']);
        Customer::factory()->create(['company_id' => $this->company->id, 'name' => 'Visible']);

        $response = $this->tenantGet('/customers');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('customers.data', 1)
        );
    }

    // ── CREATE / STORE ────────────────────────────────────

    #[Test]
    public function create_customer_form_loads(): void
    {
        $response = $this->tenantGet('/customers/create');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Customers/Form'));
    }

    #[Test]
    public function store_customer_with_valid_data(): void
    {
        $response = $this->tenantPost('/customers', [
            'name'     => 'Fatou Sow',
            'phone'    => '+221 77 123 45 67',
            'email'    => 'fatou@example.com',
            'category' => 'vip',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'company_id' => $this->company->id,
            'name'       => 'Fatou Sow',
            'phone'      => '+221 77 123 45 67',
            'category'   => 'vip',
        ]);
    }

    #[Test]
    public function store_customer_validates_required_fields(): void
    {
        $response = $this->tenantPost('/customers', [
            'name'  => '',
            'phone' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'phone']);
    }

    #[Test]
    public function store_customer_validates_email_format(): void
    {
        $response = $this->tenantPost('/customers', [
            'name'  => 'Test',
            'phone' => '770001122',
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    #[Test]
    public function store_customer_validates_nif_unique_per_company(): void
    {
        Customer::factory()->create([
            'company_id' => $this->company->id,
            'nif'        => 'NIF-001',
        ]);

        $response = $this->tenantPost('/customers', [
            'name'  => 'Duplicate NIF',
            'phone' => '770001122',
            'nif'   => 'NIF-001',
        ]);

        $response->assertSessionHasErrors(['nif']);
    }

    // ── SHOW ──────────────────────────────────────────────

    #[Test]
    public function show_customer_displays_details(): void
    {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'name'       => 'Client Test',
        ]);

        $response = $this->tenantGet('/customers/' . $customer->id);

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('App/Customers/Show')
                 ->where('customer.name', 'Client Test')
        );
    }

    #[Test]
    public function show_customer_404_for_other_tenant(): void
    {
        $otherCompany = Company::create([
            'name' => 'Other', 'slug' => 'other', 'email' => 'o@o.sn',
            'subscription_status' => 'active', 'is_active' => true,
        ]);
        $customer = Customer::factory()->create(['company_id' => $otherCompany->id]);

        $response = $this->tenantGet('/customers/' . $customer->id);
        $response->assertStatus(404);
    }

    // ── EDIT / UPDATE ─────────────────────────────────────

    #[Test]
    public function edit_customer_form_loads(): void
    {
        $customer = Customer::factory()->create(['company_id' => $this->company->id]);

        $response = $this->tenantGet('/customers/' . $customer->id . '/edit');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('App/Customers/Form')
                 ->has('customer')
        );
    }

    #[Test]
    public function update_customer_with_valid_data(): void
    {
        $customer = Customer::factory()->create([
            'company_id' => $this->company->id,
            'name'       => 'Old Name',
        ]);

        $response = $this->tenantPut('/customers/' . $customer->id, [
            'name'     => 'New Name',
            'phone'    => '+221 77 999 00 11',
            'category' => 'professional',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('customers', [
            'id'       => $customer->id,
            'name'     => 'New Name',
            'category' => 'professional',
        ]);
    }

    // ── DELETE ─────────────────────────────────────────────

    #[Test]
    public function delete_customer_soft_deletes(): void
    {
        $customer = Customer::factory()->create(['company_id' => $this->company->id]);

        $response = $this->tenantDelete('/customers/' . $customer->id);

        $response->assertRedirect();
        $this->assertSoftDeleted('customers', ['id' => $customer->id]);
    }

    // ── AUTH ──────────────────────────────────────────────

    #[Test]
    public function customers_require_authentication(): void
    {
        $response = $this->get('/app/customers?_tenant=test-co');
        $response->assertRedirect('/login');
    }
}
