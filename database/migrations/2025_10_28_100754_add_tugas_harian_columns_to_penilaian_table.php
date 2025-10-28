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
        Schema::table('penilaian', function (Blueprint $table) {
            $table->integer('bab1')->nullable();
            $table->integer('bab2')->nullable();
            $table->integer('bab3')->nullable();
            $table->integer('bab4')->nullable();
            $table->integer('bab5')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penilaian', function (Blueprint $table) {
            $table->dropColumn(['bab1', 'bab2', 'bab3', 'bab4', 'bab5']);
        });
    }
};