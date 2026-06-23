<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Condiciones de visibilidad para preguntas configurables.
 *
 * Polimórfica: sirve tanto a actividad_preguntas como a campaign_preguntas.
 * Sin FK formal sobre el morph (estándar Laravel), consistente con que
 * inscripcion_respuestas tampoco define FK. La integridad se cuida en aplicación.
 */
class CreatePreguntaCondicionesTable extends Migration
{
    public function up()
    {
        Schema::create('pregunta_condiciones', function (Blueprint $table) {
            $table->increments('id');

            // Pregunta que se muestra condicionalmente (morph: alias + id).
            $table->string('target_type', 60);
            $table->unsignedInteger('target_id');

            // Pregunta de la que depende (misma entidad, mismo PK 'id').
            $table->unsignedInteger('parent_id');

            // Operador de comparación. Extensible (v1: solo 'equals').
            $table->string('operator', 30)->default('equals');

            // Valor esperado: id ESTABLE de la opción del padre (no su texto).
            $table->string('value')->nullable();

            $table->timestamps();

            $table->index(['target_type', 'target_id']);
            $table->index('parent_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pregunta_condiciones');
    }
}
