<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Using raw SQL to ensure ENUM type is correctly set
        DB::statement("ALTER TABLE tahun_ajarans MODIFY COLUMN status ENUM('Aktif', 'Tidak Aktif', 'Terarsip') DEFAULT 'Tidak Aktif'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tahun_ajarans', function (Blueprint $table) {
            $table->string('status')->default('Tidak Aktif')->change();
        });
    }
};
