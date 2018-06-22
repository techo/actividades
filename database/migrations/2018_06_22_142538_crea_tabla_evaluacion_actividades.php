<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreaTablaEvaluacionActividades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('EvaluacionActividad')) {
            //
            Schema::create('EvaluacionActividad', function (Blueprint $table) {
                $table->increments('idEvaluacion');
                $table->integer('idPersona')->unsigned();
                $table->integer('idActividad')->unsigned();
                $table->integer('puntaje')->unsigned();
                $table->text('comentario');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('EvaluacionActividad');
    }
}
