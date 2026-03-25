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
        foreach (['created', 'updated', 'deleted'] as $event) {
            static::$event(function ($model) use ($event) {
                if (!class_exists(\App\Models\AuditLog::class)) return;

                \App\Models\AuditLog::create([
                    'user_id'    => auth()->id(),
                    'model_type' => get_class($model),
                    'model_id'   => $model->getKey(),
                    'event'      => $event,
                    'old_values' => $event === 'updated' ? $model->getOriginal() : null,
                    'new_values' => $event !== 'deleted'  ? $model->getAttributes() : null,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            });
        }
    }
}
