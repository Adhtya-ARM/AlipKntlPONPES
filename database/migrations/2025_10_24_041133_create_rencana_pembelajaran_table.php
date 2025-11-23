<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRencanaPembelajaranTable extends Migration
{
    public function up()
    {
        Schema::create('rencana_pembelajaran', function (Blueprint $table) {
            $table->id();
            $table->date('from_date');         // mulai
            $table->date('to_date');           // sampai
            $table->enum("jenis", ["kbm", "libur", "ujian", "lainnya"])->default("kbm");
            $table->string('judul')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['from_date']);
            $table->index(['to_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('rencana_pembelajaran');
    }
}
