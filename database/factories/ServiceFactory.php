<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $start = fake()->time('H:i');
        return [
            'name'       => fake()->unique()->randomElement(['Matin', 'Midi', 'Soir']),
            'start_time' => $start,
            'end_time'   => now()->createFromFormat('H:i', $start)->addHours(4)->format('H:i'),
            'is_active'  => true,
        ];
    }
}
