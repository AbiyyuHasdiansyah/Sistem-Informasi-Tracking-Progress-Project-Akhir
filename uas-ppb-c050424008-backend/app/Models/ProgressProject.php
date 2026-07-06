<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class ProgressProject extends Model
{
    use SoftDeletes, LogsActivity;

    protected $table = 'progress_projects';

    protected $fillable = [
        'mahasiswa_id',
        'kelas_mata_kuliah_id',
        'judul_project',
        'deskripsi_progress',
        'persentase_estimasi',
        'file_database',
        'file_laporan',
        'link_gdrive',
        'link_github',
        'link_youtube',
        'status',
        'catatan_dosen',
        'submitted_at'
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    public function kelasMataKuliah()
    {
        return $this->belongsTo(KelasMatKuliah::class, 'kelas_mata_kuliah_id');
    }

    public static function normalizeStatus(?string $status): string
    {
        $normalized = trim((string) $status);

        return match ($normalized) {
            'Disetujui', 'Diterima', 'Setuju', 'Approved' => 'Disetujui',
            'Ditolak', 'Revisi', 'Tolak', 'Rejected' => 'Ditolak',
            default => 'Pending',
        };
    }

    public function hasBeenValidated(): bool
    {
        $status = trim((string) ($this->status ?? ''));

        return $status !== '' && strtolower($status) !== 'pending';
    }
}
