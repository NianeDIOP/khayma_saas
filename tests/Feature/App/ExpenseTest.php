<?php

namespace Tests\Feature\App;

use App\Models\Company;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ExpenseTest extends TestCase
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
    public function expenses_index_loads(): void
    {
        $response = $this->tenantGet('/expenses');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Expenses/Index'));
    }

    #[Test]
    public function expenses_scoped_to_tenant(): void
    {
        $other = Company::create(['name' => 'Other', 'slug' => 'other', 'email' => 'o@o.sn', 'subscription_status' => 'active', 'is_active' => true]);
        Expense::factory()->create(['company_id' => $other->id, 'user_id' => $this->owner->id]);
        Expense::factory()->create(['company_id' => $this->company->id, 'user_id' => $this->owner->id]);
        $response = $this->tenantGet('/expenses');
        $response->assertInertia(fn ($page) => $page->has('expenses.data', 1));
    }

    #[Test]
    public function create_expense_form_loads(): void
    {
        $response = $this->tenantGet('/expenses/create');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Expenses/Form'));
    }

    #[Test]
    public function store_expense_with_valid_data(): void
    {
        $cat = ExpenseCategory::factory()->create(['company_id' => $this->company->id]);
        $response = $this->tenantPost('/expenses', [
            'expense_category_id' => $cat->id,
            'amount'              => 25000,
            'description'         => 'Facture eau',
            'date'                => '2025-03-15',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'company_id' => $this->company->id,
            'amount'     => 25000,
            'user_id'    => $this->owner->id,
        ]);
    }

    #[Test]
    public function store_expense_validates_required(): void
    {
        $response = $this->tenantPost('/expenses', ['amount' => '', 'date' => '']);
        $response->assertSessionHasErrors(['amount', 'date']);
    }

    #[Test]
    public function show_expense_displays_details(): void
    {
        $expense = Expense::factory()->create(['company_id' => $this->company->id, 'user_id' => $this->owner->id]);
        $response = $this->tenantGet('/expenses/' . $expense->id);
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Expenses/Show'));
    }

    #[Test]
    public function update_expense(): void
    {
        $expense = Expense::factory()->create(['company_id' => $this->company->id, 'user_id' => $this->owner->id, 'amount' => 1000]);
        $response = $this->tenantPut('/expenses/' . $expense->id, [
            'amount' => 2000,
            'date'   => '2025-03-20',
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', ['id' => $expense->id, 'amount' => 2000]);
    }

    #[Test]
    public function delete_expense_soft_deletes(): void
    {
        $expense = Expense::factory()->create(['company_id' => $this->company->id, 'user_id' => $this->owner->id]);
        $response = $this->tenantDelete('/expenses/' . $expense->id);
        $response->assertRedirect();
        $this->assertSoftDeleted('expenses', ['id' => $expense->id]);
    }

    #[Test]
    public function expenses_require_authentication(): void
    {
        $response = $this->get('/app/expenses?_tenant=test-co');
        $response->assertRedirect('/login');
    }
}
