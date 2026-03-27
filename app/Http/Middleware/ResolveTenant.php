<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    /**
     * Résout le tenant courant depuis le sous-domaine.
     *
     * En local (localhost / 127.0.0.1) on accepte un paramètre de query
     * ?_tenant=slug pour faciliter les tests sans DNS wildcard.
     *
     * En production : {slug}.khayma.com
     */
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $this->extractSlug($request);

        if (! $slug) {
            abort(404, 'Tenant introuvable.');
        }

        $company = Company::where('slug', $slug)
                          ->where('is_active', true)
                          ->first();

        if (! $company) {
            abort(404, 'Entreprise introuvable ou désactivée.');
        }

        // Injecter le tenant dans le conteneur IoC
        App::instance('currentCompany', $company);

        // Partager avec toutes les vues Inertia/Blade
        inertia()->share('currentCompany', [
            'id'                  => $company->id,
            'name'                => $company->name,
            'slug'                => $company->slug,
            'logo_url'            => $company->logo_url,
            'subscription_status' => $company->subscription_status,
            'trial_ends_at'       => $company->trial_ends_at?->toISOString(),
        ]);

        // Share active modules for sidebar visibility
        inertia()->share('activeModules', $company->modules()->get(['modules.id', 'modules.name', 'modules.code']));

        return $next($request);
    }

    /**
     * Extrait le slug du tenant depuis le sous-domaine ou du paramètre ?_tenant.
     */
    private function extractSlug(Request $request): ?string
    {
        $host = $request->getHost(); // ex: restaurantawa.khayma.com

        // Mode développement local
        if (in_array($host, ['localhost', '127.0.0.1'])) {
            return $request->query('_tenant');
        }

        // Production : extraire le premier segment du domaine
        $parts = explode('.', $host);

        // On attend au moins 3 segments : slug.khayma.com
        if (count($parts) < 3) {
            return null;
        }

        $slug = $parts[0];

        // Rejeter les sous-domaines réservés
        $reserved = ['www', 'admin', 'api', 'staging', 'mail', 'smtp'];
        if (in_array($slug, $reserved)) {
            return null;
        }

        return $slug;
    }
}
