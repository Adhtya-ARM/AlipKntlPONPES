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
        Schema::create('rencana_pembelajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId("guru_mapel_id")->constrained("guru_mapel");
            $table->integer('jumlah_pertemuan')->default(0);
            $table->integer('jumlah_bab')->default(0);
            $table->timestamps();
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
