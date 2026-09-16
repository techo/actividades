<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Un destinatario de una comunicación puede ser una Persona (voluntario, con cuenta) o
 * un suscripto/lead de campaña (sin cuenta, datos inline en `Suscripciones`). Se hace
 * `idPersona` nullable y se agrega `suscripcion_id`: cada fila apunta a uno u otro.
 *
 * Cada operación va en su propio bloque a propósito: mezclar `->change()` (que resuelve
 * doctrine/dbal) con dropUnique/add-column en un mismo Blueprint provoca reordenamientos
 * y choques (la FK de comunicacion_id se apoya en el unique). Separado es determinístico.
 */
class AddSuscripcionToComunicacionDestinatariosTable extends Migration
{
    public function up()
    {
        // 1) La FK de comunicacion_id se apoyaba en el unique; le damos su propio índice.
        Schema::table('comunicacion_destinatarios', function (Blueprint $table) {
            $table->index('comunicacion_id');
        });

        // 2) Ya se puede soltar el unique (comunicacion_id, idPersona).
        Schema::table('comunicacion_destinatarios', function (Blueprint $table) {
            $table->dropUnique(['comunicacion_id', 'idPersona']);
        });

        // 3) Nueva columna para los suscriptos/leads + su índice.
        Schema::table('comunicacion_destinatarios', function (Blueprint $table) {
            $table->unsignedInteger('suscripcion_id')->nullable()->after('idPersona');
            $table->index('suscripcion_id');
        });

        // 4) idPersona pasa a nullable (aislado: lo resuelve doctrine/dbal).
        Schema::table('comunicacion_destinatarios', function (Blueprint $table) {
            $table->unsignedInteger('idPersona')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('comunicacion_destinatarios', function (Blueprint $table) {
            $table->unsignedInteger('idPersona')->nullable(false)->change();
        });

        Schema::table('comunicacion_destinatarios', function (Blueprint $table) {
            $table->dropIndex(['suscripcion_id']);
            $table->dropColumn('suscripcion_id');
        });

        Schema::table('comunicacion_destinatarios', function (Blueprint $table) {
            $table->unique(['comunicacion_id', 'idPersona']);
        });

        Schema::table('comunicacion_destinatarios', function (Blueprint $table) {
            $table->dropIndex(['comunicacion_id']);
        });
    }
}
