<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        $name = $this->faker->company();

        return [
            'name'                => $name,
            'slug'                => Str::slug($name) . '-' . $this->faker->unique()->numerify('###'),
            'email'               => $this->faker->unique()->companyEmail(),
            'phone'               => $this->faker->phoneNumber(),
            'address'             => $this->faker->address(),
            'currency'            => 'XOF',
            'timezone'            => 'Africa/Dakar',
            'subscription_status' => 'active',
            'trial_ends_at'       => null,
            'is_active'           => true,
        ];
    }

    /** Entreprise en période d'essai valide */
    public function onTrial(int $days = 7): static
    {
        return $this->state([
            'subscription_status' => 'trial',
            'trial_ends_at'       => now()->addDays($days),
        ]);
    }

    /** Essai expiré */
    public function trialExpired(): static
    {
        return $this->state([
            'subscription_status' => 'trial',
            'trial_ends_at'       => now()->subDay(),
        ]);
    }

    /** Abonnement suspendu */
    public function suspended(): static
    {
        return $this->state([
            'subscription_status' => 'suspended',
            'is_active'           => true,
        ]);
    }

    /** Période de grâce */
    public function gracePeriod(): static
    {
        return $this->state([
            'subscription_status' => 'grace_period',
        ]);
    }

    /** Entreprise inactive */
    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
