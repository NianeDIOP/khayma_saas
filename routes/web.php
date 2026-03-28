<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\CompanyController as AdminCompany;
use App\Http\Controllers\Admin\UserController as AdminUser;
use App\Http\Controllers\Admin\PlanController as AdminPlan;
use App\Http\Controllers\Admin\ModuleController as AdminModule;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscription;
use App\Http\Controllers\Admin\AuditLogController as AdminAuditLog;
use App\Http\Controllers\Admin\SettingController as AdminSetting;
use App\Http\Controllers\Admin\LegalPageController as AdminLegalPage;
use App\Http\Controllers\App\DashboardController as AppDashboard;
use App\Http\Controllers\App\OnboardingController;
use App\Http\Controllers\App\SettingsController;
use App\Http\Controllers\App\CustomerController;
use App\Http\Controllers\App\SupplierController;
use App\Http\Controllers\App\CategoryController;
use App\Http\Controllers\App\UnitController;
use App\Http\Controllers\App\ProductController;
use App\Http\Controllers\App\DepotController;
use App\Http\Controllers\App\StockController;
use App\Http\Controllers\App\SaleController;
use App\Http\Controllers\App\ExpenseCategoryController;
use App\Http\Controllers\App\ExpenseController;
use App\Http\Controllers\App\Restaurant\CategoryController as RestaurantCategoryController;
use App\Http\Controllers\App\Restaurant\DishController;
use App\Http\Controllers\App\Restaurant\ServiceController;
use App\Http\Controllers\App\Restaurant\CashSessionController;
use App\Http\Controllers\App\Restaurant\OrderController;
use App\Http\Controllers\App\Restaurant\ReportController as RestaurantReportController;
use App\Http\Controllers\App\Quincaillerie\QuoteController;
use App\Http\Controllers\App\Quincaillerie\PurchaseOrderController;
use App\Http\Controllers\App\Quincaillerie\SupplierPaymentController;
use App\Http\Controllers\App\Quincaillerie\SupplierReturnController;
use App\Http\Controllers\App\Quincaillerie\CreditController;
use App\Http\Controllers\App\Quincaillerie\InventoryController;
use App\Http\Controllers\App\Quincaillerie\ReportController as QuincaillerieReportController;
use App\Http\Controllers\App\Quincaillerie\PosController as QuincailleriePosController;
use App\Http\Controllers\App\Boutique\PosController;
use App\Http\Controllers\App\Boutique\VariantController;
use App\Http\Controllers\App\Boutique\PromotionController;
use App\Http\Controllers\App\Boutique\LoyaltyController;
use App\Http\Controllers\App\Boutique\TransferController;
use App\Http\Controllers\App\Boutique\ReportController as BoutiqueReportController;
use App\Http\Controllers\App\Location\AssetController as LocationAssetController;
use App\Http\Controllers\App\Location\ContractController;
use App\Http\Controllers\App\Location\PaymentController as RentalPaymentController;
use App\Http\Controllers\App\Location\CalendarController;
use App\Http\Controllers\App\Location\ReportController as LocationReportController;
use App\Http\Controllers\App\TeamController;
use App\Http\Controllers\App\NotificationController;
use App\Http\Controllers\App\PdfController;
use App\Http\Controllers\App\ExportImportController;
use App\Http\Controllers\App\ReportController;
use Illuminate\Support\Facades\Route;

// ── Site public ──────────────────────────────────────────────────
Route::get('/', fn () => view('welcome'))->name('home');

// ── Authentification ─────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class,    'create'])->name('login');
    Route::post('/login',   [LoginController::class,    'store'])->name('login.store')->middleware('throttle:login');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register',[RegisterController::class, 'store'])->name('register.store')->middleware('throttle:login');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ── Espace authentifié (hors tenant) ─────────────────────────────
// Redirige /dashboard vers admin ou accueil selon le rôle
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        if (auth()->user()->is_super_admin) {
            return redirect()->route('admin.dashboard');
        }
        $company = auth()->user()->companies()->where('is_active', true)->first();
        if ($company) {
            return redirect()->route('app.dashboard', ['_tenant' => $company->slug]);
        }
        return redirect()->route('register');
    })->name('dashboard');
});

