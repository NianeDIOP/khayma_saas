<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends Controller
{
    /**
     * Affiche le formulaire d'inscription.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Crée le compte utilisateur + l'entreprise en essai gratuit.
     */
    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        [$user, $company] = DB::transaction(function () use ($data): array {
            // 1. Créer le compte utilisateur
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => $data['password'], // hashé automatiquement via cast
            ]);

            // 2. Générer un slug unique pour la company
            $slug = $this->generateUniqueSlug($data['company_name']);

            // 3. Créer l'entreprise en période d'essai (14 jours)
            $company = Company::create([
                'name'                => $data['company_name'],
                'slug'                => $slug,
                'email'               => $data['email'],
                'phone'               => $data['phone'] ?? null,
                'sector'              => $data['sector'],
                'subscription_status' => 'trial',
                'trial_ends_at'       => now()->addDays(14),
                'is_active'           => true,
            ]);

            // 4. Lier l'utilisateur à l'entreprise comme propriétaire
            $company->users()->attach($user->id, [
                'role'      => 'owner',
                'joined_at' => now(),
            ]);

            return [$user, $company];
        });

        // Connecter l'utilisateur
        Auth::login($user);
        $request->session()->regenerate();

        // Rediriger vers le dashboard tenant
        return redirect()->route('app.dashboard', ['_tenant' => $company->slug]);
    }

    /**
     * Génère un slug unique depuis le nom de l'entreprise.
     */
    private function generateUniqueSlug(string $name): string
    {
        $base = Str::slug($name, '-');
        $slug = $base;
        $i    = 1;

        while (Company::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
