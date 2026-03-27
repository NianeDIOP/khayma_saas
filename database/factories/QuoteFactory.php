<?php

namespace Database\Factories;

use App\Models\Quote;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 1000, 200000);
        $discount = fake()->randomFloat(2, 0, $subtotal * 0.1);
        $total    = round($subtotal - $discount, 2);

        return [
            'reference'       => 'DEV-' . now()->format('Ymd') . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'status'          => 'draft',
            'subtotal'        => $subtotal,
            'discount_amount' => $discount,
            'total'           => $total,
            'valid_until'     => now()->addDays(30),
            'notes'           => null,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn () => ['status' => 'sent']);
    }

    public function accepted(): static
    {
        return $this->state(fn () => ['status' => 'accepted']);
    }
}
