<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistorialDescargasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historial_descargas', function (Blueprint $table) {
            $table->id();
            $table->string('empresa',2)->nullable();
            $table->string('device',20)->nullable();
            $table->string('codigo_personal',8);
            $table->string('movimientos',50);
            $table->integer('anio');
            $table->integer('semana');
            $table->string('envio',1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('historial_descargas');
    }
}
