<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInscripcionRespuestasTable extends Migration
{
    public function up()
    {
        Schema::create('inscripcion_respuestas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('inscripcion_id')->index();
            $table->integer('pregunta_id')->index();
            $table->text('respuesta')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inscripcion_respuestas');
    }
}
