<?php

namespace App\Models\Concerns;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

/**
 * Trait BelongsToTenant
 *
 * À ajouter sur tous les modèles métier qui contiennent un company_id.
 * Applique automatiquement le TenantScope et injecte company_id à la création.
 *
 * Usage :
 *   use App\Models\Concerns\BelongsToTenant;
 *   class Product extends Model { use BelongsToTenant; }
 */
trait BelongsToTenant
{
    /**
     * Enregistre le global scope et l'injection automatique du company_id.
     */
    public static function bootBelongsToTenant(): void
    {
        // Applique le filtre sur toutes les requêtes SELECT
        static::addGlobalScope(new TenantScope());

        // Injecte company_id automatiquement lors de la création
        static::creating(function (Model $model): void {
            if (App::bound('currentCompany') && empty($model->company_id)) {
                $model->company_id = App::make('currentCompany')->id;
            }
        });
    }

    /**
     * Exécute une requête sans le filtre tenant (usage admin uniquement).
     */
    public static function withoutTenant(): \Illuminate\Database\Eloquent\Builder
    {
        return static::withoutGlobalScope(TenantScope::class);
    }
}
