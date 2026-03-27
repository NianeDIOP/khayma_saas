<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 5000, 500000);
        $total    = $subtotal;

        return [
            'reference'     => 'BC-' . now()->format('Ymd') . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'status'        => 'draft',
            'subtotal'      => $subtotal,
            'total'         => $total,
            'expected_date' => now()->addDays(fake()->numberBetween(3, 30)),
            'notes'         => null,
        ];
    }

    public function sent(): static
    {
        return $this->state(fn () => ['status' => 'sent']);
    }

    public function received(): static
    {
        return $this->state(fn () => ['status' => 'received', 'received_at' => now()]);
    }
}
