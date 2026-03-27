<?php

namespace Database\Factories;

use App\Models\SupplierReturn;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierReturnFactory extends Factory
{
    protected $model = SupplierReturn::class;

    public function definition(): array
    {
        return [
            'quantity' => fake()->numberBetween(1, 50),
            'reason'   => fake()->optional()->sentence(),
            'status'   => 'pending',
            'date'     => now(),
        ];
    }
}
