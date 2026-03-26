<?php

namespace Database\Factories;

use App\Models\Plan;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        $code = $this->faker->unique()->randomElement(['starter', 'business', 'pro', 'enterprise', 'premium']);

        return [
            'name'                   => ucfirst($code),
            'code'                   => $code,
            'max_products'           => $this->faker->randomElement([100, 300, 0]),
            'max_users'              => $this->faker->numberBetween(2, 20),
            'max_storage_gb'         => $this->faker->randomElement([1, 5, 10, 50]),
            'max_transactions_month' => $this->faker->randomElement([500, 1000, 5000, 0]),
            'api_rate_limit'         => $this->faker->randomElement([30, 60, 120]),
            'price_monthly'          => $this->faker->numberBetween(5000, 100000),
            'price_quarterly'        => $this->faker->numberBetween(13000, 270000),
            'price_yearly'           => $this->faker->numberBetween(48000, 960000),
            'is_active'              => true,
        ];
    }
}
