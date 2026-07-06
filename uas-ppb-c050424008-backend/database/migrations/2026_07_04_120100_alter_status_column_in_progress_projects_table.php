<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('progress_projects', function (Blueprint $table) {
            $table->string('status', 20)->default('Pending')->change();
        });

        DB::table('progress_projects')
            ->whereIn('status', ['Diterima', 'Revisi'])
            ->update(['status' => DB::raw("CASE WHEN status = 'Diterima' THEN 'Disetujui' ELSE 'Ditolak' END")]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('progress_projects', function (Blueprint $table) {
            $table->enum('status', ['Pending', 'Revisi', 'Diterima'])->default('Pending')->change();
        });
    }
};