// ── Espace tenant (sous-domaine résolu) ──────────────────────────
// Toutes les routes métier passent par ResolveTenant + CheckSubscription
// Ces routes seront développées module par module (Phase 2+)
Route::middleware(['tenant', 'auth', 'subscription'])
     ->prefix('app')
     ->name('app.')
     ->group(function () {
         // Tableau de bord tenant
         Route::get('/',             [AppDashboard::class,    'index'])->name('dashboard');

         // Rapports globaux (CDC §15)
         Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

         // Onboarding (compléter le profil entreprise)
         Route::get('/onboarding',   [OnboardingController::class, 'index'])->name('onboarding');
         Route::put('/onboarding',   [OnboardingController::class, 'update'])->name('onboarding.update');

         // Paramètres entreprise
         Route::get('/settings',     [SettingsController::class, 'index'])->name('settings');
         Route::put('/settings',     [SettingsController::class, 'update'])->name('settings.update');

         // Équipe (gestion des membres)
         Route::get('/team',              [TeamController::class, 'index'])->name('team.index');
         Route::get('/team/create',       [TeamController::class, 'create'])->name('team.create');
         Route::post('/team',             [TeamController::class, 'store'])->name('team.store');
         Route::get('/team/{user}/edit',  [TeamController::class, 'edit'])->name('team.edit');
         Route::put('/team/{user}',       [TeamController::class, 'update'])->name('team.update');
         Route::delete('/team/{user}',    [TeamController::class, 'destroy'])->name('team.destroy');

         // Notifications
         Route::get('/notifications',                    [NotificationController::class, 'index'])->name('notifications.index');
         Route::get('/notifications/unread-count',       [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
         Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
         Route::post('/notifications/read-all',          [NotificationController::class, 'markAllRead'])->name('notifications.read-all');

         // PDF exports
         Route::get('/pdf/sale/{sale}',     [PdfController::class, 'sale'])->name('pdf.sale');
         Route::get('/pdf/quote/{quote}',   [PdfController::class, 'quote'])->name('pdf.quote');
         Route::get('/pdf/contract/{contract}', [PdfController::class, 'contract'])->name('pdf.contract');

         // Exports Excel
         Route::get('/export/products',  [ExportImportController::class, 'exportProducts'])->name('export.products');
         Route::get('/export/customers', [ExportImportController::class, 'exportCustomers'])->name('export.customers');
         Route::get('/export/suppliers', [ExportImportController::class, 'exportSuppliers'])->name('export.suppliers');

         // Imports Excel
         Route::post('/import/products',  [ExportImportController::class, 'importProducts'])->name('import.products');
         Route::post('/import/customers', [ExportImportController::class, 'importCustomers'])->name('import.customers');
         Route::post('/import/suppliers', [ExportImportController::class, 'importSuppliers'])->name('import.suppliers');

         // Clients
         Route::resource('customers', CustomerController::class);

         // Fournisseurs
         Route::resource('suppliers', SupplierController::class);

         // Catégories
         Route::resource('categories', CategoryController::class)->except(['show']);

         // Unités de mesure
         Route::resource('units', UnitController::class)->except(['show']);

         // Produits
         Route::resource('products', ProductController::class);

         // Dépôts
         Route::resource('depots', DepotController::class)->except(['show']);

         // Stock
         Route::get('/stock',                   [StockController::class, 'index'])->name('stock.index');
         Route::get('/stock/movements',          [StockController::class, 'movements'])->name('stock.movements');
         Route::get('/stock/movements/create',   [StockController::class, 'createMovement'])->name('stock.create-movement');
         Route::post('/stock/movements',         [StockController::class, 'storeMovement'])->name('stock.store-movement');

         // Ventes
         Route::resource('sales', SaleController::class)->except(['edit', 'update']);

         // Catégories de dépenses
         Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show']);

         // Dépenses
         Route::resource('expenses', ExpenseController::class);

         // ── Module Restaurant ────────────────────────────────────
         Route::prefix('restaurant')->name('restaurant.')->group(function () {
             // Catégories de menu
             Route::resource('categories', RestaurantCategoryController::class)->except(['show']);

             // Plats
             Route::resource('dishes', DishController::class)->except(['show']);

             // Services (matin/midi/soir)
             Route::resource('services', ServiceController::class)->except(['show']);

             // Caisse (ouverture/fermeture)
             Route::get('/cash-sessions',                    [CashSessionController::class, 'index'])->name('cash-sessions.index');
             Route::post('/cash-sessions/open',              [CashSessionController::class, 'open'])->name('cash-sessions.open');
             Route::post('/cash-sessions/{cashSession}/close', [CashSessionController::class, 'close'])->name('cash-sessions.close');

             // Commandes
             Route::get('/orders',          [OrderController::class, 'index'])->name('orders.index');
             Route::get('/orders/create',   [OrderController::class, 'create'])->name('orders.create');
             Route::post('/orders',         [OrderController::class, 'store'])->name('orders.store');
             Route::get('/orders/{order}',  [OrderController::class, 'show'])->name('orders.show');
             Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');

             // Rapports
             Route::get('/reports', [RestaurantReportController::class, 'index'])->name('reports.index');
         });

         // ── Module Quincaillerie ─────────────────────────────────
         Route::prefix('quincaillerie')->name('quincaillerie.')->group(function () {             // POS (point de vente)
             Route::get('/pos',                [QuincailleriePosController::class, 'index'])->name('pos.index');
             Route::post('/pos',               [QuincailleriePosController::class, 'store'])->name('pos.store');
             Route::get('/pos/{sale}/receipt',  [QuincailleriePosController::class, 'receipt'])->name('pos.receipt');
             // Devis
             Route::resource('quotes', QuoteController::class);
             Route::patch('/quotes/{quote}/status', [QuoteController::class, 'updateStatus'])->name('quotes.update-status');
             Route::post('/quotes/{quote}/convert', [QuoteController::class, 'convert'])->name('quotes.convert');

             // Bons de commande fournisseur
             Route::resource('purchase-orders', PurchaseOrderController::class);
             Route::patch('/purchase-orders/{purchase_order}/status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.update-status');
             Route::post('/purchase-orders/{purchase_order}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');

             // Paiements fournisseur
             Route::get('/supplier-payments',       [SupplierPaymentController::class, 'index'])->name('supplier-payments.index');
             Route::get('/supplier-payments/create', [SupplierPaymentController::class, 'create'])->name('supplier-payments.create');
             Route::post('/supplier-payments',      [SupplierPaymentController::class, 'store'])->name('supplier-payments.store');

             // Retours fournisseur
             Route::get('/supplier-returns',       [SupplierReturnController::class, 'index'])->name('supplier-returns.index');
             Route::get('/supplier-returns/create', [SupplierReturnController::class, 'create'])->name('supplier-returns.create');
             Route::post('/supplier-returns',      [SupplierReturnController::class, 'store'])->name('supplier-returns.store');

             // Crédits clients
             Route::get('/credits',                    [CreditController::class, 'index'])->name('credits.index');
             Route::post('/credits/{sale}/payment',    [CreditController::class, 'addPayment'])->name('credits.add-payment');

             // Inventaires
             Route::get('/inventories',                        [InventoryController::class, 'index'])->name('inventories.index');
             Route::get('/inventories/create',                 [InventoryController::class, 'create'])->name('inventories.create');
             Route::post('/inventories',                       [InventoryController::class, 'store'])->name('inventories.store');
             Route::get('/inventories/{inventory}',            [InventoryController::class, 'show'])->name('inventories.show');
             Route::post('/inventories/{inventory}/validate',  [InventoryController::class, 'validate'])->name('inventories.validate');

             // Rapports Quincaillerie
             Route::get('/reports', [QuincaillerieReportController::class, 'index'])->name('reports.index');
         });

         // ── Module Boutique / POS ─────────────────────────────────
         Route::prefix('boutique')->name('boutique.')->group(function () {
             // POS (caisse)
             Route::get('/pos',          [PosController::class, 'index'])->name('pos.index');
             Route::post('/pos',         [PosController::class, 'store'])->name('pos.store');
             Route::get('/pos/{sale}/receipt', [PosController::class, 'receipt'])->name('pos.receipt');

             // Variantes produits
             Route::resource('variants', VariantController::class)->except(['show']);

             // Promotions
             Route::resource('promotions', PromotionController::class)->except(['show']);

             // Fidélité
             Route::get('/loyalty',         [LoyaltyController::class, 'index'])->name('loyalty.index');
             Route::put('/loyalty/config',  [LoyaltyController::class, 'updateConfig'])->name('loyalty.update-config');

             // Transferts inter-dépôts
             Route::get('/transfers',          [TransferController::class, 'index'])->name('transfers.index');
             Route::get('/transfers/create',   [TransferController::class, 'create'])->name('transfers.create');
             Route::post('/transfers',         [TransferController::class, 'store'])->name('transfers.store');
             Route::get('/transfers/{transfer}', [TransferController::class, 'show'])->name('transfers.show');

             // Rapports Boutique
             Route::get('/reports', [BoutiqueReportController::class, 'index'])->name('reports.index');
         });

         // ── Module Location ──────────────────────────────────────
         Route::prefix('location')->name('location.')->group(function () {
             // Biens locatifs
             Route::resource('assets', LocationAssetController::class);

             // Contrats
             Route::get('/contracts',               [ContractController::class, 'index'])->name('contracts.index');
             Route::get('/contracts/create',         [ContractController::class, 'create'])->name('contracts.create');
             Route::post('/contracts',               [ContractController::class, 'store'])->name('contracts.store');
             Route::get('/contracts/{contract}',     [ContractController::class, 'show'])->name('contracts.show');
             Route::patch('/contracts/{contract}/status', [ContractController::class, 'updateStatus'])->name('contracts.update-status');
             Route::post('/contracts/{contract}/renew',   [ContractController::class, 'renew'])->name('contracts.renew');
             Route::post('/contracts/{contract}/return-deposit', [ContractController::class, 'returnDeposit'])->name('contracts.return-deposit');

             // Paiements locatifs
             Route::get('/payments',                     [RentalPaymentController::class, 'index'])->name('payments.index');
             Route::post('/payments/{payment}/record',   [RentalPaymentController::class, 'recordPayment'])->name('payments.record');
             Route::post('/payments/mark-overdue',       [RentalPaymentController::class, 'markOverdue'])->name('payments.mark-overdue');

             // Calendrier
             Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');

             // Rapports Location
             Route::get('/reports', [LocationReportController::class, 'index'])->name('reports.index');
         });
     });

// ── Backoffice Super Admin ────────────────────────────────────────
Route::middleware(['auth', 'admin'])
     ->prefix('admin')
     ->name('admin.')
     ->group(function () {
         Route::get('/',           [AdminDashboard::class, 'index'])->name('dashboard');

         // Companies
         Route::get('/companies',  [AdminCompany::class,  'index'])->name('companies.index');
         Route::get('/companies/create', [AdminCompany::class, 'create'])->name('companies.create');
         Route::post('/companies', [AdminCompany::class, 'store'])->name('companies.store');
         Route::get('/companies/{company}', [AdminCompany::class, 'show'])->name('companies.show');
         Route::get('/companies/{company}/edit', [AdminCompany::class, 'edit'])->name('companies.edit');
         Route::put('/companies/{company}', [AdminCompany::class, 'update'])->name('companies.update');
         Route::delete('/companies/{company}', [AdminCompany::class, 'destroy'])->name('companies.destroy');
         Route::patch('/companies/{company}/toggle',       [AdminCompany::class, 'toggle'])->name('companies.toggle');
         Route::patch('/companies/{company}/subscription', [AdminCompany::class, 'updateSubscription'])->name('companies.subscription');
         Route::patch('/companies/{company}/extend-trial', [AdminCompany::class, 'extendTrial'])->name('companies.extend-trial');
         Route::post('/companies/{company}/modules',       [AdminCompany::class, 'syncModules'])->name('companies.modules');
         Route::post('/companies/{company}/reset-password', [AdminCompany::class, 'resetPassword'])->name('companies.reset-password');

         // Users
         Route::get('/users',      [AdminUser::class,     'index'])->name('users.index');
         Route::get('/users/{user}', [AdminUser::class,   'show'])->name('users.show');
         Route::patch('/users/{user}/toggle-admin', [AdminUser::class, 'toggleAdmin'])->name('users.toggle-admin');

         // Plans
         Route::resource('plans', AdminPlan::class);

         // Modules
         Route::resource('modules', AdminModule::class)->except(['show', 'destroy']);
         Route::patch('/modules/{module}/toggle', [AdminModule::class, 'toggle'])->name('modules.toggle');

         // Subscriptions
         Route::get('/subscriptions', [AdminSubscription::class, 'index'])->name('subscriptions.index');
         Route::get('/subscriptions/export', [AdminSubscription::class, 'export'])->name('subscriptions.export');

         // Audit Logs
         Route::get('/audit-logs', [AdminAuditLog::class, 'index'])->name('audit-logs.index');

         // Settings
         Route::get('/settings',   [AdminSetting::class, 'index'])->name('settings.index');
         Route::put('/settings',   [AdminSetting::class, 'update'])->name('settings.update');

         // Legal Pages
         Route::get('/legal-pages',  [AdminLegalPage::class, 'index'])->name('legal-pages.index');
         Route::put('/legal-pages',  [AdminLegalPage::class, 'update'])->name('legal-pages.update');
     });

