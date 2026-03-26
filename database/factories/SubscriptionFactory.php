<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Module;
use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'company_id'        => Company::factory(),
            'plan_id'           => Plan::factory(),
            'module_id'         => null,
            'status'            => 'active',
            'billing_period'    => $this->faker->randomElement(['monthly', 'quarterly', 'yearly']),
            'amount_paid'       => $this->faker->numberBetween(5000, 100000),
            'payment_reference' => 'PAY-' . $this->faker->unique()->numerify('######'),
            'starts_at'         => now(),
            'ends_at'           => now()->addMonth(),
        ];
    }
}
