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
        // 1. Create Sekolah Profile Table
        Schema::create('sekolah_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('nama_sekolah')->default('Pondok Pesantren Al-Madinah');
            $table->string('logo')->nullable();
            $table->text('visi')->nullable();
            $table->text('misi')->nullable();
            $table->string('alamat')->nullable();
            $table->string('email')->nullable();
            $table->string('telepon')->nullable();
            $table->string('website')->nullable();
            $table->timestamps();
        });

        // 2. Create Tahun Ajaran Table
        Schema::create('tahun_ajarans', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // e.g., "2024/2025"
            $table->enum('semester', ['Ganjil', 'Genap'])->default('Ganjil');
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        // 3. Modify Santri Kelas to use Tahun Ajaran ID
        Schema::table('santri_kelas', function (Blueprint $table) {
            // Drop old year column if exists (it was 'year' type)
            $table->dropColumn('tahun_ajaran'); 
        });
        Schema::table('santri_kelas', function (Blueprint $table) {
             $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->onDelete('cascade');
             $table->string('status')->default('Aktif'); // Aktif, Lulus, Pindah, Tinggal Kelas
        });

        // 4. Modify Guru Mapel to use Tahun Ajaran ID
        Schema::table('guru_mapel', function (Blueprint $table) {
            $table->dropColumn(['tahun_ajaran', 'semester']);
        });
        Schema::table('guru_mapel', function (Blueprint $table) {
            $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajarans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sekolah_profiles');
        
        Schema::table('guru_mapel', function (Blueprint $table) {
            $table->dropForeign(['tahun_ajaran_id']);
            $table->dropColumn('tahun_ajaran_id');
            $table->string('tahun_ajaran')->nullable();
            $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
        });

        Schema::table('santri_kelas', function (Blueprint $table) {
            $table->dropForeign(['tahun_ajaran_id']);
            $table->dropColumn(['tahun_ajaran_id', 'status']);
            $table->year('tahun_ajaran')->nullable();
        });

        Schema::dropIfExists('tahun_ajarans');
    }
};
