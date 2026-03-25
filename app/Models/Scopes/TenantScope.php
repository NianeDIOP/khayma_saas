<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\App;

class TenantScope implements Scope
{
    /**
     * Applique automatiquement un filtre company_id sur toutes les requêtes
     * des modèles qui utilisent le trait BelongsToTenant.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Ne pas appliquer si aucun tenant résolu (ex: commandes artisan, tests)
        if (! App::bound('currentCompany')) {
            return;
        }

        $company = App::make('currentCompany');

        $builder->where($model->getTable() . '.company_id', $company->id);
    }
}
