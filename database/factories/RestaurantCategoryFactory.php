<?php

namespace Database\Factories;

use App\Models\RestaurantCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class RestaurantCategoryFactory extends Factory
{
    protected $model = RestaurantCategory::class;

    public function definition(): array
    {
        return [
            'name'       => fake()->unique()->word(),
            'sort_order' => fake()->numberBetween(0, 50),
            'is_active'  => true,
        ];
    }
}
