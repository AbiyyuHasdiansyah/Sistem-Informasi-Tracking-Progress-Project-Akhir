<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dosen extends Model
{
    protected $table = 'dosens';
    protected $fillable = ['user_id', 'nip_nidn', 'no_hp'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function kelasMataKulahs()
    {
        return $this->hasMany(KelasMatKuliah::class, 'dosen_id');
    }
}