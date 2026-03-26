<?php

namespace Tests\Feature\App;

use App\Models\Company;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SupplierTest extends TestCase
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
    public function suppliers_index_loads(): void
    {
        Supplier::factory()->create(['company_id' => $this->company->id, 'name' => 'Sow Distribution']);
        Supplier::factory()->create(['company_id' => $this->company->id, 'name' => 'Diallo Import']);

        $response = $this->tenantGet('/suppliers');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('App/Suppliers/Index')
                 ->has('suppliers.data', 2)
        );
    }

    #[Test]
    public function suppliers_index_filters_by_search(): void
    {
        Supplier::factory()->create(['company_id' => $this->company->id, 'name' => 'Sow Distribution']);
        Supplier::factory()->create(['company_id' => $this->company->id, 'name' => 'Diallo Import']);

        $response = $this->actingAs($this->owner)
            ->get('/app/suppliers?_tenant=test-co&search=Sow');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('suppliers.data', 1)
        );
    }

    #[Test]
    public function suppliers_are_scoped_to_tenant(): void
    {
        $otherCompany = Company::create([
            'name' => 'Other Co', 'slug' => 'other-co', 'email' => 'x@y.sn',
            'subscription_status' => 'active', 'is_active' => true,
        ]);
        Supplier::factory()->create(['company_id' => $otherCompany->id]);
        Supplier::factory()->create(['company_id' => $this->company->id]);

        $response = $this->tenantGet('/suppliers');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->has('suppliers.data', 1)
        );
    }

    // ── CREATE / STORE ────────────────────────────────────

    #[Test]
    public function create_supplier_form_loads(): void
    {
        $response = $this->tenantGet('/suppliers/create');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Suppliers/Form'));
    }

    #[Test]
    public function store_supplier_with_valid_data(): void
    {
        $response = $this->tenantPost('/suppliers', [
            'name'    => 'Baol Import SARL',
            'phone'   => '+221 33 800 00 00',
            'email'   => 'contact@baol.sn',
            'ninea'   => 'NINEA-999',
            'rib'     => 'SN08 SN0001234567',
            'rating'  => 4.5,
            'notes'   => 'Fournisseur fiable',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('suppliers', [
            'company_id' => $this->company->id,
            'name'       => 'Baol Import SARL',
            'ninea'      => 'NINEA-999',
        ]);
    }

    #[Test]
    public function store_supplier_validates_required_fields(): void
    {
        $response = $this->tenantPost('/suppliers', [
            'name'  => '',
            'phone' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'phone']);
    }

    #[Test]
    public function store_supplier_validates_rating_range(): void
    {
        $response = $this->tenantPost('/suppliers', [
            'name'   => 'Test',
            'phone'  => '770001122',
            'rating' => 6,
        ]);

        $response->assertSessionHasErrors(['rating']);
    }

    #[Test]
    public function store_supplier_validates_ninea_unique_per_company(): void
    {
        Supplier::factory()->create([
            'company_id' => $this->company->id,
            'ninea'      => 'NINEA-001',
        ]);

        $response = $this->tenantPost('/suppliers', [
            'name'  => 'Duplicate NINEA',
            'phone' => '770001122',
            'ninea' => 'NINEA-001',
        ]);

        $response->assertSessionHasErrors(['ninea']);
    }

    // ── SHOW ──────────────────────────────────────────────

    #[Test]
    public function show_supplier_displays_details(): void
    {
        $supplier = Supplier::factory()->create([
            'company_id' => $this->company->id,
            'name'       => 'Fournisseur Test',
        ]);

        $response = $this->tenantGet('/suppliers/' . $supplier->id);

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('App/Suppliers/Show')
                 ->where('supplier.name', 'Fournisseur Test')
        );
    }

    #[Test]
    public function show_supplier_404_for_other_tenant(): void
    {
        $otherCompany = Company::create([
            'name' => 'Other', 'slug' => 'other', 'email' => 'o@o.sn',
            'subscription_status' => 'active', 'is_active' => true,
        ]);
        $supplier = Supplier::factory()->create(['company_id' => $otherCompany->id]);

        $response = $this->tenantGet('/suppliers/' . $supplier->id);
        $response->assertStatus(404);
    }

    // ── EDIT / UPDATE ─────────────────────────────────────

    #[Test]
    public function edit_supplier_form_loads(): void
    {
        $supplier = Supplier::factory()->create(['company_id' => $this->company->id]);

        $response = $this->tenantGet('/suppliers/' . $supplier->id . '/edit');

        $response->assertOk();
        $response->assertInertia(fn ($page) =>
            $page->component('App/Suppliers/Form')
                 ->has('supplier')
        );
    }

    #[Test]
    public function update_supplier_with_valid_data(): void
    {
        $supplier = Supplier::factory()->create([
            'company_id' => $this->company->id,
            'name'       => 'Old Name',
        ]);

        $response = $this->tenantPut('/suppliers/' . $supplier->id, [
            'name'   => 'New Name',
            'phone'  => '+221 33 111 22 33',
            'rating' => 3.5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('suppliers', [
            'id'   => $supplier->id,
            'name' => 'New Name',
        ]);
    }

    // ── DELETE ─────────────────────────────────────────────

    #[Test]
    public function delete_supplier_soft_deletes(): void
    {
        $supplier = Supplier::factory()->create(['company_id' => $this->company->id]);

        $response = $this->tenantDelete('/suppliers/' . $supplier->id);

        $response->assertRedirect();
        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }

    // ── AUTH ──────────────────────────────────────────────

    #[Test]
    public function suppliers_require_authentication(): void
    {
        $response = $this->get('/app/suppliers?_tenant=test-co');
        $response->assertRedirect('/login');
    }
}
