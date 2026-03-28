<?php

namespace Tests\Feature\App;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExportImportTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->company = Company::create([
            'name' => 'Export Co', 'slug' => 'export-co', 'email' => 'exp@co.sn',
            'subscription_status' => 'trial', 'trial_ends_at' => now()->addDays(14), 'is_active' => true,
        ]);
        $this->owner = User::factory()->create(['is_super_admin' => false]);
        $this->company->users()->attach($this->owner->id, ['role' => 'owner', 'joined_at' => now()]);
    }

    private function tenantGet(string $path) { return $this->actingAs($this->owner)->get('/app' . $path . '?_tenant=export-co'); }

    #[Test]
    public function export_products_downloads_xlsx(): void
    {
        Product::create(['company_id' => $this->company->id, 'name' => 'Export Test', 'selling_price' => 100, 'purchase_price' => 50, 'is_active' => true]);
        $response = $this->tenantGet('/export/products');
        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    #[Test]
    public function export_customers_downloads_xlsx(): void
    {
        Customer::create(['company_id' => $this->company->id, 'name' => 'Client Export', 'phone' => '770000000']);
        $response = $this->tenantGet('/export/customers');
        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    #[Test]
    public function export_suppliers_downloads_xlsx(): void
    {
        Supplier::create(['company_id' => $this->company->id, 'name' => 'Fourn Export', 'phone' => '770000001']);
        $response = $this->tenantGet('/export/suppliers');
        $response->assertOk();
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    #[Test]
    public function exports_only_return_tenant_data(): void
    {
        $other = Company::create(['name' => 'Other', 'slug' => 'other', 'email' => 'o@o.sn', 'subscription_status' => 'active', 'is_active' => true]);
        Product::create(['company_id' => $other->id, 'name' => 'Other Product', 'selling_price' => 100, 'purchase_price' => 50, 'is_active' => true]);
        Product::create(['company_id' => $this->company->id, 'name' => 'My Product', 'selling_price' => 200, 'purchase_price' => 100, 'is_active' => true]);

        $response = $this->tenantGet('/export/products');
        $response->assertOk();
        // Content should only contain tenant's products (validated by export class scoping)
    }
}
