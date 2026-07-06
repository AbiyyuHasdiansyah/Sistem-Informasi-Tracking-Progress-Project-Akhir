<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('progress_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->onDelete('cascade');
            $table->foreignId('kelas_mata_kuliah_id')->constrained('kelas_mata_kuliah')->onDelete('cascade');
    
            // Informasi Konten Project
            $table->string('judul_project');
            $table->string('deskripsi_progress'); // Penjelasan mengenai apa yang dikerjakan pada progress ini
            $table->integer('persentase_estimasi'); // Angka 0-100% estimasi selesainya project
    
            // File & Dokumen Pendukung (Sesuai Petunjuk Soal UAS)
            $table->string('file_database')->nullable(); // File .sql eksport basis data terbaru
            $table->string('file_laporan')->nullable();  // File .doc/.docx laporan/buku project akhir
    
            // Tautan Media & Repositori
            $table->string('link_gdrive')->nullable();    // Link backup / resource besar
            $table->string('link_github')->nullable();    // Link repository source code backend/flutter
            $table->string('link_youtube')->nullable();   // Link video demonstrasi progress fitur terbaru
    
            // Status Tracking dari Dosen
            // Status bisa berupa: 'Pending', 'Disetujui', 'Ditolak'
            $table->string('status', 20)->default('Pending');
            $table->text('catatan_dosen')->nullable(); // Feedback atau arahan revisi dari dosen pembimbing
    
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_projects');
    }
};
