<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Filtre automatiquement toutes les requêtes par company_id.
 * À utiliser sur tous les modèles métier (Product, Sale, Client, etc.)
 */
trait HasTenant
{
    protected static function bootHasTenant(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && auth()->user()->company_id) {
                $builder->where(
                    (new static)->getTable() . '.company_id',
                    auth()->user()->company_id
                );
            }
        });

        static::creating(function ($model) {
            if (auth()->check() && !$model->company_id) {
                $model->company_id = auth()->user()->company_id;
            }
        });
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->withoutGlobalScope('tenant')
                     ->where($this->getTable() . '.company_id', $companyId);
    }
}
