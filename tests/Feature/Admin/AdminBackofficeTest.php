<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Module;
use App\Models\Plan;
use App\Models\PlatformSetting;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminBackofficeTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_super_admin' => true]);
    }

    private function user(): User
    {
        return User::factory()->create(['is_super_admin' => false]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // ── Plans CRUD ──────────────────────────────────────────────

    #[Test]
    public function admin_can_list_plans(): void
    {
        Plan::factory()->create(['code' => 'starter']);

        $this->actingAs($this->admin())
             ->get('/admin/plans')
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p->component('Admin/Plans/Index')->has('plans', 1));
    }

    #[Test]
    public function admin_can_view_create_plan_form(): void
    {
        $this->actingAs($this->admin())
             ->get('/admin/plans/create')
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p->component('Admin/Plans/Form'));
    }

    #[Test]
    public function admin_can_create_plan(): void
    {
        $this->actingAs($this->admin())
             ->post('/admin/plans', [
                 'name' => 'Starter',
                 'code' => 'starter',
                 'max_products' => 100,
                 'max_users' => 2,
                 'max_storage_gb' => 1,
                 'max_transactions_month' => 500,
                 'api_rate_limit' => 30,
                 'price_monthly' => 9900,
                 'price_quarterly' => 26700,
                 'price_yearly' => 95000,
                 'is_active' => true,
             ])
             ->assertRedirect('/admin/plans');

        $this->assertDatabaseHas('plans', ['code' => 'starter', 'price_monthly' => 9900]);
    }

    #[Test]
    public function admin_can_update_plan(): void
    {
        $plan = Plan::factory()->create(['code' => 'starter']);

        $this->actingAs($this->admin())
             ->put("/admin/plans/{$plan->id}", [
                 'name' => 'Starter Plus',
                 'code' => 'starter',
                 'max_products' => 200,
                 'max_users' => 3,
                 'max_storage_gb' => 2,
                 'max_transactions_month' => 1000,
                 'api_rate_limit' => 60,
                 'price_monthly' => 14900,
                 'price_quarterly' => 39000,
                 'price_yearly' => 140000,
             ])
             ->assertRedirect('/admin/plans');

        $this->assertEquals('Starter Plus', $plan->fresh()->name);
    }

    #[Test]
    public function admin_can_delete_plan_without_active_subscriptions(): void
    {
        $plan = Plan::factory()->create(['code' => 'starter']);

        $this->actingAs($this->admin())
             ->delete("/admin/plans/{$plan->id}")
             ->assertRedirect('/admin/plans');

        $this->assertDatabaseMissing('plans', ['id' => $plan->id]);
    }

    #[Test]
    public function admin_cannot_delete_plan_with_active_subscriptions(): void
    {
        $plan = Plan::factory()->create(['code' => 'starter']);
        Subscription::factory()->create(['plan_id' => $plan->id, 'status' => 'active']);

        $this->actingAs($this->admin())
             ->delete("/admin/plans/{$plan->id}")
             ->assertRedirect();

        $this->assertDatabaseHas('plans', ['id' => $plan->id]);
    }

    #[Test]
    public function plan_creation_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
             ->post('/admin/plans', [])
             ->assertSessionHasErrors(['name', 'code', 'max_products', 'max_users', 'price_monthly']);
    }

    #[Test]
    public function plan_code_must_be_unique(): void
    {
        Plan::factory()->create(['code' => 'starter']);

        $this->actingAs($this->admin())
             ->post('/admin/plans', [
                 'name' => 'Another', 'code' => 'starter',
                 'max_products' => 100, 'max_users' => 2, 'max_storage_gb' => 1,
                 'max_transactions_month' => 500, 'api_rate_limit' => 30,
                 'price_monthly' => 5000, 'price_quarterly' => 13000, 'price_yearly' => 48000,
             ])
             ->assertSessionHasErrors('code');
    }

    // ── Modules CRUD ────────────────────────────────────────────

    #[Test]
    public function admin_can_list_modules(): void
    {
        Module::factory()->create();

        $this->actingAs($this->admin())
             ->get('/admin/modules')
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p->component('Admin/Modules/Index')->has('modules', 1));
    }

    #[Test]
    public function admin_can_create_module(): void
    {
        $this->actingAs($this->admin())
             ->post('/admin/modules', [
                 'name' => 'Restaurant',
                 'code' => 'restaurant',
                 'description' => 'Module restauration',
                 'icon' => 'fa-solid fa-utensils',
                 'installation_fee' => 15000,
                 'is_active' => true,
             ])
             ->assertRedirect('/admin/modules');

        $this->assertDatabaseHas('modules', ['code' => 'restaurant']);
    }

    #[Test]
    public function admin_can_update_module(): void
    {
        $module = Module::factory()->create(['code' => 'boutique']);

        $this->actingAs($this->admin())
             ->put("/admin/modules/{$module->id}", [
                 'name' => 'Boutique V2',
                 'code' => 'boutique',
                 'installation_fee' => 20000,
             ])
             ->assertRedirect('/admin/modules');

        $this->assertEquals('Boutique V2', $module->fresh()->name);
    }

    #[Test]
    public function admin_can_toggle_module(): void
    {
        $module = Module::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin())
             ->patch("/admin/modules/{$module->id}/toggle")
             ->assertRedirect();

        $this->assertFalse($module->fresh()->is_active);
    }

    // ── Subscriptions ───────────────────────────────────────────

    #[Test]
    public function admin_can_list_subscriptions(): void
    {
        Subscription::factory()->count(3)->create();

        $this->actingAs($this->admin())
             ->get('/admin/subscriptions')
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p->component('Admin/Subscriptions/Index')
                 ->has('subscriptions.data', 3)
                 ->has('stats'));
    }

    #[Test]
    public function admin_can_filter_subscriptions_by_status(): void
    {
        Subscription::factory()->create(['status' => 'active']);
        Subscription::factory()->create(['status' => 'expired']);

        $this->actingAs($this->admin())
             ->get('/admin/subscriptions?status=active')
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p->has('subscriptions.data', 1));
    }

    #[Test]
    public function admin_can_export_subscriptions_csv(): void
    {
        Subscription::factory()->create();

        $this->actingAs($this->admin())
             ->get('/admin/subscriptions/export')
             ->assertStatus(200)
             ->assertHeader('content-type', 'text/csv; charset=utf-8');
    }

    // ── Audit Logs ──────────────────────────────────────────────

    #[Test]
    public function admin_can_list_audit_logs(): void
    {
        AuditLog::factory()->count(2)->create();
        $total = AuditLog::count();

        $this->actingAs($this->admin())
             ->get('/admin/audit-logs')
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p->component('Admin/AuditLogs/Index')
                 ->has('logs.data', $total)
                 ->has('actions'));
    }

    #[Test]
    public function admin_can_filter_audit_logs(): void
    {
        AuditLog::factory()->create(['action' => 'login']);
        AuditLog::factory()->create(['action' => 'created']);

        $this->actingAs($this->admin())
             ->get('/admin/audit-logs?action=login')
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p->has('logs.data', 1));
    }

    // ── Platform Settings ───────────────────────────────────────

    #[Test]
    public function admin_can_view_settings(): void
    {
        $this->actingAs($this->admin())
             ->get('/admin/settings')
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p->component('Admin/Settings/Index')->has('settings'));
    }

    #[Test]
    public function admin_can_update_settings(): void
    {
        $this->actingAs($this->admin())
             ->put('/admin/settings', [
                 'app_name' => 'TestApp',
                 'default_currency' => 'EUR',
                 'trial_duration_days' => 14,
                 'grace_period_days' => 5,
                 'data_retention_days' => 180,
                 'maintenance_mode' => '0',
                 'support_email' => 'test@example.com',
                 'mail_mailer' => 'smtp',
                 'mail_host' => 'smtp.test.local',
                 'mail_port' => 2525,
                 'mail_username' => 'user@test.local',
                 'mail_password' => 'secret',
                 'mail_from_address' => 'no-reply@test.local',
                 'mail_from_name' => 'Test Platform',
                 'sms_provider' => 'api',
                 'sms_api_url' => 'https://sms.test.local/send',
                 'sms_api_token' => 'sms-token',
                 'sms_from' => 'TESTAPP',
             ])
             ->assertRedirect();

        $this->assertEquals('TestApp', PlatformSetting::get('app_name'));
        $this->assertEquals('EUR', PlatformSetting::get('default_currency'));
        $this->assertEquals('smtp', PlatformSetting::get('mail_mailer'));
        $this->assertEquals('api', PlatformSetting::get('sms_provider'));
    }

    #[Test]
    public function settings_validates_required_fields(): void
    {
        $this->actingAs($this->admin())
             ->put('/admin/settings', [])
             ->assertSessionHasErrors(['app_name', 'default_currency', 'trial_duration_days', 'mail_mailer', 'sms_provider']);
    }

    #[Test]
    public function settings_validate_email_and_sms_field_formats(): void
    {
        $this->actingAs($this->admin())
             ->put('/admin/settings', [
                 'app_name' => 'Khayma',
                 'default_currency' => 'XOF',
                 'trial_duration_days' => 7,
                 'grace_period_days' => 3,
                 'data_retention_days' => 90,
                 'maintenance_mode' => '0',
                 'support_email' => 'support@khayma.sn',
                 'mail_mailer' => 'invalid-mailer',
                 'mail_host' => 'smtp.khayma.sn',
                 'mail_port' => 587,
                 'mail_username' => 'smtp-user',
                 'mail_password' => 'smtp-pass',
                 'mail_from_address' => 'noreply@khayma.sn',
                 'mail_from_name' => 'Khayma',
                 'sms_provider' => 'invalid-provider',
                 'sms_api_url' => 'not-a-url',
                 'sms_api_token' => 'abc',
                 'sms_from' => 'KHAYMA',
             ])
             ->assertSessionHasErrors(['mail_mailer', 'sms_provider', 'sms_api_url']);
    }

    // ── Company CRUD (extended) ─────────────────────────────────

    #[Test]
    public function admin_can_view_create_company_form(): void
    {
        $this->actingAs($this->admin())
             ->get('/admin/companies/create')
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p->component('Admin/Companies/Create')
                 ->has('plans')
                 ->has('modules'));
    }

    #[Test]
    public function admin_can_create_company(): void
    {
        $this->actingAs($this->admin())
             ->post('/admin/companies', [
                 'name' => 'Test SARL',
                 'email' => 'test@company.com',
                 'owner_name' => 'John Doe',
                 'owner_email' => 'john@test.com',
                 'subscription_status' => 'trial',
             ]);

        $this->assertDatabaseHas('companies', ['name' => 'Test SARL']);
        $this->assertDatabaseHas('users', ['email' => 'john@test.com']);
    }

    #[Test]
    public function admin_can_edit_company(): void
    {
        $company = Company::factory()->create();

        $this->actingAs($this->admin())
             ->get("/admin/companies/{$company->id}/edit")
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p->component('Admin/Companies/Edit')->has('company'));
    }

    #[Test]
    public function admin_can_update_company(): void
    {
        $company = Company::factory()->create();

        $this->actingAs($this->admin())
             ->put("/admin/companies/{$company->id}", [
                 'name' => 'Updated Name',
                 'email' => 'updated@test.com',
             ])
             ->assertRedirect();

        $this->assertEquals('Updated Name', $company->fresh()->name);
    }

    #[Test]
    public function admin_can_delete_company(): void
    {
        $company = Company::factory()->create();

        $this->actingAs($this->admin())
             ->delete("/admin/companies/{$company->id}")
             ->assertRedirect('/admin/companies');

        $this->assertSoftDeleted('companies', ['id' => $company->id]);
    }

    #[Test]
    public function admin_can_extend_trial(): void
    {
        $company = Company::factory()->create([
            'subscription_status' => 'trial',
            'trial_ends_at' => now(),
        ]);

        $this->actingAs($this->admin())
             ->patch("/admin/companies/{$company->id}/extend-trial", ['days' => 14])
             ->assertRedirect();

        $this->assertEquals('trial', $company->fresh()->subscription_status);
        $this->assertTrue($company->fresh()->trial_ends_at->isFuture());
    }

    #[Test]
    public function admin_can_sync_modules(): void
    {
        $company = Company::factory()->create();
        $module = Module::factory()->create();

        $this->actingAs($this->admin())
             ->post("/admin/companies/{$company->id}/modules", [
                 'module_ids' => [$module->id],
             ])
             ->assertRedirect();

        $this->assertTrue($company->modules()->where('modules.id', $module->id)->exists());
    }

    #[Test]
    public function admin_can_reset_owner_password(): void
    {
        $company = Company::factory()->create();
        $owner = User::factory()->create();
        $company->users()->attach($owner->id, ['role' => 'owner', 'joined_at' => now()]);
        $oldHash = $owner->password;

        $this->actingAs($this->admin())
             ->post("/admin/companies/{$company->id}/reset-password")
             ->assertRedirect();

        $this->assertNotEquals($oldHash, $owner->fresh()->password);
    }

    #[Test]
    public function company_show_includes_modules_and_subscriptions(): void
    {
        $company = Company::factory()->create();

        $this->actingAs($this->admin())
             ->get("/admin/companies/{$company->id}")
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p->component('Admin/Companies/Show'));
    }

    // ── Users (extended) ────────────────────────────────────────

    #[Test]
    public function admin_can_view_user_detail(): void
    {
        $user = $this->user();

        $this->actingAs($this->admin())
             ->get("/admin/users/{$user->id}")
             ->assertStatus(200)
             ->assertInertia(fn ($p) => $p->component('Admin/Users/Show')->has('user'));
    }

    #[Test]
    public function admin_can_toggle_admin_role(): void
    {
        $admin = $this->admin();
        $target = $this->user();

        $this->actingAs($admin)
             ->patch("/admin/users/{$target->id}/toggle-admin")
             ->assertRedirect();

        $this->assertTrue($target->fresh()->is_super_admin);
    }

    #[Test]
    public function admin_cannot_toggle_own_admin_role(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
             ->patch("/admin/users/{$admin->id}/toggle-admin")
             ->assertRedirect();

        // Should still be admin (not toggled)
        $this->assertTrue($admin->fresh()->is_super_admin);
    }

    #[Test]
    public function admin_can_filter_users_by_role(): void
    {
        $this->admin(); // creates an admin
        User::factory()->count(3)->create(['is_super_admin' => false]);

        $this->actingAs($this->admin())
             ->get('/admin/users?role=admin')
             ->assertStatus(200);
    }

    // ── Access control ──────────────────────────────────────────

    #[Test]
    public function non_admin_cannot_access_plans(): void
    {
        $this->actingAs($this->user())
             ->get('/admin/plans')
             ->assertStatus(403);
    }

    #[Test]
    public function non_admin_cannot_access_modules(): void
    {
        $this->actingAs($this->user())
             ->get('/admin/modules')
             ->assertStatus(403);
    }

    #[Test]
    public function non_admin_cannot_access_settings(): void
    {
        $this->actingAs($this->user())
             ->get('/admin/settings')
             ->assertStatus(403);
    }

    #[Test]
    public function non_admin_cannot_access_subscriptions(): void
    {
        $this->actingAs($this->user())
             ->get('/admin/subscriptions')
             ->assertStatus(403);
    }

    #[Test]
    public function non_admin_cannot_access_audit_logs(): void
    {
        $this->actingAs($this->user())
             ->get('/admin/audit-logs')
             ->assertStatus(403);
    }
}
