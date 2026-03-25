<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
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

