<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Entreprise / Tenant
            'companies.view', 'companies.create', 'companies.edit', 'companies.delete',
            // Utilisateurs
            'users.view', 'users.create', 'users.edit', 'users.delete',
            // Clients & Fournisseurs
            'clients.view', 'clients.create', 'clients.edit', 'clients.delete',
            'suppliers.view', 'suppliers.create', 'suppliers.edit', 'suppliers.delete',
            // Produits / Stock
            'products.view', 'products.create', 'products.edit', 'products.delete',
            'stock.view', 'stock.adjust',
            // Ventes
            'sales.view', 'sales.create', 'sales.cancel',
            // Caisse
            'cashier.open', 'cashier.close',
            // Rapports
            'reports.view',
            // Paramètres
            'settings.view', 'settings.edit',
            // Abonnements (super_admin)
            'subscriptions.view', 'subscriptions.manage',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // Super Admin — accès total
        $superAdmin = Role::firstOrCreate(['name' => UserRole::SuperAdmin->value, 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        // Owner — tout sauf gestion super admin
        $owner = Role::firstOrCreate(['name' => UserRole::Owner->value, 'guard_name' => 'web']);
        $owner->givePermissionTo(array_filter($permissions, fn($p) => !str_starts_with($p, 'subscriptions')));

        // Manager
        $manager = Role::firstOrCreate(['name' => UserRole::Manager->value, 'guard_name' => 'web']);
        $manager->givePermissionTo([
            'clients.view', 'clients.create', 'clients.edit',
            'suppliers.view', 'suppliers.create', 'suppliers.edit',
            'products.view', 'products.create', 'products.edit',
            'stock.view', 'stock.adjust',
            'sales.view', 'sales.create', 'sales.cancel',
            'cashier.open', 'cashier.close',
            'reports.view',
        ]);

        // Caissier
        $caissier = Role::firstOrCreate(['name' => UserRole::Caissier->value, 'guard_name' => 'web']);
        $caissier->givePermissionTo([
            'clients.view', 'clients.create',
            'products.view',
            'sales.view', 'sales.create',
            'cashier.open', 'cashier.close',
        ]);

        // Magasinier
        $magasinier = Role::firstOrCreate(['name' => UserRole::Magasinier->value, 'guard_name' => 'web']);
        $magasinier->givePermissionTo([
            'products.view', 'products.create', 'products.edit',
            'suppliers.view',
            'stock.view', 'stock.adjust',
        ]);

        $this->command->info('Rôles et permissions créés avec succès.');
    }
}
