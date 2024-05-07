<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCoordinadoresEquiposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coordinadores_equipos', function (Blueprint $table) {
            $table->increments('idCoordinadorEquipo');
            $table->integer('idPersona')->index('fk_coordinadores_equipos_2_idx');
            $table->integer('idEquipo')->index('fk_coordinadores_equipos_3_idx');
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
        Schema::dropIfExists('coordinadores_equipos');
    }
}
