<?php

namespace Tests\Unit\Tenant;

use App\Models\Concerns\BelongsToTenant;
use App\Models\Company;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

// Modèle factice pour tester le trait en isolation
class FakeProduct extends Model
{
    use BelongsToTenant;

    protected $table    = 'fake_products';
    protected $fillable = ['name', 'company_id'];
}

class TenantScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer une table temporaire pour le modèle factice
        \Illuminate\Support\Facades\Schema::create('fake_products', function ($table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });
    }

    /** @test */
    #[Test]
    public function tenant_scope_filters_by_current_company(): void
    {
        $companyA = Company::factory()->create(['slug' => 'company-a']);
        $companyB = Company::factory()->create(['slug' => 'company-b']);

        // Insérer des enregistrements bruts sans scope
        FakeProduct::withoutTenant()->create(['name' => 'Produit A', 'company_id' => $companyA->id]);
        FakeProduct::withoutTenant()->create(['name' => 'Produit B', 'company_id' => $companyB->id]);

        // Résoudre le tenant A
        App::instance('currentCompany', $companyA);

        $products = FakeProduct::all();

        $this->assertCount(1, $products);
        $this->assertEquals('Produit A', $products->first()->name);
    }

    /** @test */
    #[Test]
    public function tenant_scope_does_not_apply_without_resolved_tenant(): void
    {
        $companyA = Company::factory()->create(['slug' => 'company-a2']);
        $companyB = Company::factory()->create(['slug' => 'company-b2']);

        FakeProduct::withoutTenant()->create(['name' => 'Produit A', 'company_id' => $companyA->id]);
        FakeProduct::withoutTenant()->create(['name' => 'Produit B', 'company_id' => $companyB->id]);

        // Pas de tenant résolu → withoutTenant() retourne tout
        $products = FakeProduct::withoutTenant()->get();

        $this->assertCount(2, $products);
    }

    /** @test */
    #[Test]
    public function creating_a_model_auto_injects_company_id(): void
    {
        $company = Company::factory()->create(['slug' => 'auto-inject']);
        App::instance('currentCompany', $company);

        $product = FakeProduct::create(['name' => 'Auto produit']);

        $this->assertEquals($company->id, $product->company_id);
    }

    /** @test */
    #[Test]
    public function global_scope_is_registered_on_model(): void
    {
        $scopes = (new FakeProduct())->getGlobalScopes();

        $this->assertArrayHasKey(TenantScope::class, $scopes);
    }
}
