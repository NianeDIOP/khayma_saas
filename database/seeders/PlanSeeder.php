<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'                    => 'Starter',
                'code'                    => 'starter',
                'max_products'            => 200,
                'max_users'               => 3,
                'max_storage_gb'          => 2,
                'max_transactions_month'  => 1000,
                'api_rate_limit'          => 100,
                'price_monthly'           => 15000,
                'price_quarterly'         => 40000,
                'price_yearly'            => 150000,
                'is_active'               => true,
            ],
            [
                'name'                    => 'Pro',
                'code'                    => 'pro',
                'max_products'            => 2000,
                'max_users'               => 10,
                'max_storage_gb'          => 10,
                'max_transactions_month'  => 10000,
                'api_rate_limit'          => 500,
                'price_monthly'           => 30000,
                'price_quarterly'         => 80000,
                'price_yearly'            => 300000,
                'is_active'               => true,
            ],
            [
                'name'                    => 'Premium',
                'code'                    => 'premium',
                'max_products'            => 999999,
                'max_users'               => 50,
                'max_storage_gb'          => 50,
                'max_transactions_month'  => 999999,
                'api_rate_limit'          => 2000,
                'price_monthly'           => 55000,
                'price_quarterly'         => 150000,
                'price_yearly'            => 550000,
                'is_active'               => true,
            ],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['code' => $plan['code']], $plan);
        }

        $this->command->info('✅ Plans créés : Starter / Pro / Premium');
    }
}
