<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AmpliaPuntoDePuntoencuentro extends Migration
{
    /**
     * PuntoEncuentro.punto era VARCHAR(100): un coordinador que cargaba la dirección
     * completa del punto (~130 caracteres) recibía "Data too long" (500) sin mensaje y
     * terminaba recortándola (54 intentos en prod el 2026-10-06, actividad 41927).
     * Se amplía a 255 (no TEXT: es un nombre corto que se muestra en listados/selects)
     * y CrearPunto valida max:255 para que el exceso dé un error de validación legible.
     */
    public function up()
    {
        Schema::table('PuntoEncuentro', function (Blueprint $table) {
            $table->string('punto', 255)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('PuntoEncuentro', function (Blueprint $table) {
            $table->string('punto', 100)->nullable()->change();
        });
    }
}
