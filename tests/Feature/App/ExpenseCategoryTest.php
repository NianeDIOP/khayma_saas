<?php

namespace Tests\Feature\App;

use App\Models\Company;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExpenseCategoryTest extends TestCase
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
    private function tenantPut(string $path, array $data = []) { return $this->actingAs($this->owner)->put('/app' . $path . '?_tenant=test-co', $data); }
    private function tenantDelete(string $path) { return $this->actingAs($this->owner)->delete('/app' . $path . '?_tenant=test-co'); }

    #[Test]
    public function expense_categories_index_loads(): void
    {
        ExpenseCategory::factory()->create(['company_id' => $this->company->id]);
        $response = $this->tenantGet('/expense-categories');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/ExpenseCategories/Index')->has('categories.data', 1));
    }

    #[Test]
    public function expense_categories_scoped_to_tenant(): void
    {
        $other = Company::create(['name' => 'Other', 'slug' => 'other', 'email' => 'o@o.sn', 'subscription_status' => 'active', 'is_active' => true]);
        ExpenseCategory::factory()->create(['company_id' => $other->id]);
        ExpenseCategory::factory()->create(['company_id' => $this->company->id]);
        $response = $this->tenantGet('/expense-categories');
        $response->assertInertia(fn ($page) => $page->has('categories.data', 1));
    }

    #[Test]
    public function store_expense_category_with_valid_data(): void
    {
        $response = $this->tenantPost('/expense-categories', ['name' => 'Loyer']);
        $response->assertRedirect();
        $this->assertDatabaseHas('expense_categories', ['company_id' => $this->company->id, 'name' => 'Loyer']);
    }

    #[Test]
    public function store_expense_category_validates_required(): void
    {
        $response = $this->tenantPost('/expense-categories', ['name' => '']);
        $response->assertSessionHasErrors(['name']);
    }

    #[Test]
    public function store_expense_category_validates_unique_name(): void
    {
        ExpenseCategory::factory()->create(['company_id' => $this->company->id, 'name' => 'Loyer']);
        $response = $this->tenantPost('/expense-categories', ['name' => 'Loyer']);
        $response->assertSessionHasErrors(['name']);
    }

    #[Test]
    public function update_expense_category(): void
    {
        $cat = ExpenseCategory::factory()->create(['company_id' => $this->company->id, 'name' => 'Old']);
        $response = $this->tenantPut('/expense-categories/' . $cat->id, ['name' => 'New']);
        $response->assertRedirect();
        $this->assertDatabaseHas('expense_categories', ['id' => $cat->id, 'name' => 'New']);
    }

    #[Test]
    public function delete_expense_category(): void
    {
        $cat = ExpenseCategory::factory()->create(['company_id' => $this->company->id]);
        $response = $this->tenantDelete('/expense-categories/' . $cat->id);
        $response->assertRedirect();
        $this->assertDatabaseMissing('expense_categories', ['id' => $cat->id]);
    }

    #[Test]
    public function expense_categories_require_authentication(): void
    {
        $response = $this->get('/app/expense-categories?_tenant=test-co');
        $response->assertRedirect('/login');
    }
}
