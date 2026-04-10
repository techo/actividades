<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActividadPreguntasTable extends Migration
{
    public function up()
    {
        Schema::create('actividad_preguntas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('actividad_id')->index();
            $table->string('pregunta');
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['abierta', 'desplegable'])->default('abierta');
            $table->json('opciones')->nullable();
            $table->boolean('requerida')->default(false);
            $table->integer('orden')->default(0);
            $table->timestamps();

            // Clave foránea de idActividad con eliminación en cascada
            $table->foreign('actividad_id')
                ->references('idActividad')->on('Actividad')
                ->onDelete('CASCADE');
        });
    }

    public function down()
    {
        Schema::dropIfExists('actividad_preguntas');
    }
}
