<?php

namespace App\Traits;

use App\Models\AuditTrail;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            AuditTrail::record($model, 'created', null, $model->toArray(), 'Data baru dibuat');
        });

        static::updated(function ($model) {
            $oldValues = collect($model->getOriginal())
                ->only($model->getDirty() ? array_keys($model->getDirty()) : [])
                ->toArray();
            $newValues = $model->getDirty();

            if (!empty($newValues)) {
                AuditTrail::record($model, 'updated', $oldValues, $newValues, 'Data diperbarui');
            }
        });

        static::deleted(function ($model) {
            AuditTrail::record($model, 'deleted', $model->toArray(), null, 'Data dihapus');
        });
    }
}
