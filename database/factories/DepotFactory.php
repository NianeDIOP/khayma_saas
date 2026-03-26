<?php

namespace Database\Factories;

use App\Models\Depot;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepotFactory extends Factory
{
    protected $model = Depot::class;

    public function definition(): array
    {
        return [
            'name'       => fake()->randomElement(['Magasin Principal', 'Réserve', 'Entrepôt', 'Boutique']) . ' ' . fake()->numberBetween(1, 9),
            'address'    => fake()->optional()->address(),
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn () => ['is_default' => true]);
    }
}
