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
        Schema::create('absensi_header', function (Blueprint $table) {
            $table->id();
            $table->foreignId("rencana_pembelajaran_id")->constrained("rencana_pembelajaran");
             $table->foreignId("guru_mapel_id")->constrained("guru_mapel");
            $table->integer("pertemuan_ke");
            $table->date("tanggal_absensi");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_header');
    }
};
