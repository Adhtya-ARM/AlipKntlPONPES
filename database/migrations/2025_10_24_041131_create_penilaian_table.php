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
        Schema::create("penilaian", function (Blueprint $table) {
            $table->id();
            $table->foreignId("santri_profile_id")->constrained("santri_profile");
            $table->foreignId("mapel_id")->constrained("mapel");
            $table->foreignId("guru_profile_id")->constrained("guru_profile");
            $table->enum('semester', ['ganjil', 'genap'])->default('ganjil');
            $table->integer("nilai");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("_penilaian");
    }
};
