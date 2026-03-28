<?php

namespace Tests\Feature\App;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->company = Company::create([
            'name' => 'Audit Co', 'slug' => 'audit-co', 'email' => 'audit@co.sn',
            'subscription_status' => 'trial', 'trial_ends_at' => now()->addDays(14), 'is_active' => true,
        ]);
        $this->owner = User::factory()->create(['is_super_admin' => false]);
        $this->company->users()->attach($this->owner->id, ['role' => 'owner', 'joined_at' => now()]);
    }

    #[Test]
    public function creating_product_generates_audit_log(): void
    {
        $this->actingAs($this->owner);
        app()->instance('currentCompany', $this->company);

        $product = Product::create([
            'company_id' => $this->company->id,
            'name' => 'Test Audit Product',
            'selling_price' => 1000,
            'purchase_price' => 500,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => Product::class,
            'model_id' => $product->id,
            'action' => 'created',
            'user_id' => $this->owner->id,
            'company_id' => $this->company->id,
        ]);
    }

    #[Test]
    public function updating_product_generates_audit_log_with_changes(): void
    {
        $this->actingAs($this->owner);
        app()->instance('currentCompany', $this->company);

        $product = Product::create([
            'company_id' => $this->company->id,
            'name' => 'Old Name',
            'selling_price' => 1000,
            'purchase_price' => 500,
            'is_active' => true,
        ]);

        $product->update(['name' => 'New Name']);

        $log = AuditLog::where('action', 'updated')
            ->where('model_id', $product->id)
            ->where('model_type', Product::class)
            ->first();

        $this->assertNotNull($log);
        $this->assertArrayHasKey('name', $log->new_values);
    }

    #[Test]
    public function deleting_product_generates_audit_log(): void
    {
        $this->actingAs($this->owner);
        app()->instance('currentCompany', $this->company);

        $product = Product::create([
            'company_id' => $this->company->id,
            'name' => 'Delete Me',
            'selling_price' => 500,
            'purchase_price' => 250,
            'is_active' => true,
        ]);

        $productId = $product->id;
        $product->delete();

        $this->assertDatabaseHas('audit_logs', [
            'model_type' => Product::class,
            'model_id' => $productId,
            'action' => 'deleted',
        ]);
    }

    #[Test]
    public function audit_logs_index_loads_for_admin(): void
    {
        $admin = User::factory()->create(['is_super_admin' => true]);
        $response = $this->actingAs($admin)->get('/admin/audit-logs');
        $response->assertOk();
    }
}
