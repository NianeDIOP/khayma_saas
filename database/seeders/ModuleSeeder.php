<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'name'             => 'Restaurant',
                'code'             => 'restaurant',
                'description'      => 'Gestion des commandes, sessions, menu, caisse restaurant.',
                'icon'             => 'fa-solid fa-utensils',
                'installation_fee' => 0,
                'is_active'        => true,
            ],
            [
                'name'             => 'Quincaillerie',
                'code'             => 'quincaillerie',
                'description'      => 'Vente comptoir, devis, crédit client, dettes fournisseurs.',
                'icon'             => 'fa-solid fa-wrench',
                'installation_fee' => 0,
                'is_active'        => true,
            ],
            [
                'name'             => 'Boutique / POS',
                'code'             => 'boutique',
                'description'      => 'Interface POS, variantes produits, fidélité, multi-dépôts.',
                'icon'             => 'fa-solid fa-cart-shopping',
                'installation_fee' => 0,
                'is_active'        => true,
            ],
            [
                'name'             => 'Location',
                'code'             => 'location',
                'description'      => 'Gestion biens, contrats PDF, planning, paiements échelonnés.',
                'icon'             => 'fa-solid fa-key',
                'installation_fee' => 0,
                'is_active'        => true,
            ],
        ];

        foreach ($modules as $module) {
            Module::updateOrCreate(['code' => $module['code']], $module);
        }

        $this->command->info('✅ Modules créés : restaurant / quincaillerie / boutique / location');
    }
}
