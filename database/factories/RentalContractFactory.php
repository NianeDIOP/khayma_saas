<?php

namespace Database\Factories;

use App\Models\RentalContract;
use Illuminate\Database\Eloquent\Factories\Factory;

class RentalContractFactory extends Factory
{
    protected $model = RentalContract::class;

    public function definition(): array
    {
        $start = now()->subDays(fake()->numberBetween(0, 30));
        $end   = $start->copy()->addMonths(fake()->numberBetween(1, 12));

        return [
            'reference'         => 'LOC-' . str_pad(fake()->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'start_date'        => $start->toDateString(),
            'end_date'          => $end->toDateString(),
            'total_amount'      => fake()->randomFloat(0, 50000, 2000000),
            'deposit_amount'    => fake()->randomFloat(0, 10000, 200000),
            'deposit_returned'  => false,
            'deposit_returned_amount' => 0,
            'payment_frequency' => fake()->randomElement(['monthly', 'quarterly', 'yearly', 'one_time']),
            'status'            => 'active',
            'conditions'        => null,
            'inspection_start'  => null,
            'inspection_end'    => null,
            'notes'             => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed']);
    }

    public function overdue(): static
    {
        return $this->state(fn () => ['status' => 'overdue']);
    }

    public function renewed(): static
    {
        return $this->state(fn () => ['status' => 'renewed']);
    }
}
