<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncorporaTablaEvaluacionPersonaRespuesta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('evaluacion_persona_respuestas', function (Blueprint $table) {
            $table->increments('idEvaluacionPersonaRespuesta');
            $table->unsignedInteger('idEvaluacionPersona')->index();
            $table->string('question_key', 100)->comment('ej: conexion_equipo, compromiso_colaboracion, etc.');
            $table->tinyInteger('score')->nullable()->comment('1..10, null = no aplica');
            $table->json('tags_positivos')->nullable();
            $table->json('tags_negativos')->nullable();
            $table->text('comentario')->nullable();
            $table->timestamps();

            // Opcional FK:
            $table->foreign('idEvaluacionPersona')->references('idEvaluacionPersona')->on('EvaluacionPersona')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('evaluacion_persona_respuestas');
    }
}
