<?php

namespace Database\Factories;

use App\Models\DepotTransfer;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepotTransferFactory extends Factory
{
    protected $model = DepotTransfer::class;

    public function definition(): array
    {
        return [
            'reference' => 'TRF-' . now()->format('Ymd') . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'status'    => 'pending',
            'notes'     => fake()->optional()->sentence(),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn () => ['status' => 'completed']);
    }
}
