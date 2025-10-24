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
        Schema::create("wali_profile", function (Blueprint $table) {
            $table->id();
            $table->foreignId("wali_id")->constrained("wali");
            $table->string("nama");
            $table->text("alamat");
            $table->string("no_hp")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("wali_profile");
    }
};
