<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'company_id'          => Company::factory(),
            'name'                => fake()->name(),
            'phone'               => fake()->numerify('+221 7# ### ## ##'),
            'email'               => fake()->unique()->safeEmail(),
            'address'             => fake()->address(),
            'nif'                 => null,
            'category'            => fake()->randomElement(['normal', 'vip', 'professional']),
            'loyalty_points'      => fake()->numberBetween(0, 500),
            'outstanding_balance' => 0,
        ];
    }

    public function vip(): static
    {
        return $this->state(['category' => 'vip']);
    }

    public function professional(): static
    {
        return $this->state(['category' => 'professional']);
    }
}
