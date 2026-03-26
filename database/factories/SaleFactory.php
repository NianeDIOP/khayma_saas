<?php

namespace Database\Factories;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Factories\Factory;

class SaleFactory extends Factory
{
    protected $model = Sale::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 500, 100000);
        $discount = fake()->randomFloat(2, 0, $subtotal * 0.1);
        $tax      = round(($subtotal - $discount) * 0.18, 2);
        $total    = round($subtotal - $discount + $tax, 2);

        return [
            'reference'       => 'VNT-' . strtoupper(fake()->unique()->bothify('####??')),
            'type'            => fake()->randomElement(['counter', 'delivery']),
            'status'          => 'completed',
            'subtotal'        => $subtotal,
            'discount_amount' => $discount,
            'tax_amount'      => $tax,
            'total'           => $total,
            'payment_status'  => 'paid',
        ];
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => 'cancelled']);
    }
}
