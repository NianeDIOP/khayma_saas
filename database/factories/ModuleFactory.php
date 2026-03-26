<?php

namespace Database\Factories;

use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleFactory extends Factory
{
    protected $model = Module::class;

    public function definition(): array
    {
        return [
            'name'             => $this->faker->unique()->word() . ' Module',
            'code'             => $this->faker->unique()->slug(1),
            'description'      => $this->faker->sentence(),
            'icon'             => 'fa-solid fa-puzzle-piece',
            'installation_fee' => $this->faker->randomElement([0, 5000, 10000, 25000]),
            'is_active'        => true,
        ];
    }
}
