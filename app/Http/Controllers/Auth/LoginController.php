<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    /**
     * Affiche le formulaire de connexion.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Traite la tentative de connexion.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()->withErrors([
                'email' => 'Ces identifiants ne correspondent à aucun compte.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // Super admin → backoffice
        if ($user->is_super_admin) {
            return redirect()->route('admin.dashboard');
        }

        // Rediriger vers le tenant si l'utilisateur a une entreprise
        $company = $user->companies()->where('is_active', true)->first();

        if ($company) {
            return redirect()->route('app.dashboard', ['_tenant' => $company->slug]);
        }

        // Pas encore de company → créer une
        return redirect()->route('register');
    }

    /**
     * Déconnecte l'utilisateur.
     */
    public function destroy(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Force full page reload for Inertia requests to avoid app shell bleed-through
        if ($request->header('X-Inertia')) {
            return Inertia::location(route('home'));
        }

        return redirect()->route('home');
    }
}
