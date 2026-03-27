<?php

namespace Database\Factories;

use App\Models\LoyaltyConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoyaltyConfigFactory extends Factory
{
    protected $model = LoyaltyConfig::class;

    public function definition(): array
    {
        return [
            'points_per_amount'    => 1,
            'amount_per_point'     => 1000,
            'redemption_threshold' => 100,
            'redemption_value'     => 500,
            'is_active'            => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
