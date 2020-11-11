<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuentaTrabajadorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cuenta_trabajador', function (Blueprint $table) {
            $table->id();
            $table->string('CODIGO',8);
            $table->string('A_MATERNO',20);
            $table->string('A_PATERNO',20);
            $table->string('NOMBRES',50);
            $table->string('PASSWORD',20);
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
        Schema::dropIfExists('cuenta_trabajador');
    }
}
