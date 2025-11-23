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
        Schema::table('guru', function (Blueprint $table) {
            if (Schema::hasColumn('guru', 'sub_roles')) {
                $table->dropColumn('sub_roles');
            }
            if (Schema::hasColumn('guru', 'last_active_guru_role')) {
                $table->dropColumn('last_active_guru_role');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->json('sub_roles')->nullable();
            $table->string('last_active_guru_role')->nullable();
        });
    }
};
