<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Module;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        // ── Entreprise de test ────────────────────────────────
        $company = Company::updateOrCreate(
            ['slug' => 'diallo-store'],
            [
                'name'                => 'Diallo Store',
                'slug'                => 'diallo-store',
                'email'               => 'contact@diallo-store.sn',
                'phone'               => '+221771234567',
                'address'             => 'Dakar, Sénégal',
                'sector'              => 'boutique',
                'currency'            => 'XOF',
                'timezone'            => 'Africa/Dakar',
                'subscription_status' => 'trial',
                'trial_ends_at'       => now()->addDays(30),
                'is_active'           => true,
            ]
        );

        // ── User owner ────────────────────────────────────────
        $owner = User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name'     => 'Mamadou Diallo',
                'email'    => 'admin@test.com',
                'password' => Hash::make('password'),
            ]
        );

        // Associer à l'entreprise
        $company->users()->syncWithoutDetaching([
            $owner->id => ['role' => 'owner', 'joined_at' => now()],
        ]);

        // Donner le rôle Spatie
        if (!$owner->hasRole('owner')) {
            $owner->assignRole('owner');
        }

        // ── Abonnement trial ──────────────────────────────────
        $plan = Plan::where('code', 'pro')->first();
        if ($plan) {
            Subscription::updateOrCreate(
                ['company_id' => $company->id, 'status' => 'active'],
                [
                    'plan_id'        => $plan->id,
                    'billing_period' => 'monthly',
                    'amount_paid'    => 0,
                    'starts_at'      => now(),
                    'ends_at'        => now()->addDays(30),
                ]
            );
        }

        // ── Activer module boutique ───────────────────────────
        $module = Module::where('code', 'boutique')->first();
        if ($module) {
            $company->modules()->syncWithoutDetaching([
                $module->id => ['activated_at' => now(), 'activated_by' => $owner->id],
            ]);
        }

        $this->command->info('✅ Entreprise de test créée : Diallo Store (admin@test.com / password)');
    }
}
