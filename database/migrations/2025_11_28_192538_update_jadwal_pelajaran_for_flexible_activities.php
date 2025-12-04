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
        Schema::table('jadwal_pelajaran', function (Blueprint $table) {
            // Add jenis_kegiatan field
            $table->enum('jenis_kegiatan', ['KBM', 'Upacara', 'Apel', 'Istirahat', 'Ekstrakurikuler', 'Lainnya'])
                ->default('KBM')
                ->after('id');
            
            // Add jenjang field (SMP/SMA)
            $table->enum('jenjang', ['SMP', 'SMA'])->nullable()->after('jenis_kegiatan');
            
            // Add nama_kegiatan for non-KBM activities
            $table->string('nama_kegiatan')->nullable()->after('jenjang');
            
            // Make guru_mapel_id, kelas_id, mapel_id, guru_profile_id nullable
            // (since non-KBM activities don't need these)
            $table->foreignId('guru_mapel_id')->nullable()->change();
            $table->foreignId('kelas_id')->nullable()->change();
            $table->foreignId('mapel_id')->nullable()->change();
            $table->foreignId('guru_profile_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_pelajaran', function (Blueprint $table) {
            $table->dropColumn(['jenis_kegiatan', 'jenjang', 'nama_kegiatan']);
            
            // Restore NOT NULL constraints (if needed)
            // Note: This might fail if there's nullable data
            // $table->foreignId('guru_mapel_id')->nullable(false)->change();
        });
    }
};
