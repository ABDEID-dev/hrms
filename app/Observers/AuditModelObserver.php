<?php

namespace App\Observers;

use App\Support\AuditLogger;
use Illuminate\Database\Eloquent\Model;

class AuditModelObserver
{
    public function created(Model $model): void
    {
        AuditLogger::modelCreated($model);
    }

    public function updated(Model $model): void
    {
        AuditLogger::modelUpdated($model);
    }

    public function deleted(Model $model): void
    {
        AuditLogger::modelDeleted($model);
    }

    public function restored(Model $model): void
    {
        AuditLogger::log('restored', class_basename($model).' restored', [
            'model' => $model::class,
            'model_id' => $model->getKey(),
        ]);
    }
}
