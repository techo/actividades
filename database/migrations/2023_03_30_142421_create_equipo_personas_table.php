<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEquipoPersonasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipo_personas', function (Blueprint $table) {
            $table->increments('idEquipoPersona');
            $table->integer('idEquipo')->index('idEquipo');
            $table->integer('idPersona')->index('idPersona');
            $table->string('rol');
			$table->boolean('estado');
            $table->dateTime('fechaInicio');
            $table->dateTime('fechaFin');
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
        Schema::dropIfExists('equipo_personas');
    }
}
