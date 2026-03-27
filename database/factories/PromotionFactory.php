<?php

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['percentage', 'fixed']);

        return [
            'name'       => 'Promo ' . fake()->words(2, true),
            'type'       => $type,
            'value'      => $type === 'percentage' ? fake()->numberBetween(5, 50) : fake()->numberBetween(100, 5000),
            'start_date' => now()->subDays(5),
            'end_date'   => now()->addDays(25),
            'is_active'  => true,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'start_date' => now()->subDays(30),
            'end_date'   => now()->subDays(1),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
