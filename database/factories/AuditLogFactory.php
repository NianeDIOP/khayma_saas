<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'company_id'  => Company::factory(),
            'user_id'     => User::factory(),
            'action'      => $this->faker->randomElement(['created', 'updated', 'deleted', 'login']),
            'model_type'  => 'App\\Models\\Company',
            'model_id'    => $this->faker->randomNumber(3),
            'old_values'  => null,
            'new_values'  => null,
            'ip_address'  => $this->faker->ipv4(),
            'user_agent'  => $this->faker->userAgent(),
            'created_at'  => now(),
        ];
    }
}
