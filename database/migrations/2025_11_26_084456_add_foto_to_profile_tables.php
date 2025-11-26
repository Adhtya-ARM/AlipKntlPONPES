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
            $table->string('foto')->nullable()->after('no_hp');
        });
        Schema::table('santri_profile', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('status');
        });
        Schema::table('wali_profile', function (Blueprint $table) {
            $table->string('foto')->nullable()->after('alamat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guru_profile', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
        Schema::table('santri_profile', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
        Schema::table('wali_profile', function (Blueprint $table) {
            $table->dropColumn('foto');
        });
    }
};
