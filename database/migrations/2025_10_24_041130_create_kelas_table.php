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
            $table->string('nama_kelas');
            $table->integer('level');
            $table->string('nama_unik');
            $table->foreignId('guru_profile_id') ->nullable()->constrained('guru_profile')->onDelete('set null');
            $table->foreignId("wali_kelas_id")->nullable()->constrained("guru_profile")->nullOnDelete();
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
