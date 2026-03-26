<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'company_id'          => Company::factory(),
            'name'                => fake()->company(),
            'phone'               => fake()->numerify('+221 3# ### ## ##'),
            'email'               => fake()->unique()->safeEmail(),
            'address'             => fake()->address(),
            'ninea'               => null,
            'rib'                 => null,
            'rating'              => null,
            'outstanding_balance' => 0,
            'notes'               => null,
        ];
    }
}
