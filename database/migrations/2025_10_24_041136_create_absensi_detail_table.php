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
        Schema::create('absensi_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId("absensi_header_id")->constrained("absensi_header");
            $table->foreignId("santri_profile_id")->constrained("santri_profile");
            $table->enum("kehadiran", ["hadir", "ijin","alpha", "sakit"])->default("hadir");
            $table->text("catatan");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_detail');
    }
};
