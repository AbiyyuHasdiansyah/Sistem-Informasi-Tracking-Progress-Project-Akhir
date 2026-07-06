<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\tahun_akademiks;
use App\Models\program_studis;
use App\Models\Kelas;
use App\Models\mata_kuliahs;
use App\Models\User;
use App\Models\dosens;
use App\Models\mahasiswas;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AkademikSeeder extends Seeder
{
    public function run()
    {
        // 1. Tahun Akademik (Cari dulu, kalau tidak ada baru buat)
        $ta = tahun_akademiks::firstOrCreate(
            ['nama' => '2025/2026 Genap'],
            ['status_aktif' => true]
        );

        // 2. Program Studi
        $prodi = program_studis::firstOrCreate(
            ['kode_prodi' => 'TI'],
            ['nama_prodi' => 'Teknik Informatika']
        );

        // 3. Kelas
        $kelas = Kelas::firstOrCreate(
            [
                'nama_kelas' => '3A TI',
                'tahun_akademik_id' => $ta->id,
                'program_studi_id' => $prodi->id
            ]
        );

        // 4. Mata Kuliah Project Akhir
        $mk = mata_kuliahs::firstOrCreate(
            ['kode_mk' => 'MK004'],
            [
                'nama_mk' => 'Project Akhir',
                'sks' => 4
            ]
        );

        // 5. User & Dosen Pembimbing (Gunakan firstOrCreate agar aman dari Duplicate Entry)
        $userDosen = User::firstOrCreate(
            ['email' => 'arifin@poliban.ac.id'],
            [
                'name' => 'Arifin Noor Asyikin, M.T.',
                'password' => Hash::make('password123'),
                'role' => 'Dosen'
            ]
        );

        // Cek dulu apakah NIP dosen sudah terdaftar, jika belum baru buat
        $dosen = dosens::firstOrCreate(
            ['nip_nidn' => '198701012025011001'],
            [
                'user_id' => $userDosen->id,
                'no_hp' => '08123456789'
            ]
        );

        // 6. Plotting Dosen ke Kelas & Mata Kuliah (Pivot) - Cek eksistensi sebelum insert
        $pivotExists = DB::table('kelas_mata_kuliah')
            ->where('kelas_id', $kelas->id)
            ->where('mata_kuliah_id', $mk->id)
            ->where('dosen_id', $dosen->id)
            ->exists();

        if (!$pivotExists) {
            DB::table('kelas_mata_kuliah')->insert([
                'kelas_id' => $kelas->id,
                'mata_kuliah_id' => $mk->id,
                'dosen_id' => $dosen->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // 7. User & Mahasiswa Dummy (Gunakan firstOrCreate agar aman dari Duplicate Entry)
        $userMhs = User::firstOrCreate(
            ['email' => 'abiyyu.hasdiansyah@poliban.ac.id'],
            [
                'name' => 'Muhammad Abiyyu Hasdiansyah',
                'password' => Hash::make('password123'),
                'role' => 'Mahasiswa'
            ]
        );

        mahasiswas::firstOrCreate(
            ['nim' => 'C030325001'],
            [
                'user_id' => $userMhs->id,
                'no_hp' => '0812555001',
                'kelas_id' => $kelas->id
            ]
        );
    }
}