<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'program_studi_id',
        'tahun_akademik_id',
        'nama_kelas',
        'kode_kelas',
    ];

    public function programStudi()
    {
        return $this->belongsTo(program_studis::class, 'program_studi_id');
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(tahun_akademiks::class, 'tahun_akademik_id');
    }

    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class, 'kelas_id');
    }
}
