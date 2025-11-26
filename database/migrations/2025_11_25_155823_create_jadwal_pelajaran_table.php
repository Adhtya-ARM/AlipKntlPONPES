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
        Schema::create('jadwal_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_mapel_id')->constrained('guru_mapel')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('mapel_id')->constrained('mapel')->onDelete('cascade');
            $table->foreignId('guru_profile_id')->constrained('guru_profile')->onDelete('cascade');
            
            // Hari dalam seminggu
            $table->enum('hari', ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']);
            
            // Jam pelajaran (1-10 atau sesuai kebutuhan)
            $table->integer('jam_ke'); // 1, 2, 3, dst
            
            // Waktu mulai dan selesai (opsional untuk display)
            $table->time('jam_mulai'); // misal: 07:00
            $table->time('jam_selesai'); // misal: 07:45
            
            // Semester dan tahun ajaran
            $table->enum('semester', ['ganjil', 'genap']);
            $table->string('tahun_ajaran'); // 2025/2026
            
            $table->timestamps();
            
            // Index untuk performa query
            $table->index(['hari', 'jam_ke']);
            $table->index(['guru_profile_id', 'hari']);
            $table->index(['kelas_id', 'hari']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_pelajaran');
    }
};
