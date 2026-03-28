<?php

namespace Tests\Feature\App;

use App\Mail\WelcomeMail;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeamNotificationTest extends TestCase
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

        Role::findOrCreate('manager', 'web');
    }

    #[Test]
    public function creating_team_member_queues_welcome_email(): void
    {
        Mail::fake();

        $response = $this->actingAs($this->owner)
            ->post('/app/team?_tenant=test-co', [
                'name'        => 'Nouveau Manager',
                'email'       => 'manager@test.sn',
                'phone'       => '+221776667777',
                'password'    => 'Password123!',
                'role'        => 'manager',
                'permissions' => ['dashboard', 'sales'],
            ]);

        $response->assertRedirect();

        Mail::assertQueued(WelcomeMail::class, fn (WelcomeMail $mail) =>
            (int) $mail->company->id === (int) $this->company->id
            && $mail->user->email === 'manager@test.sn'
        );
    }
}
