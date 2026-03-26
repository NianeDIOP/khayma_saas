<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $purchasePrice = fake()->randomFloat(2, 100, 50000);

        return [
            'name'            => fake()->words(rand(1, 3), true),
            'description'     => fake()->optional()->sentence(),
            'purchase_price'  => $purchasePrice,
            'selling_price'   => round($purchasePrice * fake()->randomFloat(2, 1.1, 2.5), 2),
            'barcode'         => fake()->optional(0.7)->ean13(),
            'min_stock_alert' => fake()->numberBetween(0, 50),
            'is_active'       => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
