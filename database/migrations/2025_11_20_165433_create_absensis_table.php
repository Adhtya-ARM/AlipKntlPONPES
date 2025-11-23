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
        Schema::dropIfExists('absensis');

        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_profile_id')->constrained('santri_profile')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('mapel_id')->nullable()->constrained('mapel')->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('status', ['H', 'S', 'I', 'A', 'D', 'T'])->default('A');
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['santri_profile_id', 'tanggal', 'mapel_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
