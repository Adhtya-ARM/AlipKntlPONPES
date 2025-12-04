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
        Schema::create("santri_profile", function (Blueprint $table) {
            $table->id();
            $table->foreignId("santri_id")->constrained("santris");
            $table->foreignId("wali_profile_id")->constrained("wali_profile");
            $table->string("nama");
            $table->string("no_hp")->nullable();
            $table->text("alamat")->nullable();
            $table
                ->enum("status", ["aktif", "non-aktif", "dropout"])
                ->default("aktif");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("santri_profile");
    }
};
