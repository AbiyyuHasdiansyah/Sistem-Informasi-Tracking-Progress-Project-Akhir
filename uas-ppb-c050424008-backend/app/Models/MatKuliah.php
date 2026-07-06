<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatKuliah extends Model
{
    protected $table = 'mata_kuliahs';
    protected $fillable = ['nama_mk', 'kode_mk', 'sks'];

    public function kelasMataKulahs()
    {
        return $this->hasMany(KelasMatKuliah::class, 'mata_kuliah_id');
    }
}
