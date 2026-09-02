<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Registro de cada comunicación saliente disparada desde el hub de comunicaciones
 * (pantalla "Invitaciones"). Hasta ahora los envíos eran fire-and-forget (solo log);
 * esta tabla es la columna vertebral que habilita historial, multicanal y atribución
 * (¿esta comunicación generó inscripciones?).
 *
 * Diseñada genérica desde el día uno:
 *  - `canal`: hoy 'push'; deja lugar a 'email' y 'whatsapp' sin migración nueva.
 *  - `objetivo_tipo`/`objetivo_id`: hoy siempre 'actividad'; deja lugar a 'campaign'.
 * El detalle por persona vive en `comunicacion_destinatarios` (para atribución).
 */
class CreateComunicacionesTable extends Migration
{
    public function up()
    {
        Schema::create('comunicaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('canal')->default('push');            // push | email | whatsapp
            $table->string('objetivo_tipo')->default('actividad'); // actividad | campaign
            $table->unsignedInteger('objetivo_id')->nullable();
            $table->string('segmento');
            $table->json('paises')->nullable();                  // ids de país usados en la segmentación
            $table->string('titulo');
            $table->text('mensaje');
            $table->unsignedInteger('destinatarios_count')->default(0);
            $table->unsignedInteger('idAdmin')->nullable();      // idPersona que disparó el envío
            $table->string('estado')->default('procesando');     // procesando | enviada | error
            $table->timestamps();

            $table->index(['objetivo_tipo', 'objetivo_id']);
            $table->index('idAdmin');
            $table->index('canal');
        });
    }

    public function down()
    {
        Schema::dropIfExists('comunicaciones');
    }
}
