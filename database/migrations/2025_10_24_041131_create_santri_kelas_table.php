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
        Schema::create('santri_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId("santri_profile_id")->constrained("santri_profile");
            $table->foreignId("kelas_id")->constrained("kelas");
            $table->year('tahun_ajaran')->default(date('Y'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('santri_kelas');
    }
};
