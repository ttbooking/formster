<?php

declare(strict_types=1);

namespace TTBooking\Formster\Observers;

use Illuminate\Database\Eloquent\Model;
use TTBooking\Formster\Types\File;

class OrphanedFileCollector
{
    /**
     * Cleanup orphaned uploaded files.
     */
    public function deleted(Model $model): void
    {
        if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
            return;
        }

        foreach ($model->attributesToArray() as $maybeFile) {
            $maybeFile instanceof File && ! str_starts_with($maybeFile->name, '/') && $maybeFile->delete();
        }
    }
}
