<?php

namespace Database\Factories;

use App\Models\CashSession;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CashSessionFactory extends Factory
{
    protected $model = CashSession::class;

    public function definition(): array
    {
        return [
            'opened_at'      => now(),
            'opening_amount' => fake()->numberBetween(0, 50000),
        ];
    }

    public function closed(): static
    {
        return $this->state(fn () => [
            'closed_at'       => now()->addHours(8),
            'closing_amount'  => fake()->numberBetween(10000, 200000),
            'expected_amount' => fake()->numberBetween(10000, 200000),
        ]);
    }
}
