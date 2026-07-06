<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tahun_akademiks extends Model
{
    protected $table = 'tahun_akademiks';
    protected $fillable = ['nama', 'status_aktif'];

    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'tahun_akademik_id');
    }
}