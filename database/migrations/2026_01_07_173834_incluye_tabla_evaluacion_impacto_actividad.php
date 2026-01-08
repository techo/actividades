<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeTablaEvaluacionImpactoActividad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('EvaluacionImpactoActividad', function (Blueprint $table) {
            $table->increments('idEvaluacionImpacto');

            $table->integer('idActividad');
            $table->integer('idPersona');

            $table->tinyInteger('impacto_habilidades_capacidades');
            $table->tinyInteger('impacto_percepcion_realidad');
            $table->tinyInteger('impacto_recomendaria_experiencia');

            $table->timestamps();

            $table->unique(['idActividad', 'idPersona']);

            $table->foreign('idActividad')
                  ->references('idActividad')
                  ->on('Actividad')
                  ->onDelete('cascade');

            $table->foreign('idPersona')
                ->references('idPersona')->on('Persona')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('EvaluacionImpactoActividad');
    }
}
