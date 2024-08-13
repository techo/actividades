<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreaTablaJornada extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Jornada', function (Blueprint $table) {
            $table->increments('idJornada');
            $table->integer('idActividad');
            
            $table->string('nombre');
            $table->dateTime('fechaInicio')->nullable();
			$table->dateTime('fechaFin')->nullable();
			$table->boolean('activo');
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('idActividad', 'Jornada_ibfk_1')->references('idActividad')->on('Actividad')->onDelete('CASCADE');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Jornada');
    }
}
