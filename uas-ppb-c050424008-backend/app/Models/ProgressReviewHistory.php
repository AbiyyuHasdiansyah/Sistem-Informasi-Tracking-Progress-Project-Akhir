<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressReviewHistory extends Model
{
    protected $table = 'progress_review_histories';

    protected $fillable = [
        'progress_project_id',
        'status',
        'catatan_dosen',
        'reviewed_by',
        'reviewed_by_name',
        'reviewed_at',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function progressProject()
    {
        return $this->belongsTo(ProgressProject::class, 'progress_project_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
