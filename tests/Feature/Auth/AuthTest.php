<?php

namespace Tests\Feature\Auth;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    // ── Login ──────────────────────────────────────────────────────────────

    #[Test]
    public function login_page_is_accessible_as_guest(): void
    {
        $this->get(route('login'))
             ->assertStatus(200)
             ->assertInertia(fn ($page) => $page->component('Auth/Login'));
    }

    #[Test]
    public function authenticated_user_is_redirected_from_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get(route('login'))
             ->assertRedirect();
    }

    #[Test]
    public function user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email'    => 'test@khayma.com',
            'password' => bcrypt('Secret123'),
        ]);

        $this->post(route('login.store'), [
            'email'    => 'test@khayma.com',
            'password' => 'Secret123',
        ])->assertRedirect();

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email'    => 'test@khayma.com',
            'password' => bcrypt('Secret123'),
        ]);

        $this->post(route('login.store'), [
            'email'    => 'test@khayma.com',
            'password' => 'mauvais',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    #[Test]
    public function user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->post(route('logout'))
             ->assertRedirect(route('home'));

        $this->assertGuest();
    }

    // ── Register ───────────────────────────────────────────────────────────

    #[Test]
    public function register_page_is_accessible_as_guest(): void
    {
        $this->get(route('register'))
             ->assertStatus(200)
             ->assertInertia(fn ($page) => $page->component('Auth/Register'));
    }

    #[Test]
    public function user_can_register_and_company_is_created(): void
    {
        $this->post(route('register.store'), [
            'name'                 => 'Aminata Diallo',
            'email'                => 'aminata@test.com',
            'password'             => 'Secret123',
            'password_confirmation'=> 'Secret123',
            'company_name'         => 'Restaurant Awa',
            'sector'               => 'restaurant',
            'phone'                => '+221 77 000 00 00',
        ])->assertRedirect();

        // Utilisateur créé
        $this->assertDatabaseHas('users', ['email' => 'aminata@test.com']);

        // Entreprise créée avec slug + trial
        $this->assertDatabaseHas('companies', [
            'name'                => 'Restaurant Awa',
            'slug'                => 'restaurant-awa',
            'sector'              => 'restaurant',
            'subscription_status' => 'trial',
        ]);

        // Lien user-company créé avec rôle owner
        $user    = User::where('email', 'aminata@test.com')->first();
        $company = Company::where('slug', 'restaurant-awa')->first();

        $this->assertDatabaseHas('company_users', [
            'user_id'    => $user->id,
            'company_id' => $company->id,
            'role'       => 'owner',
        ]);

        $this->assertAuthenticatedAs($user);
    }

    #[Test]
    public function registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existe@test.com']);

        $this->post(route('register.store'), [
            'name'                  => 'Test',
            'email'                 => 'existe@test.com',
            'password'              => 'Secret123',
            'password_confirmation' => 'Secret123',
            'company_name'          => 'Ma Boutique',
            'sector'                => 'boutique',
        ])->assertSessionHasErrors('email');
    }

    #[Test]
    public function registration_fails_with_invalid_sector(): void
    {
        $this->post(route('register.store'), [
            'name'                  => 'Test',
            'email'                 => 'nouveau@test.com',
            'password'              => 'Secret123',
            'password_confirmation' => 'Secret123',
            'company_name'          => 'Ma Société',
            'sector'                => 'pharmacie', // invalide
        ])->assertSessionHasErrors('sector');
    }

    #[Test]
    public function trial_ends_at_is_set_to_14_days(): void
    {
        $this->post(route('register.store'), [
            'name'                  => 'Oumar Ba',
            'email'                 => 'oumar@test.com',
            'password'              => 'Secret123',
            'password_confirmation' => 'Secret123',
            'company_name'          => 'Quincaillerie Demba',
            'sector'                => 'quincaillerie',
        ]);

        $company = Company::where('slug', 'quincaillerie-demba')->first();

        $this->assertNotNull($company->trial_ends_at);
        $this->assertTrue($company->trial_ends_at->isFuture());
        // trial_ends_at doit être dans ~14 jours (tolérance : ±1 jour)
        $this->assertEqualsWithDelta(14, now()->diffInDays($company->trial_ends_at), 1);
    }
}
