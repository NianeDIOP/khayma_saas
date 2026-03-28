<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Vérifie que le tenant a un abonnement actif ou un essai en cours.
     *
     * Statuts autorisés  : active, trial
     * Statuts bloquants  : expired, suspended, cancelled
     * Statut "grace"     : grace_period → accès lecture seule (à gérer côté frontend)
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! App::bound('currentCompany')) {
            return $next($request);
        }

        // Keep renewal/payment routes reachable even when access is blocked by subscription state.
        if ($request->routeIs('app.payment.*')) {
            return $next($request);
        }

        $company = App::make('currentCompany');

        // Vérifications dans l'ordre de priorité
        match ($company->subscription_status) {
            'active'       => null,                                   // OK
            'trial'        => $this->checkTrialExpiry($company),      // Vérif date
            'grace_period' => $this->handleGracePeriod($request),     // Lecture seule
            default        => $this->blockAccess($company),           // Bloqué
        };

        return $next($request);
    }

    /**
     * Vérifie si la période d'essai est encore valide.
     */
    private function checkTrialExpiry(object $company): void
    {
        if ($company->trial_ends_at && $company->trial_ends_at->isPast()) {
            // Mettre à jour le statut en base
            $company->update(['subscription_status' => 'expired']);
            $this->blockAccess($company);
        }
    }

    /**
     * En grace_period : les requêtes d'écriture (POST/PUT/PATCH/DELETE)
     * sont bloquées, les lectures (GET/HEAD) passent.
     */
    private function handleGracePeriod(Request $request): void
    {
        if (! $request->isMethodSafe()) {
            abort(403, 'Votre abonnement a expiré. Veuillez renouveler pour continuer.');
        }
    }

    /**
     * Bloque totalement l'accès et redirige vers la page de renouvellement.
     */
    private function blockAccess(object $company): never
    {
        if (request()->expectsJson()) {
            abort(402, 'Abonnement requis. Veuillez renouveler votre abonnement Khayma.');
        }

        abort(402, 'Votre accès a été suspendu. Contactez le support Khayma.');
    }
}
