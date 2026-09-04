<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-país por usuario (Fase 1, chokepoint): un usuario puede tener varios países
 * habilitados. Es aditivo y retrocompatible: si un usuario NO tiene filas acá, se cae
 * al modelo actual de `Persona.idPaisPermitido` (un solo país, o global si es 0/null).
 *
 * Sin FK a la tabla legacy `Persona` (idPersona) a propósito, para no acoplar tipos
 * ni engines; se maneja por índice + unique.
 */
class CreatePersonaPaisesPermitidosTable extends Migration
{
    public function up()
    {
        Schema::create('persona_paises_permitidos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('idPersona');
            $table->integer('idPais');
            $table->timestamps();

            $table->unique(['idPersona', 'idPais']);
            $table->index('idPersona');
        });
    }

    public function down()
    {
        Schema::dropIfExists('persona_paises_permitidos');
    }
}
