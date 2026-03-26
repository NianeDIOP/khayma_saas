<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\CompanyController as AdminCompany;
use App\Http\Controllers\Admin\UserController as AdminUser;
use Illuminate\Support\Facades\Route;

// ── Site public ──────────────────────────────────────────────────
Route::get('/', fn () => view('welcome'))->name('home');

// ── Authentification ─────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',    [LoginController::class,    'create'])->name('login');
    Route::post('/login',   [LoginController::class,    'store'])->name('login.store');
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register',[RegisterController::class, 'store'])->name('register.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// ── Espace authentifié (hors tenant) ─────────────────────────────
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', fn () => inertia('Dashboard'))->name('dashboard');
});

// ── Espace tenant (sous-domaine résolu) ──────────────────────────
// Toutes les routes métier passent par ResolveTenant + CheckSubscription
// Ces routes seront développées module par module (Phase 2+)
Route::middleware(['tenant', 'auth', 'subscription'])
     ->prefix('app')
     ->name('app.')
     ->group(function () {
         // Tableau de bord tenant
         Route::get('/', fn () => inertia('App/Dashboard'))->name('dashboard');
     });

// ── Backoffice Super Admin ────────────────────────────────────────
Route::middleware(['auth', 'admin'])
     ->prefix('admin')
     ->name('admin.')
     ->group(function () {
         Route::get('/',           [AdminDashboard::class, 'index'])->name('dashboard');
         Route::get('/companies',  [AdminCompany::class,  'index'])->name('companies.index');
         Route::get('/companies/{company}', [AdminCompany::class, 'show'])->name('companies.show');
         Route::patch('/companies/{company}/toggle',       [AdminCompany::class, 'toggle'])->name('companies.toggle');
         Route::patch('/companies/{company}/subscription', [AdminCompany::class, 'updateSubscription'])->name('companies.subscription');
         Route::get('/users',      [AdminUser::class,     'index'])->name('users.index');
     });

