<?php

namespace Database\Factories;

use App\Models\OrderItem;
use App\Models\Dish;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $qty   = fake()->numberBetween(1, 5);
        $price = fake()->numberBetween(500, 10000);
        return [
            'quantity'   => $qty,
            'unit_price' => $price,
            'total'      => $qty * $price,
        ];
    }
}
