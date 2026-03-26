<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'reference'      => 'CMD-' . now()->format('Ymd') . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'type'           => fake()->randomElement(['table', 'takeaway', 'delivery']),
            'status'         => 'completed',
            'subtotal'       => fake()->numberBetween(2000, 50000),
            'discount_amount'=> 0,
            'total'          => fake()->numberBetween(2000, 50000),
            'payment_method' => fake()->randomElement(['cash', 'wave', 'om', 'card']),
            'payment_status' => 'paid',
            'paid_at'        => now(),
        ];
    }

    public function table(): static
    {
        return $this->state(fn () => [
            'type'         => 'table',
            'table_number' => (string) fake()->numberBetween(1, 20),
        ]);
    }

    public function delivery(): static
    {
        return $this->state(fn () => [
            'type'             => 'delivery',
            'delivery_address' => fake()->address(),
            'delivery_person'  => fake()->name(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => [
            'status'        => 'cancelled',
            'cancel_reason' => fake()->sentence(),
        ]);
    }
}
