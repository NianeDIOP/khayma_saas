<?php

use App\Models\Company;
use Illuminate\Support\Facades\App;

if (! function_exists('current_company')) {
    /**
     * Retourne l'entreprise (tenant) courante résolue par le middleware ResolveTenant.
     *
     * @throws \RuntimeException si aucun tenant n'a été résolu
     */
    function current_company(): Company
    {
        if (! App::bound('currentCompany')) {
            throw new \RuntimeException(
                'Aucun tenant résolu. Assurez-vous que le middleware ResolveTenant est actif.'
            );
        }

        return App::make('currentCompany');
    }
}

if (! function_exists('current_company_id')) {
    /**
     * Retourne l'ID de l'entreprise courante.
     * Raccourci pratique pour les requêtes manuelles.
     */
    function current_company_id(): int
    {
        return current_company()->id;
    }
}

if (! function_exists('has_tenant')) {
    /**
     * Indique si un tenant est actuellement résolu.
     * Utile pour les commandes artisan et les contextes sans sous-domaine.
     */
    function has_tenant(): bool
    {
        return App::bound('currentCompany');
    }
}
