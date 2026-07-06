<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswas';
    protected $fillable = ['user_id', 'nim', 'no_hp', 'kelas_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function progressProjects()
    {
        return $this->hasMany(ProgressProject::class, 'mahasiswa_id');
    }

    public function kelasMataKulahs()
    {
        return $this->hasManyThrough(
            KelasMatKuliah::class,
            Kelas::class,
            'id',
            'kelas_id',
            'kelas_id',
            'id'
        );
    }
}
