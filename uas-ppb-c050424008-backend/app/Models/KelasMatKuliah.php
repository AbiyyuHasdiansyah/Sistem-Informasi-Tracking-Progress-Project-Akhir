<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KelasMatKuliah extends Model
{
    protected $table = 'kelas_mata_kuliah';
    protected $fillable = ['kelas_id', 'mata_kuliah_id', 'dosen_id'];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MatKuliah::class, 'mata_kuliah_id');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    public function progressProjects()
    {
        return $this->hasMany(ProgressProject::class, 'kelas_mata_kuliah_id');
    }
}
