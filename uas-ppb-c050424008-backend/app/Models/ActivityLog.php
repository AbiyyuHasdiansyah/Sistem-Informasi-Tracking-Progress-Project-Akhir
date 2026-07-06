<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'action',
        'model',
        'model_id',
        'changes',
        'old_value',
        'new_value',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope untuk filter berdasarkan model
     */
    public function scopeForModel($query, $modelClass, $modelId = null)
    {
        $query->where('model', $modelClass);
        
        if ($modelId) {
            $query->where('model_id', $modelId);
        }
        
        return $query;
    }

    /**
     * Scope untuk filter berdasarkan action
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }
}
