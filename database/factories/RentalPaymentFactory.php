<?php

namespace Database\Factories;

use App\Models\RentalPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class RentalPaymentFactory extends Factory
{
    protected $model = RentalPayment::class;

    public function definition(): array
    {
        return [
            'amount'       => fake()->randomFloat(0, 10000, 500000),
            'amount_paid'  => 0,
            'due_date'     => now()->addDays(fake()->numberBetween(-15, 30))->toDateString(),
            'payment_date' => null,
            'status'       => 'pending',
            'method'       => null,
            'reference'    => null,
            'notes'        => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $attributes['amount'] ?? 100000;
            return [
                'amount_paid'  => $amount,
                'status'       => 'paid',
                'payment_date' => now()->toDateString(),
                'method'       => fake()->randomElement(['cash', 'wave', 'om', 'bank_transfer']),
            ];
        });
    }

    public function partial(): static
    {
        return $this->state(function (array $attributes) {
            $amount = $attributes['amount'] ?? 100000;
            return [
                'amount_paid'  => round($amount * 0.5),
                'status'       => 'partial',
                'payment_date' => now()->toDateString(),
                'method'       => 'cash',
            ];
        });
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'status'   => 'overdue',
            'due_date' => now()->subDays(5)->toDateString(),
        ]);
    }
}
