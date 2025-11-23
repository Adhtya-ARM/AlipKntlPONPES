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
        Schema::create('penilaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('santri_profile_id')->constrained('santri_profile')->cascadeOnDelete();
            $table->foreignId('guru_mapel_id')->constrained('guru_mapel')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('jenis_penilaian')->default('UH'); // UH, UTS, UAS, TUGAS, dll
            $table->decimal('nilai', 5, 2)->default(0);
            $table->string('keterangan')->nullable();
            $table->timestamps();

            // Ensure one grade per type per date per student (optional, but good for data integrity)
            // Or maybe just per type per date?
            // Let's keep it flexible for now, maybe just index.
            $table->index(['santri_profile_id', 'guru_mapel_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaians');
    }
};
