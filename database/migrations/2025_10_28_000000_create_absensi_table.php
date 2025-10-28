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
        Schema::create('absensi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guru_profile_id')->index();
            $table->unsignedBigInteger('mapel_id')->index();
            $table->integer('jumlah_pertemuan')->default(0);
            $table->integer('jumlah_bab')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // foreign keys are optional depending on existing schema
            // if guru_profile and mapel tables use different PK names adjust accordingly
            // $table->foreign('guru_profile_id')->references('id')->on('guru_profile')->onDelete('cascade');
            // $table->foreign('mapel_id')->references('id')->on('mapel')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi');
    }
};
