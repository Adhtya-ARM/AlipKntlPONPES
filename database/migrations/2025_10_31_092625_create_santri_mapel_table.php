<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('santri_mapel', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_mapel_id')
                ->constrained('guru_mapel')
                ->onDelete('cascade');

            $table->foreignId('santri_profile_id')
                ->constrained('santri_profiles')
                ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('santri_mapel');
    }
};
