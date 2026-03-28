<?php

namespace Tests\Feature\App;

use App\Models\AppNotification;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->company = Company::create([
            'name' => 'Notif Co', 'slug' => 'notif-co', 'email' => 'n@co.sn',
            'subscription_status' => 'trial', 'trial_ends_at' => now()->addDays(14), 'is_active' => true,
        ]);
        $this->owner = User::factory()->create(['is_super_admin' => false]);
        $this->company->users()->attach($this->owner->id, ['role' => 'owner', 'joined_at' => now()]);
    }

    private function tenantGet(string $path) { return $this->actingAs($this->owner)->get('/app' . $path . '?_tenant=notif-co'); }
    private function tenantPost(string $path, array $data = []) { return $this->actingAs($this->owner)->post('/app' . $path . '?_tenant=notif-co', $data); }

    #[Test]
    public function notifications_index_loads(): void
    {
        $response = $this->tenantGet('/notifications');
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('App/Notifications/Index'));
    }

    #[Test]
    public function unread_count_returns_json(): void
    {
        AppNotification::send($this->owner->id, 'Test', 'Body', 'info', $this->company->id);

        $response = $this->tenantGet('/notifications/unread-count');
        $response->assertOk()->assertJson(['count' => 1]);
    }

    #[Test]
    public function mark_notification_as_read(): void
    {
        $notif = AppNotification::send($this->owner->id, 'Test', 'Body', 'info', $this->company->id);

        $this->tenantPost("/notifications/{$notif->id}/read");

        $this->assertTrue($notif->fresh()->is_read);
    }

    #[Test]
    public function mark_all_notifications_read(): void
    {
        AppNotification::send($this->owner->id, 'A', 'Body', 'info', $this->company->id);
        AppNotification::send($this->owner->id, 'B', 'Body', 'alert', $this->company->id);

        $this->tenantPost('/notifications/read-all');

        $this->assertEquals(0, AppNotification::where('user_id', $this->owner->id)->whereNull('read_at')->count());
    }

    #[Test]
    public function notifications_scoped_to_user(): void
    {
        $other = User::factory()->create();
        $this->company->users()->attach($other->id, ['role' => 'staff', 'joined_at' => now()]);

        AppNotification::send($other->id, 'Other', 'Not mine', 'info', $this->company->id);
        AppNotification::send($this->owner->id, 'Mine', 'Mine', 'info', $this->company->id);

        $response = $this->tenantGet('/notifications');
        $response->assertInertia(fn ($page) => $page->has('notifications.data', 1));
    }
}
