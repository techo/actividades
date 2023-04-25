<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEquipoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Equipo', function (Blueprint $table) {
            $table->increments('idEquipo');
            $table->integer('idOficina')->index('idOficina');
            $table->integer('idPais')->index('idPais');
            $table->string('nombre');
			$table->boolean('activo');
            $table->dateTime('fechaInicio');
            $table->dateTime('fechaFin')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Equipo');
    }
}
