<?php

namespace App\Traits;

/**
 * Enregistre automatiquement chaque création/modification/suppression
 * dans la table audit_logs (créée en Phase 1B).
 */
trait HasAuditLog
{
    protected static function bootHasAuditLog(): void
    {
        foreach (['created', 'updated', 'deleted'] as $action) {
            static::$action(function ($model) use ($action) {
                if (!class_exists(\App\Models\AuditLog::class)) return;

                try {
                    $companyId = $model->company_id
                        ?? (app()->bound('currentCompany') ? app('currentCompany')->id : null);

                    \App\Models\AuditLog::create([
                        'company_id' => $companyId,
                        'user_id'    => auth()->id(),
                        'action'     => $action,
                        'model_type' => get_class($model),
                        'model_id'   => $model->getKey(),
                        'old_values' => $action === 'updated' ? array_intersect_key($model->getOriginal(), $model->getDirty()) : null,
                        'new_values' => $action !== 'deleted'  ? $model->getDirty() ?: null : null,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'created_at' => now(),
                    ]);
                } catch (\Throwable) {
                    // Never let audit logging break the main operation
                }
            });
        }
    }
}
