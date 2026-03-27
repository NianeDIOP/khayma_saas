<?php

namespace Database\Factories;

use App\Models\RentalAsset;
use Illuminate\Database\Eloquent\Factories\Factory;

class RentalAssetFactory extends Factory
{
    protected $model = RentalAsset::class;

    public function definition(): array
    {
        $type = fake()->randomElement(['vehicle', 'real_estate', 'equipment', 'other']);

        return [
            'name'             => fake()->words(3, true),
            'description'      => fake()->optional()->sentence(),
            'type'             => $type,
            'daily_rate'       => fake()->optional()->randomFloat(0, 5000, 50000),
            'monthly_rate'     => fake()->optional()->randomFloat(0, 50000, 500000),
            'status'           => 'available',
            'characteristics'  => null,
            'images'           => null,
            'documents'        => null,
            'inspection_notes' => null,
            'is_active'        => true,
        ];
    }

    public function rented(): static
    {
        return $this->state(fn () => ['status' => 'rented']);
    }

    public function maintenance(): static
    {
        return $this->state(fn () => ['status' => 'maintenance']);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
