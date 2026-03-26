<?php

namespace Database\Factories;

use App\Models\Dish;
use App\Models\RestaurantCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class DishFactory extends Factory
{
    protected $model = Dish::class;

    public function definition(): array
    {
        return [
            'name'              => fake()->unique()->words(2, true),
            'description'       => fake()->optional()->sentence(),
            'price'             => fake()->numberBetween(500, 15000),
            'is_available'      => true,
            'available_morning' => fake()->boolean(30),
            'available_noon'    => true,
            'available_evening' => fake()->boolean(70),
            'is_additional'     => false,
            'sort_order'        => fake()->numberBetween(0, 50),
        ];
    }

    public function extra(): static
    {
        return $this->state(fn () => ['is_additional' => true]);
    }

    public function withPromo(): static
    {
        return $this->state(fn () => [
            'promo_price' => fake()->numberBetween(200, 5000),
            'promo_start' => now()->subDay()->toDateString(),
            'promo_end'   => now()->addDays(7)->toDateString(),
        ]);
    }
}
