<?php

use Illuminate\Support\Facades\Route;

// ── Site public ──────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
})->name('home');
// Placeholder auth — sera remplacé par Fortify/Breeze en Phase 2
Route::get('/login',  fn () => view('welcome'))->name('login');
Route::get('/register', fn () => view('welcome'))->name('register');
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

