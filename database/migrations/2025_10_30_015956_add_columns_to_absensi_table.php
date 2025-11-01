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
        Schema::table('absensi', function (Blueprint $table) {
            $table->unsignedBigInteger('santri_profile_id')->index()->after('id');
            $table->integer('pertemuan_ke')->after('santri_profile_id');
            $table->enum('status', ['hadir', 'sakit', 'izin', 'alpha', 'X'])->after('pertemuan_ke');
            $table->text('keterangan')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropColumn(['santri_profile_id', 'pertemuan_ke', 'status']);
            $table->text('keterangan')->nullable(false)->change();
        });
    }
};