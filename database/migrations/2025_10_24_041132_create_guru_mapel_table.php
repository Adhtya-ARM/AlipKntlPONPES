<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create("guru_mapel", function (Blueprint $table) {
            $table->id();
            $table->foreignId("guru_profile_id")->constrained("guru_profile");
            $table->foreignId("kelas_id")->constrained("kelas");
            $table->foreignId("mapel_id")->constrained("mapel");
            $table->string("tahun_ajaran");
            $table->enum("semester", ["ganjil", "genap"])->default("ganjil");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("guru_mapel");
    }
};
