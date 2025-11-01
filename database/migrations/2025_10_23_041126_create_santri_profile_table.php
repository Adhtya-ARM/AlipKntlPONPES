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
<<<<<<< HEAD
            $table->foreignId("wali_profile_id")->constrained("wali_profile");
=======
            $table->foreignId("profile_wali_id")->constrained("wali_profiles");
>>>>>>> f050ae17c144e6079ae8b8ec27ed5f44f35675f6
            $table->string("nama");
            $table->integer("nisn")->unique();
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
