<?php

namespace Database\Factories;

use App\Models\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'amount'      => fake()->randomFloat(2, 500, 50000),
            'description' => fake()->sentence(),
            'date'        => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
