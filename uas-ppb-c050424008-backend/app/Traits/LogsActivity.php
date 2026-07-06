<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function booted()
    {
        static::created(function ($model) {
            self::recordActivity('created', $model, null, null);
        });

        static::updated(function ($model) {
            $changes = [];
            foreach ($model->getChanges() as $key => $value) {
                $changes[$key] = [
                    'old' => $model->getOriginal($key),
                    'new' => $value,
                ];
            }

            // Log perubahan status khusus untuk ProgressProject
            if ($model instanceof \App\Models\ProgressProject && isset($changes['status'])) {
                self::recordActivity('status_changed', $model, $changes['status']['old'], $changes['status']['new']);
            } else {
                self::recordActivity('updated', $model, null, null, $changes);
            }
        });

        static::deleted(function ($model) {
            self::recordActivity('deleted', $model, null, null);
        });
    }

    protected static function recordActivity($action, $model, $oldValue = null, $newValue = null, $changes = null)
    {
        $user = Auth::user();

        ActivityLog::create([
            'user_id' => $user?->id,
            'action' => $action,
            'model' => get_class($model),
            'model_id' => $model->id,
            'changes' => $changes,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
