<?php

namespace Database\Factories;

use App\Models\SupplierPayment;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierPaymentFactory extends Factory
{
    protected $model = SupplierPayment::class;

    public function definition(): array
    {
        return [
            'amount'    => fake()->randomFloat(2, 1000, 100000),
            'method'    => fake()->randomElement(['cash', 'wave', 'om', 'bank']),
            'reference' => fake()->optional()->bothify('PAY-####'),
            'notes'     => null,
            'date'      => now(),
        ];
    }
}
