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
        Schema::table('sekolah_profiles', function (Blueprint $table) {
            $table->text('maps_embed_url')->nullable()->after('website');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sekolah_profiles', function (Blueprint $table) {
            $table->dropColumn('maps_embed_url');
        });
    }
};
