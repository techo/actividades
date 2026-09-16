<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Detalle por persona de cada comunicación enviada. Una fila = "a esta persona se le
 * despachó esta comunicación en este momento".
 *
 * Es lo que habilita la atribución/conversión (el "buen indicador"): más adelante se
 * cruza `idPersona` con las inscripciones a `comunicaciones.objetivo_id` posteriores a
 * `created_at` para saber cuántos destinatarios efectivamente se anotaron.
 *
 * No guarda PII: solo el vínculo comunicación ↔ idPersona (la persona ya existe en el
 * sistema). No se exporta ni duplica dato de contacto.
 */
class CreateComunicacionDestinatariosTable extends Migration
{
    public function up()
    {
        Schema::create('comunicacion_destinatarios', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('comunicacion_id');
            $table->unsignedInteger('idPersona');
            $table->string('estado')->default('enviado'); // enviado | error (a futuro: entregado)
            $table->timestamps();

            $table->foreign('comunicacion_id')
                ->references('id')->on('comunicaciones')
                ->onDelete('cascade');

            $table->unique(['comunicacion_id', 'idPersona']);
            $table->index('idPersona');
        });
    }

    public function down()
    {
        Schema::dropIfExists('comunicacion_destinatarios');
    }
}
