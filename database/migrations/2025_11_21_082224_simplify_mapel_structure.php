<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop foreign key from mapel if exists
        if (Schema::hasTable('mapel') && Schema::hasColumn('mapel', 'kelompok_mapel_id')) {
            Schema::table('mapel', function (Blueprint $table) {
                $table->dropForeign(['kelompok_mapel_id']);
                $table->dropColumn(['kelompok_mapel_id', 'urutan']);
            });
        }

        // Drop kelompok_mapels and jurusans tables
        Schema::dropIfExists('kelompok_mapels');
        Schema::dropIfExists('jurusans');

        // Add new simplified columns to mapel
        if (Schema::hasTable('mapel')) {
            Schema::table('mapel', function (Blueprint $table) {
                if (!Schema::hasColumn('mapel', 'kategori')) {
                    $table->string('kategori')->default('umum')->after('nama_mapel'); // umum, kejuruan, khusus
                }
                if (!Schema::hasColumn('mapel', 'kelompok')) {
                    $table->string('kelompok')->nullable()->after('kategori'); // nama kelompok
                }
                if (!Schema::hasColumn('mapel', 'jjm')) {
                    $table->integer('jjm')->default(2)->after('kelompok'); // Jam per minggu
                }
                if (!Schema::hasColumn('mapel', 'tingkat')) {
                    $table->json('tingkat')->nullable()->after('jjm'); // Array tingkat kelas
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mapel')) {
            Schema::table('mapel', function (Blueprint $table) {
                $table->dropColumn(['kategori', 'kelompok', 'jjm', 'tingkat']);
            });
        }

        // Recreate tables
        Schema::create('jurusans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode');
            $table->timestamps();
        });

        Schema::create('kelompok_mapels', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->enum('jenis', ['umum', 'kejuruan', 'khusus'])->default('umum');
            $table->foreignId('jurusan_id')->nullable()->constrained('jurusans')->nullOnDelete();
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        if (Schema::hasTable('mapel')) {
            Schema::table('mapel', function (Blueprint $table) {
                $table->foreignId('kelompok_mapel_id')->nullable()->constrained('kelompok_mapels')->nullOnDelete();
                $table->integer('urutan')->default(0);
            });
        }
    }
};
