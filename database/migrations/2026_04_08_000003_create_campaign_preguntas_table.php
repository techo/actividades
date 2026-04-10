<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignPreguntasTable extends Migration
{
    public function up()
    {
        Schema::create('campaign_preguntas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('campaign_id');
            $table->string('pregunta', 500);
            $table->string('descripcion', 1000)->nullable();
            $table->enum('tipo', ['abierta', 'desplegable'])->default('abierta');
            $table->json('opciones')->nullable();
            $table->boolean('requerida')->default(false);
            $table->integer('orden')->default(0);
            $table->timestamps();

            $table->foreign('campaign_id')
                  ->references('id')
                  ->on('campaigns')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('campaign_preguntas');
    }
}
