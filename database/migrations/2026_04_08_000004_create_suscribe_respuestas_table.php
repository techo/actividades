<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuscribeRespuestasTable extends Migration
{
    public function up()
    {
        Schema::create('suscribe_respuestas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('suscripcion_id');
            $table->unsignedInteger('pregunta_id');
            $table->text('respuesta')->nullable();
            $table->timestamps();

            $table->foreign('suscripcion_id')
                  ->references('id')
                  ->on('Suscripciones')
                  ->onDelete('cascade');

            $table->foreign('pregunta_id')
                  ->references('id')
                  ->on('campaign_preguntas')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('suscribe_respuestas');
    }
}
