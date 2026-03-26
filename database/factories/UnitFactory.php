<?php

namespace Database\Factories;

use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        return [
            'name'              => fake()->unique()->randomElement(['Pièce', 'Kilogramme', 'Litre', 'Mètre', 'Sac', 'Carton', 'Tonne']),
            'symbol'            => fake()->unique()->randomElement(['pcs', 'kg', 'L', 'm', 'sac', 'ctn', 't']),
            'conversion_factor' => 1,
        ];
    }
}
