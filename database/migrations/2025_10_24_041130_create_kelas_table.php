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
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->integer('level'); // Tingkat (10, 11, 12)
            $table->string('nama_unik')->nullable(); // Nama unik kelas (TTN, TITL, TPEM, dll)
            $table->foreignId('guru_profile_id')->nullable()->constrained('guru_profile')->onDelete('set null'); // Deprecated, gunakan wali_kelas_id
            $table->foreignId("wali_kelas_id")->nullable()->constrained("guru_profile")->nullOnDelete(); // Wali kelas
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
