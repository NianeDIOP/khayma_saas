<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'name'                   => fake()->words(2, true),
            'sku'                    => 'VAR-' . fake()->unique()->numerify('######'),
            'barcode'                => fake()->optional(0.5)->ean13(),
            'price_override'         => fake()->optional(0.5)->randomFloat(2, 500, 50000),
            'purchase_price_override' => fake()->optional(0.3)->randomFloat(2, 200, 30000),
            'attributes'             => ['couleur' => fake()->safeColorName(), 'taille' => fake()->randomElement(['S', 'M', 'L', 'XL'])],
            'is_active'              => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
