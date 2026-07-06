<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class program_studis extends Model
{
    protected $table = 'program_studis';
    protected $fillable = ['nama_prodi', 'kode_prodi'];

    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'program_studi_id');
    }
}