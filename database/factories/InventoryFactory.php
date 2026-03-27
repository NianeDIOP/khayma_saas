<?php

namespace Database\Factories;

use App\Models\Inventory;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryFactory extends Factory
{
    protected $model = Inventory::class;

    public function definition(): array
    {
        return [
            'reference'    => 'INV-' . now()->format('Ymd') . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'status'       => 'in_progress',
            'notes'        => null,
            'validated_at' => null,
        ];
    }

    public function validated(): static
    {
        return $this->state(fn () => ['status' => 'validated', 'validated_at' => now()]);
    }
}
