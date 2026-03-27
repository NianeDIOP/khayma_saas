<?php

namespace Database\Seeders;

use App\Models\CashSession;
use App\Models\Category;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Depot;
use App\Models\DepotTransfer;
use App\Models\DepotTransferItem;
use App\Models\Dish;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Inventory;
use App\Models\InventoryLine;
use App\Models\LoyaltyConfig;
use App\Models\LoyaltyTransaction;
use App\Models\Module;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Promotion;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\RentalAsset;
use App\Models\RentalContract;
use App\Models\RentalPayment;
use App\Models\RestaurantCategory;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\StockItem;
use App\Models\StockMovement;
use App\Models\Subscription;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\SupplierReturn;
use App\Models\Unit;
use App\Models\User;
use App\Models\VariantStockItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    private Company $company;
    private User $owner;
    private User $manager;
    private User $caissier;
    private User $magasinier;
    private array $customers = [];
    private array $suppliers = [];
    private array $products  = [];
    private array $depots    = [];
    private array $units     = [];

    public function run(): void
    {
        $this->createCompanyAndTeam();
        $this->activateAllModules();
        $this->createBaseData();
        $this->seedRestaurant();
        $this->seedQuincaillerie();
        $this->seedBoutique();
        $this->seedLocation();
        $this->seedExpenses();

        $this->command->info('');
        $this->command->info('╔══════════════════════════════════════════════════════╗');
        $this->command->info('║  ✅  DONNÉES DÉMO CRÉÉES AVEC SUCCÈS                ║');
        $this->command->info('╠══════════════════════════════════════════════════════╣');
        $this->command->info('║  Entreprise : Diallo & Frères SARL                  ║');
        $this->command->info('║  Slug       : diallo-freres                         ║');
        $this->command->info('║                                                      ║');
        $this->command->info('║  👤 Owner    : patron@diallo.sn   / Password1!      ║');
        $this->command->info('║  👤 Manager  : manager@diallo.sn  / Password1!      ║');
        $this->command->info('║  👤 Caissier : caisse@diallo.sn   / Password1!      ║');
        $this->command->info('║  👤 Stock    : stock@diallo.sn    / Password1!      ║');
        $this->command->info('║  👤 Admin    : admin@khayma.com   / Admin@1234!     ║');
        $this->command->info('║                                                      ║');
        $this->command->info('║  URL : /app/dashboard?_tenant=diallo-freres          ║');
        $this->command->info('╚══════════════════════════════════════════════════════╝');
    }

    // ─────────────────────────────────────────────────
    //  1. ENTREPRISE + ÉQUIPE
    // ─────────────────────────────────────────────────
    private function createCompanyAndTeam(): void
    {
        $this->company = Company::updateOrCreate(
            ['slug' => 'diallo-freres'],
            [
                'name'                => 'Diallo & Frères SARL',
                'slug'                => 'diallo-freres',
                'email'               => 'contact@diallo-freres.sn',
                'phone'               => '+221 77 123 45 67',
                'address'             => 'Marché Sandaga, Avenue Blaise Diagne, Dakar',
                'sector'              => 'boutique',
                'ninea'               => '005234567 2G3',
                'currency'            => 'XOF',
                'timezone'            => 'Africa/Dakar',
                'subscription_status' => 'active',
                'trial_ends_at'       => now()->addMonths(6),
                'is_active'           => true,
            ]
        );

        // Owner
        $this->owner = User::updateOrCreate(
            ['email' => 'patron@diallo.sn'],
            ['name' => 'Mamadou Diallo', 'password' => Hash::make('Password1!'), 'is_super_admin' => false]
        );
        $this->company->users()->syncWithoutDetaching([
            $this->owner->id => ['role' => 'owner', 'joined_at' => now()->subMonths(6)],
        ]);
        if (!$this->owner->hasRole('owner')) $this->owner->assignRole('owner');

        // Manager
        $this->manager = User::updateOrCreate(
            ['email' => 'manager@diallo.sn'],
            ['name' => 'Fatou Sow', 'password' => Hash::make('Password1!'), 'is_super_admin' => false]
        );
        $this->company->users()->syncWithoutDetaching([
            $this->manager->id => ['role' => 'manager', 'joined_at' => now()->subMonths(4),
                'permissions' => json_encode(['dashboard','customers','suppliers','products','categories','units','depots','stock','sales','expenses',
                    'restaurant.pos','restaurant.orders','restaurant.dishes','restaurant.categories','restaurant.services','restaurant.cash','restaurant.reports',
                    'quinc.pos','quinc.quotes','quinc.purchase-orders','quinc.supplier-payments','quinc.supplier-returns','quinc.credits','quinc.inventories','quinc.reports',
                    'boutique.pos','boutique.variants','boutique.promotions','boutique.loyalty','boutique.transfers','boutique.reports',
                    'location.assets','location.contracts','location.payments','location.calendar','location.reports'])],
        ]);
        if (!$this->manager->hasRole('manager')) $this->manager->assignRole('manager');

        // Caissier
        $this->caissier = User::updateOrCreate(
            ['email' => 'caisse@diallo.sn'],
            ['name' => 'Awa Ndiaye', 'password' => Hash::make('Password1!'), 'is_super_admin' => false]
        );
        $this->company->users()->syncWithoutDetaching([
            $this->caissier->id => ['role' => 'caissier', 'joined_at' => now()->subMonths(3),
                'permissions' => json_encode(['dashboard','customers','sales','restaurant.pos','restaurant.orders','restaurant.cash','quinc.pos','quinc.credits','boutique.pos','location.payments'])],
        ]);
        if (!$this->caissier->hasRole('caissier')) $this->caissier->assignRole('caissier');

        // Magasinier
        $this->magasinier = User::updateOrCreate(
            ['email' => 'stock@diallo.sn'],
            ['name' => 'Ibrahima Fall', 'password' => Hash::make('Password1!'), 'is_super_admin' => false]
        );
        $this->company->users()->syncWithoutDetaching([
            $this->magasinier->id => ['role' => 'magasinier', 'joined_at' => now()->subMonths(2),
                'permissions' => json_encode(['dashboard','suppliers','products','categories','units','depots','stock','quinc.inventories','boutique.transfers'])],
        ]);
        if (!$this->magasinier->hasRole('magasinier')) $this->magasinier->assignRole('magasinier');

        // Subscription Pro
        $plan = Plan::where('code', 'pro')->first();
        if ($plan) {
            Subscription::updateOrCreate(
                ['company_id' => $this->company->id, 'status' => 'active'],
                [
                    'plan_id'        => $plan->id,
                    'billing_period' => 'yearly',
                    'amount_paid'    => 300000,
                    'starts_at'      => now()->subMonths(6),
                    'ends_at'        => now()->addMonths(6),
                ]
            );
        }

        // Super Admin
        User::updateOrCreate(
            ['email' => 'admin@khayma.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('Admin@1234!'), 'is_super_admin' => true]
        );

        $this->command->info('✅ Entreprise + Équipe (4 membres)');
    }

    // ─────────────────────────────────────────────────
    //  2. ACTIVER TOUS LES MODULES
    // ─────────────────────────────────────────────────
    private function activateAllModules(): void
    {
        $modules = Module::where('is_active', true)->get();
        foreach ($modules as $module) {
            $this->company->modules()->syncWithoutDetaching([
                $module->id => ['activated_at' => now()->subMonths(5), 'activated_by' => $this->owner->id],
            ]);
        }
        $this->command->info('✅ 4 modules activés (Restaurant, Quincaillerie, Boutique, Location)');
    }

    // ─────────────────────────────────────────────────
    //  3. DONNÉES DE BASE (Clients, Fournisseurs, etc.)
    // ─────────────────────────────────────────────────
    private function createBaseData(): void
    {
        $co = $this->company->id;

        // ── Clients ──
        $clientsData = [
            ['name' => 'Ousmane Ba',        'phone' => '+221 77 234 56 78', 'email' => 'ousmane.ba@gmail.com',      'address' => 'Médina, Dakar',             'category' => 'normal'],
            ['name' => 'Aminata Diop',      'phone' => '+221 78 345 67 89', 'email' => 'aminata.diop@yahoo.fr',     'address' => 'Parcelles Assainies, Dakar','category' => 'vip'],
            ['name' => 'Aliou Cissé',       'phone' => '+221 76 456 78 90', 'email' => 'aliou.cisse@hotmail.com',   'address' => 'Guédiawaye',                'category' => 'normal'],
            ['name' => 'Entreprise Touba BTP','phone'=> '+221 33 867 45 23', 'email' => 'contact@toubabtp.sn',      'address' => 'Touba, Diourbel',           'category' => 'professional'],
            ['name' => 'Hotel Teranga',      'phone' => '+221 33 889 12 34', 'email' => 'achats@teranga-hotel.sn',  'address' => 'Place de l\'Indépendance',  'category' => 'professional'],
            ['name' => 'Mame Diarra Bousso','phone' => '+221 77 567 89 01', 'email' => null,                        'address' => 'Thiès',                     'category' => 'normal'],
            ['name' => 'Restaurant Le Djolof','phone'=> '+221 33 845 67 89', 'email' => 'commande@ledjolof.sn',    'address' => 'Almadies, Dakar',           'category' => 'professional'],
            ['name' => 'Moussa Sarr',        'phone' => '+221 70 678 90 12', 'email' => 'moussa.sarr@live.fr',      'address' => 'Rufisque',                  'category' => 'normal'],
            ['name' => 'Mbacké Construction','phone' => '+221 33 976 54 32', 'email' => 'info@mbacke-btp.sn',      'address' => 'Kaolack',                   'category' => 'professional'],
            ['name' => 'Sokhna Aïda Mbaye', 'phone' => '+221 77 890 12 34', 'email' => 'sokhna.aida@gmail.com',    'address' => 'Pikine',                    'category' => 'vip'],
            ['name' => 'Groupe Wally Seck',  'phone' => '+221 78 901 23 45', 'email' => 'events@wallyseck.sn',     'address' => 'Grand Yoff, Dakar',         'category' => 'vip'],
            ['name' => 'Pape Demba Sy',     'phone' => '+221 76 012 34 56', 'email' => null,                        'address' => 'Saint-Louis',               'category' => 'normal'],
        ];

        foreach ($clientsData as $i => $c) {
            $this->customers[] = Customer::updateOrCreate(
                ['company_id' => $co, 'phone' => $c['phone']],
                array_merge($c, [
                    'company_id'          => $co,
                    'loyalty_points'      => rand(0, 500),
                    'outstanding_balance' => $i < 4 ? rand(0, 150000) : 0,
                ])
            );
        }

        // ── Fournisseurs ──
        $suppliersData = [
            ['name' => 'CFAO Sénégal',          'phone' => '+221 33 849 50 00', 'email' => 'commandes@cfao.sn',       'address' => 'Zone industrielle, Dakar',    'ninea' => '001234567 2G3'],
            ['name' => 'Ets Kébé & Fils',       'phone' => '+221 33 842 31 12', 'email' => 'kebe.appro@orange.sn',    'address' => 'Sandaga, Dakar',              'ninea' => '002345678 2G3'],
            ['name' => 'Quincaillerie Touba',    'phone' => '+221 77 543 21 09', 'email' => 'quinc.touba@gmail.com',   'address' => 'Touba',                       'ninea' => '003456789 2G3'],
            ['name' => 'Import-Export Saly',     'phone' => '+221 33 957 12 34', 'email' => 'contact@imexsaly.sn',     'address' => 'Saly, Mbour',                 'ninea' => '004567890 2G3'],
            ['name' => 'Dragon d\'Or Trading',   'phone' => '+221 33 832 98 76', 'email' => 'dragonor@trading.sn',     'address' => 'Centenaire, Dakar',           'ninea' => '005678901 2G3'],
            ['name' => 'Batimat Distribution',   'phone' => '+221 33 867 43 21', 'email' => 'vente@batimat-sn.com',    'address' => 'Rufisque',                    'ninea' => '006789012 2G3'],
            ['name' => 'SN Alimentaire',         'phone' => '+221 33 860 11 22', 'email' => 'commande@sn-alim.sn',     'address' => 'Mbao, Dakar',                 'ninea' => '007890123 2G3'],
            ['name' => 'Tech Solutions Afrique', 'phone' => '+221 33 889 99 88', 'email' => 'sales@techsol-afrique.sn','address' => 'Point E, Dakar',              'ninea' => '008901234 2G3'],
        ];

        foreach ($suppliersData as $i => $s) {
            $this->suppliers[] = Supplier::updateOrCreate(
                ['company_id' => $co, 'phone' => $s['phone']],
                array_merge($s, [
                    'company_id'          => $co,
                    'rating'              => round(rand(30, 50) / 10, 1),
                    'outstanding_balance' => $i < 3 ? rand(50000, 500000) : 0,
                ])
            );
        }

        // ── Unités ──
        $unitsData = [
            ['name' => 'Pièce',     'symbol' => 'pce'],
            ['name' => 'Kilogramme','symbol' => 'kg'],
            ['name' => 'Litre',     'symbol' => 'L'],
            ['name' => 'Mètre',     'symbol' => 'm'],
            ['name' => 'Sac',       'symbol' => 'sac'],
            ['name' => 'Carton',    'symbol' => 'ctn'],
            ['name' => 'Rouleau',   'symbol' => 'rl'],
            ['name' => 'Tonne',     'symbol' => 'T'],
        ];

        foreach ($unitsData as $u) {
            $this->units[] = Unit::updateOrCreate(
                ['company_id' => $co, 'name' => $u['name']],
                array_merge($u, ['company_id' => $co])
            );
        }

        // ── Dépôts ──
        $this->depots[] = Depot::updateOrCreate(
            ['company_id' => $co, 'name' => 'Dépôt Principal'],
            ['company_id' => $co, 'name' => 'Dépôt Principal', 'address' => 'Sandaga, Dakar', 'is_default' => true]
        );
        $this->depots[] = Depot::updateOrCreate(
            ['company_id' => $co, 'name' => 'Dépôt Parcelles'],
            ['company_id' => $co, 'name' => 'Dépôt Parcelles', 'address' => 'Parcelles U26, Dakar', 'is_default' => false]
        );

        // ── Catégories ──
        $categoriesData = [
            // General / Boutique
            ['name' => 'Électronique',        'module' => 'boutique'],
            ['name' => 'Alimentaire',          'module' => 'boutique'],
            ['name' => 'Habillement',          'module' => 'boutique'],
            ['name' => 'Cosmétiques',          'module' => 'boutique'],
            ['name' => 'Accessoires',          'module' => 'boutique'],
            // Quincaillerie
            ['name' => 'Ciment & Béton',       'module' => 'quincaillerie'],
            ['name' => 'Plomberie',            'module' => 'quincaillerie'],
            ['name' => 'Électricité',          'module' => 'quincaillerie'],
            ['name' => 'Peinture',             'module' => 'quincaillerie'],
            ['name' => 'Outillage',            'module' => 'quincaillerie'],
            ['name' => 'Fer & Acier',          'module' => 'quincaillerie'],
        ];

        $categories = [];
        foreach ($categoriesData as $cat) {
            $categories[$cat['name']] = Category::updateOrCreate(
                ['company_id' => $co, 'name' => $cat['name'], 'module' => $cat['module']],
                array_merge($cat, ['company_id' => $co])
            );
        }

        // ── Produits ──
        $pce    = $this->units[0]; // Pièce
        $kg     = $this->units[1]; // Kg
        $litre  = $this->units[2]; // Litre
        $metre  = $this->units[3]; // Mètre
        $sac    = $this->units[4]; // Sac
        $carton = $this->units[5]; // Carton
        $rl     = $this->units[6]; // Rouleau
        $tonne  = $this->units[7]; // Tonne

        $productsData = [
            // ── BOUTIQUE ──
            ['name' => 'Samsung Galaxy A15',      'cat' => 'Électronique',  'unit' => $pce,   'pp' => 85000,  'sp' => 115000, 'min' => 5,  'barcode' => '8806095467890'],
            ['name' => 'Écouteurs Bluetooth TWS',  'cat' => 'Électronique',  'unit' => $pce,   'pp' => 3500,   'sp' => 7500,   'min' => 20, 'barcode' => '6901443284523'],
            ['name' => 'Chargeur USB-C 25W',       'cat' => 'Électronique',  'unit' => $pce,   'pp' => 2000,   'sp' => 5000,   'min' => 15, 'barcode' => '8806095234567'],
            ['name' => 'Riz Brisé Uncle Benz 25kg','cat' => 'Alimentaire',   'unit' => $sac,   'pp' => 12500,  'sp' => 14500,  'min' => 10, 'barcode' => '8901234567890'],
            ['name' => 'Huile Végétale Niéré 5L',  'cat' => 'Alimentaire',   'unit' => $pce,   'pp' => 5800,   'sp' => 7000,   'min' => 8,  'barcode' => '6291041005011'],
            ['name' => 'Lait en poudre Nido 400g', 'cat' => 'Alimentaire',   'unit' => $pce,   'pp' => 2800,   'sp' => 3500,   'min' => 15, 'barcode' => '7613036628532'],
            ['name' => 'Sucre en morceaux 5kg',    'cat' => 'Alimentaire',   'unit' => $pce,   'pp' => 2500,   'sp' => 3200,   'min' => 10, 'barcode' => '3560070139811'],
            ['name' => 'Boubou Homme Bazin',       'cat' => 'Habillement',   'unit' => $pce,   'pp' => 15000,  'sp' => 25000,  'min' => 3,  'barcode' => null],
            ['name' => 'Ensemble Femme Wax',       'cat' => 'Habillement',   'unit' => $pce,   'pp' => 8000,   'sp' => 15000,  'min' => 5,  'barcode' => null],
            ['name' => 'Crème Nivea 400ml',        'cat' => 'Cosmétiques',   'unit' => $pce,   'pp' => 2200,   'sp' => 3500,   'min' => 10, 'barcode' => '4005808904501'],
            ['name' => 'Parfum Thiouraye 100ml',   'cat' => 'Cosmétiques',   'unit' => $pce,   'pp' => 4000,   'sp' => 8000,   'min' => 5,  'barcode' => null],
            ['name' => 'Montre Casio Classique',   'cat' => 'Accessoires',   'unit' => $pce,   'pp' => 12000,  'sp' => 20000,  'min' => 3,  'barcode' => '4549526216978'],
            // ── QUINCAILLERIE ──
            ['name' => 'Ciment Sococim CEM II 50kg','cat'=>'Ciment & Béton', 'unit' => $sac,   'pp' => 4200,   'sp' => 4800,   'min' => 50, 'barcode' => null],
            ['name' => 'Fer à béton Ø8 (barre 12m)','cat'=>'Fer & Acier',   'unit' => $pce,   'pp' => 2500,   'sp' => 3200,   'min' => 100,'barcode' => null],
            ['name' => 'Fer à béton Ø10 (barre 12m)','cat'=>'Fer & Acier',  'unit' => $pce,   'pp' => 3800,   'sp' => 4500,   'min' => 80, 'barcode' => null],
            ['name' => 'Fer à béton Ø12 (barre 12m)','cat'=>'Fer & Acier',  'unit' => $pce,   'pp' => 5200,   'sp' => 6200,   'min' => 50, 'barcode' => null],
            ['name' => 'Tuyau PVC Ø110 (barre 4m)','cat' => 'Plomberie',    'unit' => $pce,   'pp' => 4500,   'sp' => 6000,   'min' => 20, 'barcode' => null],
            ['name' => 'Robinet lavabo chromé',    'cat' => 'Plomberie',     'unit' => $pce,   'pp' => 5000,   'sp' => 8500,   'min' => 10, 'barcode' => null],
            ['name' => 'Câble électrique 2.5mm² (rl 100m)','cat'=>'Électricité','unit'=>$rl,   'pp' => 18000,  'sp' => 25000,  'min' => 5,  'barcode' => null],
            ['name' => 'Disjoncteur 20A Schneider','cat' => 'Électricité',   'unit' => $pce,   'pp' => 5500,   'sp' => 8000,   'min' => 10, 'barcode' => null],
            ['name' => 'Peinture Seigneurie 20L',  'cat' => 'Peinture',     'unit' => $pce,   'pp' => 32000,  'sp' => 42000,  'min' => 3,  'barcode' => null],
            ['name' => 'Perceuse Bosch 650W',      'cat' => 'Outillage',    'unit' => $pce,   'pp' => 35000,  'sp' => 48000,  'min' => 2,  'barcode' => null],
            ['name' => 'Sable fin (tonne)',         'cat' =>'Ciment & Béton','unit' => $tonne,  'pp' => 15000,  'sp' => 20000,  'min' => 5,  'barcode' => null],
            ['name' => 'Gravier concassé (tonne)',  'cat' =>'Ciment & Béton','unit' => $tonne,  'pp' => 18000,  'sp' => 25000,  'min' => 5,  'barcode' => null],
        ];

        foreach ($productsData as $p) {
            $product = Product::updateOrCreate(
                ['company_id' => $co, 'name' => $p['name']],
                [
                    'company_id'      => $co,
                    'name'            => $p['name'],
                    'category_id'     => $categories[$p['cat']]->id,
                    'unit_id'         => $p['unit']->id,
                    'purchase_price'  => $p['pp'],
                    'selling_price'   => $p['sp'],
                    'barcode'         => $p['barcode'],
                    'min_stock_alert' => $p['min'],
                    'is_active'       => true,
                ]
            );
            $this->products[$p['name']] = $product;

            // ── Stocks dans les 2 dépôts ──
            $qty1 = rand($p['min'], $p['min'] * 5);
            $qty2 = rand(0, $p['min'] * 2);

            StockItem::updateOrCreate(
                ['company_id' => $co, 'product_id' => $product->id, 'depot_id' => $this->depots[0]->id],
                ['quantity' => $qty1]
            );
            StockItem::updateOrCreate(
                ['company_id' => $co, 'product_id' => $product->id, 'depot_id' => $this->depots[1]->id],
                ['quantity' => $qty2]
            );

            // Stock movement entry
            StockMovement::create([
                'company_id' => $co, 'product_id' => $product->id, 'depot_id' => $this->depots[0]->id,
                'type' => 'in', 'quantity' => $qty1, 'unit_cost' => $p['pp'],
                'reference' => 'INIT-STOCK', 'notes' => 'Stock initial', 'user_id' => $this->magasinier->id,
            ]);
        }

        // Link suppliers to products
        foreach ($this->products as $name => $product) {
            $supplierIdx = rand(0, count($this->suppliers) - 1);
            $product->suppliers()->syncWithoutDetaching([
                $this->suppliers[$supplierIdx]->id => [
                    'supplier_price'     => $product->purchase_price * 0.95,
                    'supplier_reference' => 'REF-' . strtoupper(substr(md5($name), 0, 6)),
                    'is_preferred'       => true,
                ],
            ]);
        }

        // ── Catégories de dépenses ──
        $expCats = ['Loyer', 'Transport', 'Salaires', 'Eau & Électricité', 'Téléphone & Internet', 'Fournitures bureau', 'Maintenance', 'Divers'];
        foreach ($expCats as $ec) {
            ExpenseCategory::updateOrCreate(
                ['company_id' => $co, 'name' => $ec],
                ['company_id' => $co, 'name' => $ec]
            );
        }

        $this->command->info('✅ Données de base : ' . count($this->customers) . ' clients, ' . count($this->suppliers) . ' fournisseurs, ' . count($this->products) . ' produits, 2 dépôts');
    }

    // ─────────────────────────────────────────────────
    //  4. MODULE RESTAURANT
    // ─────────────────────────────────────────────────
    private function seedRestaurant(): void
    {
        $co = $this->company->id;

        // Services
        $services = [
            Service::updateOrCreate(['company_id' => $co, 'name' => 'Petit-déjeuner'],  ['company_id' => $co, 'name' => 'Petit-déjeuner','start_time' => '07:00', 'end_time' => '10:30', 'is_active' => true]),
            Service::updateOrCreate(['company_id' => $co, 'name' => 'Déjeuner'],        ['company_id' => $co, 'name' => 'Déjeuner',      'start_time' => '12:00', 'end_time' => '15:30', 'is_active' => true]),
            Service::updateOrCreate(['company_id' => $co, 'name' => 'Dîner'],           ['company_id' => $co, 'name' => 'Dîner',         'start_time' => '19:00', 'end_time' => '23:00', 'is_active' => true]),
        ];

        // Restaurant Categories
        $restCats = [
            RestaurantCategory::updateOrCreate(['company_id' => $co, 'name' => 'Entrées'],     ['company_id' => $co, 'name' => 'Entrées',     'sort_order' => 1, 'is_active' => true]),
            RestaurantCategory::updateOrCreate(['company_id' => $co, 'name' => 'Plats'],       ['company_id' => $co, 'name' => 'Plats',       'sort_order' => 2, 'is_active' => true]),
            RestaurantCategory::updateOrCreate(['company_id' => $co, 'name' => 'Grillades'],   ['company_id' => $co, 'name' => 'Grillades',   'sort_order' => 3, 'is_active' => true]),
            RestaurantCategory::updateOrCreate(['company_id' => $co, 'name' => 'Desserts'],    ['company_id' => $co, 'name' => 'Desserts',    'sort_order' => 4, 'is_active' => true]),
            RestaurantCategory::updateOrCreate(['company_id' => $co, 'name' => 'Boissons'],    ['company_id' => $co, 'name' => 'Boissons',    'sort_order' => 5, 'is_active' => true]),
            RestaurantCategory::updateOrCreate(['company_id' => $co, 'name' => 'Suppléments'], ['company_id' => $co, 'name' => 'Suppléments', 'sort_order' => 6, 'is_active' => true]),
        ];

        // Dishes
        $dishesData = [
            // Entrées
            ['cat' => 0, 'name' => 'Salade Niçoise',             'price' => 2500, 'morning' => false, 'noon' => true,  'evening' => true],
            ['cat' => 0, 'name' => 'Nems au poulet (4 pièces)',  'price' => 2000, 'morning' => false, 'noon' => true,  'evening' => true],
            ['cat' => 0, 'name' => 'Soupe de légumes',           'price' => 1500, 'morning' => true,  'noon' => true,  'evening' => true],
            // Plats
            ['cat' => 1, 'name' => 'Thiéboudienne (Riz au poisson)', 'price' => 3500, 'morning' => false, 'noon' => true,  'evening' => false, 'promo' => 3000],
            ['cat' => 1, 'name' => 'Yassa Poulet',                   'price' => 3000, 'morning' => false, 'noon' => true,  'evening' => true],
            ['cat' => 1, 'name' => 'Mafé Bœuf',                     'price' => 3000, 'morning' => false, 'noon' => true,  'evening' => true],
            ['cat' => 1, 'name' => 'Thiéré Mboum (Couscous)',       'price' => 2500, 'morning' => false, 'noon' => true,  'evening' => true],
            ['cat' => 1, 'name' => 'Domoda (Sauce arachide)',        'price' => 2800, 'morning' => false, 'noon' => true,  'evening' => true],
            ['cat' => 1, 'name' => 'Poulet DG',                     'price' => 4500, 'morning' => false, 'noon' => true,  'evening' => true],
            ['cat' => 1, 'name' => 'Burger Diallo Spécial',         'price' => 3500, 'morning' => false, 'noon' => true,  'evening' => true],
            // Grillades
            ['cat' => 2, 'name' => 'Brochettes de bœuf (3)',        'price' => 3000, 'morning' => false, 'noon' => false, 'evening' => true],
            ['cat' => 2, 'name' => 'Poulet braisé entier',          'price' => 5000, 'morning' => false, 'noon' => false, 'evening' => true],
            ['cat' => 2, 'name' => 'Dibi Agneau (portion)',         'price' => 4000, 'morning' => false, 'noon' => false, 'evening' => true],
            // Desserts
            ['cat' => 3, 'name' => 'Thiakry (couscous sucré)',      'price' => 1500, 'morning' => false, 'noon' => true,  'evening' => true],
            ['cat' => 3, 'name' => 'Salade de fruits frais',        'price' => 1500, 'morning' => true,  'noon' => true,  'evening' => true],
            // Boissons
            ['cat' => 4, 'name' => 'Bissap frais (verre)',          'price' => 500,  'morning' => true,  'noon' => true,  'evening' => true],
            ['cat' => 4, 'name' => 'Jus de Bouye (verre)',          'price' => 500,  'morning' => true,  'noon' => true,  'evening' => true],
            ['cat' => 4, 'name' => 'Gingembre frais (verre)',       'price' => 500,  'morning' => true,  'noon' => true,  'evening' => true],
            ['cat' => 4, 'name' => 'Café Touba',                    'price' => 300,  'morning' => true,  'noon' => true,  'evening' => true],
            ['cat' => 4, 'name' => 'Thé Ataya',                     'price' => 500,  'morning' => true,  'noon' => true,  'evening' => true],
            ['cat' => 4, 'name' => 'Coca-Cola 33cl',                'price' => 700,  'morning' => true,  'noon' => true,  'evening' => true],
            ['cat' => 4, 'name' => 'Eau minérale Kirène 1.5L',      'price' => 500,  'morning' => true,  'noon' => true,  'evening' => true],
            // Suppléments
            ['cat' => 5, 'name' => 'Supplément frites',             'price' => 500,  'morning' => false, 'noon' => true,  'evening' => true,  'additional' => true],
            ['cat' => 5, 'name' => 'Supplément fromage',            'price' => 300,  'morning' => false, 'noon' => true,  'evening' => true,  'additional' => true],
            ['cat' => 5, 'name' => 'Supplément oeuf',               'price' => 200,  'morning' => true,  'noon' => true,  'evening' => true,  'additional' => true],
        ];

        $dishes = [];
        foreach ($dishesData as $i => $d) {
            $dishes[] = Dish::updateOrCreate(
                ['company_id' => $co, 'name' => $d['name']],
                [
                    'company_id'             => $co,
                    'restaurant_category_id' => $restCats[$d['cat']]->id,
                    'name'                   => $d['name'],
                    'price'                  => $d['price'],
                    'is_available'           => true,
                    'available_morning'      => $d['morning'],
                    'available_noon'         => $d['noon'],
                    'available_evening'      => $d['evening'],
                    'is_additional'          => $d['additional'] ?? false,
                    'promo_price'            => $d['promo'] ?? null,
                    'promo_start'            => isset($d['promo']) ? now()->subDays(5)->toDateString() : null,
                    'promo_end'              => isset($d['promo']) ? now()->addDays(10)->toDateString() : null,
                    'sort_order'             => $i,
                ]
            );
        }

        // ── Cash Sessions (3 derniers jours) ──
        $orderRef = 1;
        for ($day = 2; $day >= 0; $day--) {
            $date = now()->subDays($day);

            foreach ($services as $sIdx => $service) {
                $session = CashSession::create([
                    'company_id'     => $co,
                    'service_id'     => $service->id,
                    'user_id'        => $this->caissier->id,
                    'opened_at'      => $date->copy()->setTimeFromTimeString($service->start_time),
                    'closed_at'      => $day === 0 && $sIdx === 2 ? null : $date->copy()->setTimeFromTimeString($service->end_time),
                    'opening_amount' => 25000,
                    'closing_amount' => $day === 0 && $sIdx === 2 ? null : rand(80000, 180000),
                    'expected_amount'=> $day === 0 && $sIdx === 2 ? null : rand(80000, 180000),
                ]);

                // 3-8 commandes par session
                $nbOrders = rand(3, 8);
                for ($o = 0; $o < $nbOrders; $o++) {
                    $type     = ['table', 'table', 'table', 'takeaway', 'delivery'][rand(0, 4)];
                    $status   = $day === 0 && $o >= $nbOrders - 2 ? 'pending' : 'completed';
                    $payStatus= $status === 'completed' ? 'paid' : 'pending';

                    $order = Order::create([
                        'company_id'      => $co,
                        'reference'       => 'CMD-' . $date->format('Ymd') . '-' . str_pad($orderRef++, 4, '0', STR_PAD_LEFT),
                        'type'            => $type,
                        'table_number'    => $type === 'table' ? rand(1, 15) : null,
                        'delivery_address'=> $type === 'delivery' ? 'Médina, rue ' . rand(1, 30) : null,
                        'customer_name'   => ['Ousmane', 'Fatou', 'Awa', 'Moussa', 'Aïda', 'Pape', 'Mame'][rand(0, 6)],
                        'service_id'      => $service->id,
                        'cash_session_id' => $session->id,
                        'user_id'         => $this->caissier->id,
                        'status'          => $status,
                        'subtotal'        => 0,
                        'discount_amount' => 0,
                        'total'           => 0,
                        'payment_method'  => $payStatus === 'paid' ? ['cash', 'cash', 'cash', 'wave', 'om'][rand(0, 4)] : 'cash',
                        'payment_status'  => $payStatus,
                        'paid_at'         => $payStatus === 'paid' ? $date : null,
                    ]);

                    // 1-4 items per order
                    $nbItems  = rand(1, 4);
                    $subtotal = 0;
                    $usedDishes = [];
                    for ($it = 0; $it < $nbItems; $it++) {
                        $dish = $dishes[rand(0, count($dishes) - 1)];
                        if (in_array($dish->id, $usedDishes)) continue;
                        $usedDishes[] = $dish->id;
                        $qty  = rand(1, 3);
                        $price= $dish->promo_price ?? $dish->price;
                        $tot  = $qty * $price;
                        $subtotal += $tot;
                        OrderItem::create([
                            'order_id'   => $order->id,
                            'dish_id'    => $dish->id,
                            'quantity'   => $qty,
                            'unit_price' => $price,
                            'total'      => $tot,
                        ]);
                    }

                    $discount = rand(0, 3) === 0 ? round($subtotal * 0.1) : 0;
                    $order->update([
                        'subtotal'        => $subtotal,
                        'discount_amount' => $discount,
                        'total'           => $subtotal - $discount,
                    ]);
                }
            }
        }

        $this->command->info('✅ Restaurant : ' . count($dishes) . ' plats, 3 services, sessions + commandes sur 3 jours');
    }

    // ─────────────────────────────────────────────────
    //  5. MODULE QUINCAILLERIE
    // ─────────────────────────────────────────────────
    private function seedQuincaillerie(): void
    {
        $co = $this->company->id;

        // ── Devis ──
        $quoteProducts = array_values(array_filter($this->products, fn($p) => $p->category->module === 'quincaillerie'));
        $quoteDate = now()->subDays(15);

        for ($q = 0; $q < 5; $q++) {
            $customer = $this->customers[rand(0, count($this->customers) - 1)];
            $status   = ['draft', 'sent', 'accepted', 'accepted', 'converted'][$q];

            $quote = Quote::create([
                'company_id'  => $co,
                'customer_id' => $customer->id,
                'user_id'     => $this->manager->id,
                'reference'   => 'DEV-' . $quoteDate->copy()->addDays($q * 3)->format('Ymd') . '-' . str_pad($q + 1, 4, '0', STR_PAD_LEFT),
                'status'      => $status,
                'subtotal'    => 0,
                'discount_amount' => 0,
                'total'       => 0,
                'notes'       => 'Devis pour chantier ' . ['Médina', 'Guédiawaye', 'Thiaroye', 'Mbao', 'Rufisque'][$q],
                'valid_until' => $quoteDate->copy()->addDays($q * 3 + 30)->toDateString(),
            ]);

            $subtotal = 0;
            $nbItems  = rand(2, 5);
            for ($i = 0; $i < $nbItems && $i < count($quoteProducts); $i++) {
                $product = $quoteProducts[($q * 2 + $i) % count($quoteProducts)];
                $qty     = rand(5, 50);
                $price   = $product->selling_price;
                $total   = $qty * $price;
                $subtotal += $total;

                QuoteItem::create([
                    'quote_id'   => $quote->id,
                    'product_id' => $product->id,
                    'unit_id'    => $product->unit_id,
                    'quantity'   => $qty,
                    'unit_price' => $price,
                    'discount'   => 0,
                    'total'      => $total,
                ]);
            }

            $discount = $q >= 2 ? round($subtotal * 0.05) : 0;
            $quote->update(['subtotal' => $subtotal, 'discount_amount' => $discount, 'total' => $subtotal - $discount]);
        }

        // ── Bons de commande fournisseurs ──
        for ($p = 0; $p < 4; $p++) {
            $supplier = $this->suppliers[$p % count($this->suppliers)];
            $status   = ['draft', 'sent', 'received', 'received'][$p];
            $poDate   = now()->subDays(20 - $p * 5);

            $po = PurchaseOrder::create([
                'company_id'  => $co,
                'supplier_id' => $supplier->id,
                'user_id'     => $this->manager->id,
                'reference'   => 'BC-' . $poDate->format('Ymd') . '-' . str_pad($p + 1, 4, '0', STR_PAD_LEFT),
                'status'      => $status,
                'subtotal'    => 0,
                'total'       => 0,
                'notes'       => 'Approvisionnement mensuel',
                'expected_date' => $poDate->copy()->addDays(7)->toDateString(),
                'received_at'   => $status === 'received' ? $poDate->copy()->addDays(5) : null,
            ]);

            $subtotal = 0;
            $nbItems  = rand(2, 4);
            for ($i = 0; $i < $nbItems && $i < count($quoteProducts); $i++) {
                $product = $quoteProducts[($p * 3 + $i) % count($quoteProducts)];
                $qty     = rand(10, 100);
                $price   = $product->purchase_price;
                $total   = $qty * $price;
                $subtotal += $total;

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id'        => $product->id,
                    'unit_id'           => $product->unit_id,
                    'quantity'          => $qty,
                    'unit_price'        => $price,
                    'total'             => $total,
                    'received_quantity' => $status === 'received' ? $qty : 0,
                ]);
            }

            $po->update(['subtotal' => $subtotal, 'total' => $subtotal]);

            // Paiement fournisseur pour les bons reçus
            if ($status === 'received') {
                SupplierPayment::create([
                    'company_id'        => $co,
                    'supplier_id'       => $supplier->id,
                    'purchase_order_id' => $po->id,
                    'user_id'           => $this->owner->id,
                    'amount'            => round($subtotal * 0.7),
                    'method'            => ['cash', 'transfer', 'cheque'][rand(0, 2)],
                    'reference'         => 'PAY-' . $poDate->format('Ymd') . '-' . ($p + 1),
                    'date'              => $poDate->copy()->addDays(5)->toDateString(),
                ]);
            }
        }

        // ── Retours fournisseur ──
        SupplierReturn::create([
            'company_id'        => $co,
            'supplier_id'       => $this->suppliers[0]->id,
            'purchase_order_id' => PurchaseOrder::where('company_id', $co)->where('status', 'received')->first()?->id,
            'product_id'        => $quoteProducts[0]->id ?? $this->products[array_key_first($this->products)]->id,
            'user_id'           => $this->magasinier->id,
            'quantity'          => 5,
            'reason'            => 'Marchandise endommagée lors du transport',
            'status'            => 'approved',
            'date'              => now()->subDays(8)->toDateString(),
        ]);

        // ── Ventes Quincaillerie (avec crédit) ──
        $allProducts = array_values($this->products);
        for ($s = 0; $s < 8; $s++) {
            $customer    = $this->customers[$s % count($this->customers)];
            $saleDate    = now()->subDays(rand(0, 20));
            $payStatus   = ['paid', 'paid', 'paid', 'partial', 'partial', 'paid', 'paid', 'unpaid'][$s];
            $saleStatus  = 'completed';

            $sale = Sale::create([
                'company_id'      => $co,
                'customer_id'     => $customer->id,
                'user_id'         => $this->caissier->id,
                'reference'       => 'VT-' . $saleDate->format('Ymd') . '-' . str_pad($s + 1, 4, '0', STR_PAD_LEFT),
                'type'            => 'counter',
                'status'          => $saleStatus,
                'subtotal'        => 0,
                'discount_amount' => 0,
                'tax_amount'      => 0,
                'total'           => 0,
                'payment_status'  => $payStatus,
                'depot_id'        => $this->depots[0]->id,
            ]);

            $subtotal = 0;
            $nbItems  = rand(1, 4);
            for ($i = 0; $i < $nbItems; $i++) {
                $product = $allProducts[rand(0, count($allProducts) - 1)];
                $qty     = rand(1, 10);
                $price   = $product->selling_price;
                $total   = $qty * $price;
                $subtotal += $total;

                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'company_id' => $co,
                    'product_id' => $product->id,
                    'quantity'   => $qty,
                    'unit_price' => $price,
                    'discount'   => 0,
                    'total'      => $total,
                ]);
            }

            $discount = rand(0, 3) === 0 ? round($subtotal * 0.05) : 0;
            $total    = $subtotal - $discount;
            $sale->update(['subtotal' => $subtotal, 'discount_amount' => $discount, 'total' => $total]);

            // Paiements
            $paid = match ($payStatus) {
                'paid'    => $total,
                'partial' => round($total * 0.6),
                default   => 0,
            };
            if ($paid > 0) {
                Payment::create([
                    'company_id' => $co,
                    'sale_id'    => $sale->id,
                    'amount'     => $paid,
                    'method'     => ['cash', 'cash', 'wave', 'om', 'transfer'][rand(0, 4)],
                    'reference'  => 'REC-' . $saleDate->format('Ymd') . '-' . ($s + 1),
                ]);
            }
        }

        // ── Inventaire ──
        $inventory = Inventory::create([
            'company_id' => $co,
            'depot_id'   => $this->depots[0]->id,
            'user_id'    => $this->magasinier->id,
            'reference'  => 'INV-' . now()->subDays(3)->format('Ymd') . '-0001',
            'status'     => 'validated',
            'notes'      => 'Inventaire mensuel dépôt principal',
            'validated_at' => now()->subDays(2),
        ]);

        foreach (array_slice($allProducts, 0, 10) as $product) {
            $stockItem = StockItem::where('company_id', $co)->where('product_id', $product->id)->where('depot_id', $this->depots[0]->id)->first();
            $systemQty = $stockItem ? $stockItem->quantity : 0;
            $physicalQty = max(0, $systemQty + rand(-3, 3));
            InventoryLine::create([
                'inventory_id'    => $inventory->id,
                'product_id'      => $product->id,
                'system_quantity'  => $systemQty,
                'physical_quantity'=> $physicalQty,
                'gap'             => $physicalQty - $systemQty,
            ]);
        }

        $this->command->info('✅ Quincaillerie : 5 devis, 4 bons commande, 8 ventes, 1 inventaire');
    }

    // ─────────────────────────────────────────────────
    //  6. MODULE BOUTIQUE / POS
    // ─────────────────────────────────────────────────
    private function seedBoutique(): void
    {
        $co = $this->company->id;

        // ── Variantes produits ──
        $samsung = $this->products['Samsung Galaxy A15'] ?? null;
        if ($samsung) {
            $colors = [
                ['name' => 'Galaxy A15 - Noir',  'sku' => 'GA15-BLK', 'barcode' => '8806095467891', 'attrs' => ['Couleur' => 'Noir',  'Stockage' => '128 Go']],
                ['name' => 'Galaxy A15 - Bleu',  'sku' => 'GA15-BLU', 'barcode' => '8806095467892', 'attrs' => ['Couleur' => 'Bleu',  'Stockage' => '128 Go']],
                ['name' => 'Galaxy A15 - Blanc', 'sku' => 'GA15-WHT', 'barcode' => '8806095467893', 'attrs' => ['Couleur' => 'Blanc', 'Stockage' => '128 Go']],
            ];

            foreach ($colors as $v) {
                $variant = ProductVariant::updateOrCreate(
                    ['company_id' => $co, 'sku' => $v['sku']],
                    [
                        'company_id'  => $co,
                        'product_id'  => $samsung->id,
                        'name'        => $v['name'],
                        'sku'         => $v['sku'],
                        'barcode'     => $v['barcode'],
                        'attributes'  => $v['attrs'],
                        'is_active'   => true,
                    ]
                );

                VariantStockItem::updateOrCreate(
                    ['company_id' => $co, 'product_variant_id' => $variant->id, 'depot_id' => $this->depots[0]->id],
                    ['quantity' => rand(5, 20)]
                );
            }
        }

        $boubou = $this->products['Boubou Homme Bazin'] ?? null;
        if ($boubou) {
            $sizes = [
                ['name' => 'Boubou Bazin - M', 'sku' => 'BOB-M', 'attrs' => ['Taille' => 'M', 'Tissu' => 'Bazin riche']],
                ['name' => 'Boubou Bazin - L', 'sku' => 'BOB-L', 'attrs' => ['Taille' => 'L', 'Tissu' => 'Bazin riche']],
                ['name' => 'Boubou Bazin - XL','sku' => 'BOB-XL','attrs' => ['Taille' => 'XL','Tissu' => 'Bazin riche']],
            ];

            foreach ($sizes as $v) {
                $variant = ProductVariant::updateOrCreate(
                    ['company_id' => $co, 'sku' => $v['sku']],
                    [
                        'company_id'  => $co,
                        'product_id'  => $boubou->id,
                        'name'        => $v['name'],
                        'sku'         => $v['sku'],
                        'attributes'  => $v['attrs'],
                        'is_active'   => true,
                    ]
                );

                VariantStockItem::updateOrCreate(
                    ['company_id' => $co, 'product_variant_id' => $variant->id, 'depot_id' => $this->depots[0]->id],
                    ['quantity' => rand(3, 12)]
                );
            }
        }

        // ── Promotions ──
        if ($samsung) {
            Promotion::updateOrCreate(
                ['company_id' => $co, 'product_id' => $samsung->id, 'name' => 'Promo Tabaski Galaxy'],
                [
                    'company_id'  => $co,
                    'product_id'  => $samsung->id,
                    'name'        => 'Promo Tabaski Galaxy',
                    'type'        => 'percentage',
                    'value'       => 10,
                    'start_date'  => now()->subDays(3)->toDateString(),
                    'end_date'    => now()->addDays(12)->toDateString(),
                    'is_active'   => true,
                ]
            );
        }

        $nivea = $this->products['Crème Nivea 400ml'] ?? null;
        if ($nivea) {
            Promotion::updateOrCreate(
                ['company_id' => $co, 'product_id' => $nivea->id, 'name' => 'Nivea -500 FCFA'],
                [
                    'company_id'  => $co,
                    'product_id'  => $nivea->id,
                    'name'        => 'Nivea -500 FCFA',
                    'type'        => 'fixed',
                    'value'       => 500,
                    'start_date'  => now()->subDays(7)->toDateString(),
                    'end_date'    => now()->addDays(7)->toDateString(),
                    'is_active'   => true,
                ]
            );
        }

        // ── Programme fidélité ──
        LoyaltyConfig::updateOrCreate(
            ['company_id' => $co],
            [
                'company_id'           => $co,
                'points_per_amount'    => 100,    // 1 point par 100 FCFA
                'amount_per_point'     => 100,
                'redemption_threshold' => 500,    // Minimum 500 points
                'redemption_value'     => 5000,   // 500 pts = 5000 FCFA
                'is_active'            => true,
            ]
        );

        // Transactions fidélité pour quelques clients
        foreach (array_slice($this->customers, 0, 4) as $customer) {
            LoyaltyTransaction::create([
                'company_id'  => $co,
                'customer_id' => $customer->id,
                'type'        => 'earn',
                'points'      => rand(50, 300),
                'monetary_value' => 0,
                'description' => 'Points gagnés sur achats',
            ]);
        }

        // ── Transfert inter-dépôts ──
        $transfer = DepotTransfer::create([
            'company_id'   => $co,
            'from_depot_id'=> $this->depots[0]->id,
            'to_depot_id'  => $this->depots[1]->id,
            'user_id'      => $this->magasinier->id,
            'reference'    => 'TR-' . now()->subDays(5)->format('Ymd') . '-0001',
            'status'       => 'completed',
            'notes'        => 'Réapprovisionnement dépôt Parcelles',
        ]);

        $transferProducts = array_slice(array_values($this->products), 0, 3);
        foreach ($transferProducts as $product) {
            DepotTransferItem::create([
                'depot_transfer_id' => $transfer->id,
                'product_id'        => $product->id,
                'quantity'          => rand(5, 20),
            ]);
        }

        $this->command->info('✅ Boutique : 6 variantes, 2 promos, fidélité, 1 transfert');
    }

    // ─────────────────────────────────────────────────
    //  7. MODULE LOCATION
    // ─────────────────────────────────────────────────
    private function seedLocation(): void
    {
        $co = $this->company->id;

        // ── Biens locatifs ──
        $assetsData = [
            ['name' => 'Appartement F3 Almadies',       'type' => 'real_estate', 'daily' => null,   'monthly' => 350000, 'desc' => 'Bel appartement F3 meublé, vue mer, 2 chambres',     'chars' => ['Surface' => '85 m²', 'Étage' => '3ème', 'Chambres' => '2', 'Parking' => 'Oui']],
            ['name' => 'Appartement F2 Mermoz',          'type' => 'real_estate', 'daily' => null,   'monthly' => 200000, 'desc' => 'Appartement F2 au cœur de Mermoz, proche ambassade', 'chars' => ['Surface' => '55 m²', 'Étage' => '1er', 'Chambres' => '1', 'Climatisé' => 'Oui']],
            ['name' => 'Villa 4 pièces Ngor',            'type' => 'real_estate', 'daily' => null,   'monthly' => 600000, 'desc' => 'Villa de standing avec jardin et piscine',           'chars' => ['Surface' => '200 m²', 'Chambres' => '3', 'Jardin' => 'Oui', 'Piscine' => 'Oui']],
            ['name' => 'Bureau Centre-ville (50m²)',     'type' => 'real_estate', 'daily' => null,   'monthly' => 250000, 'desc' => 'Bureau open-space climatisé au Plateau',             'chars' => ['Surface' => '50 m²', 'Climatisation' => 'Centrale', 'Internet' => 'Fibre']],
            ['name' => 'Toyota Hilux 4x4 2024',         'type' => 'vehicle',     'daily' => 45000,  'monthly' => 900000, 'desc' => 'Pick-up double cabine, 4x4, diesel automatique',    'chars' => ['Marque' => 'Toyota', 'Modèle' => 'Hilux', 'Année' => '2024', 'Carburant' => 'Diesel', 'Km' => '12 000']],
            ['name' => 'Renault Duster 2023',            'type' => 'vehicle',     'daily' => 30000,  'monthly' => 600000, 'desc' => 'SUV compact, essence, idéal pour la ville',         'chars' => ['Marque' => 'Renault', 'Modèle' => 'Duster', 'Année' => '2023', 'Carburant' => 'Essence']],
            ['name' => 'Bus 30 places Hyundai',          'type' => 'vehicle',     'daily' => 80000,  'monthly' => null,    'desc' => 'Bus de transport 30 places, climatisé',            'chars' => ['Marque' => 'Hyundai', 'Places' => '30', 'Climatisé' => 'Oui']],
            ['name' => 'Groupe électrogène 100 KVA',     'type' => 'equipment',   'daily' => 25000,  'monthly' => 500000, 'desc' => 'Groupe électrogène diesel, démarrage automatique',  'chars' => ['Puissance' => '100 KVA', 'Carburant' => 'Diesel', 'Marque' => 'Caterpillar']],
            ['name' => 'Grue mobile 25T',                'type' => 'equipment',   'daily' => 150000, 'monthly' => null,    'desc' => 'Grue mobile tout-terrain pour chantiers BTP',      'chars' => ['Capacité' => '25 Tonnes', 'Portée' => '30 m']],
            ['name' => 'Bétonnière 350L',                'type' => 'equipment',   'daily' => 15000,  'monthly' => 300000, 'desc' => 'Bétonnière électrique professionnelle',             'chars' => ['Capacité' => '350 L', 'Moteur' => 'Électrique']],
        ];

        $assets = [];
        foreach ($assetsData as $i => $a) {
            $status = $i < 6 ? ($i < 4 ? ['available', 'rented', 'rented', 'available'][$i] : 'rented') : 'available';
            if ($i === 7) $status = 'maintenance';

            $assets[] = RentalAsset::updateOrCreate(
                ['company_id' => $co, 'name' => $a['name']],
                [
                    'company_id'       => $co,
                    'name'             => $a['name'],
                    'description'      => $a['desc'],
                    'type'             => $a['type'],
                    'daily_rate'       => $a['daily'],
                    'monthly_rate'     => $a['monthly'],
                    'status'           => $status,
                    'characteristics'  => $a['chars'],
                    'images'           => [],
                    'documents'        => [],
                    'is_active'        => true,
                ]
            );
        }

        // ── Contrats ──
        $contractsData = [
            // Appartement F2 Mermoz — actif
            ['asset' => 1, 'customer' => 0, 'ref' => 'LOC-00001', 'start' => now()->subMonths(4), 'end' => now()->addMonths(8), 'total' => 2400000, 'deposit' => 400000, 'freq' => 'monthly', 'status' => 'active'],
            // Appartement F3 Almadies — actif
            ['asset' => 2, 'customer' => 3, 'ref' => 'LOC-00002', 'start' => now()->subMonths(2), 'end' => now()->addMonths(10),'total' => 4200000, 'deposit' => 700000, 'freq' => 'monthly', 'status' => 'active'],
            // Toyota Hilux — actif
            ['asset' => 4, 'customer' => 8, 'ref' => 'LOC-00003', 'start' => now()->subMonths(1), 'end' => now()->addMonths(5), 'total' => 5400000, 'deposit' => 900000, 'freq' => 'monthly', 'status' => 'active'],
            // Renault Duster — actif, expire bientôt
            ['asset' => 5, 'customer' => 4, 'ref' => 'LOC-00004', 'start' => now()->subMonths(6), 'end' => now()->addDays(5),   'total' => 3600000, 'deposit' => 600000, 'freq' => 'monthly', 'status' => 'active'],
            // Grue — ponctuel terminé
            ['asset' => 8, 'customer' => 3, 'ref' => 'LOC-00005', 'start' => now()->subDays(30),  'end' => now()->subDays(10),  'total' => 3000000, 'deposit' => 500000, 'freq' => 'one_time','status' => 'completed'],
        ];

        $contracts = [];
        foreach ($contractsData as $cd) {
            $contract = RentalContract::updateOrCreate(
                ['company_id' => $co, 'reference' => $cd['ref']],
                [
                    'company_id'       => $co,
                    'rental_asset_id'  => $assets[$cd['asset']]->id,
                    'customer_id'      => $this->customers[$cd['customer']]->id,
                    'user_id'          => $this->owner->id,
                    'reference'        => $cd['ref'],
                    'start_date'       => $cd['start']->toDateString(),
                    'end_date'         => $cd['end']->toDateString(),
                    'total_amount'     => $cd['total'],
                    'deposit_amount'   => $cd['deposit'],
                    'deposit_returned' => $cd['status'] === 'completed',
                    'deposit_returned_amount' => $cd['status'] === 'completed' ? $cd['deposit'] : 0,
                    'payment_frequency'=> $cd['freq'],
                    'status'           => $cd['status'],
                    'conditions'       => 'Paiement mensuel avant le 5 du mois. Caution remboursable à la fin du contrat.',
                ]
            );
            $contracts[] = $contract;

            // Générer les paiements pour les contrats mensuels
            if ($cd['freq'] === 'monthly') {
                $months   = max(1, (int) $cd['start']->diffInMonths($cd['end']));
                $monthly  = round($cd['total'] / $months);
                $current  = $cd['start']->copy();

                for ($m = 0; $m < $months && $m < 12; $m++) {
                    $dueDate  = $current->copy()->addMonths($m)->startOfMonth()->addDays(4);
                    $isPast   = $dueDate->isPast();
                    $isRecent = $dueDate->between(now()->subDays(10), now());

                    $status = 'pending';
                    $amountPaid = 0;
                    $payDate    = null;
                    $method     = null;

                    if ($isPast && !$isRecent) {
                        // Passé → payé
                        $status     = 'paid';
                        $amountPaid = $monthly;
                        $payDate    = $dueDate->copy()->subDays(rand(0, 3))->toDateString();
                        $method     = ['cash', 'transfer', 'wave'][rand(0, 2)];
                    } elseif ($isPast && $isRecent && rand(0, 1)) {
                        // Récent → partiel ou en retard
                        $status     = rand(0, 1) ? 'partial' : 'overdue';
                        $amountPaid = $status === 'partial' ? round($monthly * 0.5) : 0;
                        $payDate    = $status === 'partial' ? now()->subDays(rand(1, 3))->toDateString() : null;
                        $method     = $status === 'partial' ? 'wave' : null;
                    }

                    RentalPayment::create([
                        'company_id'          => $co,
                        'rental_contract_id'  => $contract->id,
                        'amount'              => $monthly,
                        'amount_paid'         => $amountPaid,
                        'due_date'            => $dueDate->toDateString(),
                        'payment_date'        => $payDate,
                        'status'              => $status,
                        'method'              => $method,
                        'reference'           => $cd['ref'] . '-M' . str_pad($m + 1, 2, '0', STR_PAD_LEFT),
                    ]);
                }
            } else {
                // one_time
                RentalPayment::create([
                    'company_id'         => $co,
                    'rental_contract_id' => $contract->id,
                    'amount'             => $cd['total'],
                    'amount_paid'        => $cd['total'],
                    'due_date'           => $cd['start']->toDateString(),
                    'payment_date'       => $cd['start']->toDateString(),
                    'status'             => 'paid',
                    'method'             => 'transfer',
                    'reference'          => $cd['ref'] . '-FULL',
                ]);
            }
        }

        $this->command->info('✅ Location : ' . count($assets) . ' biens, ' . count($contracts) . ' contrats avec échéanciers');
    }

    // ─────────────────────────────────────────────────
    //  8. DÉPENSES
    // ─────────────────────────────────────────────────
    private function seedExpenses(): void
    {
        $co      = $this->company->id;
        $expCats = ExpenseCategory::where('company_id', $co)->get();

        $expensesData = [
            ['cat' => 'Loyer',                 'amount' => 300000, 'desc' => 'Loyer mensuel boutique Sandaga',      'date' => now()->subDays(25)],
            ['cat' => 'Loyer',                 'amount' => 150000, 'desc' => 'Loyer dépôt Parcelles',               'date' => now()->subDays(25)],
            ['cat' => 'Salaires',              'amount' => 450000, 'desc' => 'Salaires équipe Mars 2026',           'date' => now()->subDays(2)],
            ['cat' => 'Eau & Électricité',     'amount' => 65000,  'desc' => 'Facture Senelec Mars',                'date' => now()->subDays(10)],
            ['cat' => 'Eau & Électricité',     'amount' => 15000,  'desc' => 'Facture SDE Mars',                    'date' => now()->subDays(10)],
            ['cat' => 'Téléphone & Internet',  'amount' => 35000,  'desc' => 'Forfait Orange Business',             'date' => now()->subDays(15)],
            ['cat' => 'Transport',             'amount' => 25000,  'desc' => 'Transport livraisons semaine 12',     'date' => now()->subDays(5)],
            ['cat' => 'Transport',             'amount' => 18000,  'desc' => 'Transport livraisons semaine 11',     'date' => now()->subDays(12)],
            ['cat' => 'Fournitures bureau',    'amount' => 12000,  'desc' => 'Papier, encre, cahiers',              'date' => now()->subDays(8)],
            ['cat' => 'Maintenance',           'amount' => 45000,  'desc' => 'Réparation climatiseur boutique',     'date' => now()->subDays(18)],
            ['cat' => 'Divers',                'amount' => 8000,   'desc' => 'Frais de nettoyage',                  'date' => now()->subDays(3)],
        ];

        foreach ($expensesData as $e) {
            $cat = $expCats->firstWhere('name', $e['cat']);
            if (!$cat) continue;

            Expense::create([
                'company_id'          => $co,
                'expense_category_id' => $cat->id,
                'amount'              => $e['amount'],
                'description'         => $e['desc'],
                'supplier_id'         => null,
                'user_id'             => $this->owner->id,
                'date'                => $e['date']->toDateString(),
            ]);
        }

        $this->command->info('✅ Dépenses : ' . count($expensesData) . ' écritures');
    }
}
