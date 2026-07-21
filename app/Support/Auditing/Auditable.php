<?php

namespace App\Support\Auditing;

use App\Repositories\Contracts\AuditLogRepositoryInterface;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            app(AuditLogRepositoryInterface::class)->record($model, 'created');
        });

        static::updated(function ($model) {
            $repository = app(AuditLogRepositoryInterface::class);
            $except = array_merge(['updated_at'], $model->auditExcept ?? []);
            $statusFields = $model->auditStatusFields ?? [];

            foreach ($model->getDirty() as $key => $newValue) {
                if ($key === 'updated_at') {
                    continue;
                }

                if (in_array($key, $except, true)) {
                    $repository->record($model, 'updated', $key, null, null);
                    continue;
                }

                if (in_array($key, $statusFields, true)) {
                    $repository->record($model, $newValue ? 'activated' : 'deactivated');
                    continue;
                }

                $repository->record($model, 'updated', $key, $model->getOriginal($key), $newValue);
            }
        });

        static::deleted(function ($model) {
            app(AuditLogRepositoryInterface::class)->record($model, 'deleted');
        });
    }
}
