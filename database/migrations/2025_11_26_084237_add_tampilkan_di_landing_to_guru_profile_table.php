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
        Schema::table('guru_profile', function (Blueprint $table) {
            $table->boolean('tampilkan_di_landing')->default(false)->after('jabatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guru_profile', function (Blueprint $table) {
            $table->dropColumn('tampilkan_di_landing');
        });
    }
};
