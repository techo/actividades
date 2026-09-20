<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AmpliaHitosYDescripcionEducacionAText extends Migration
{
    /**
     * Dos campos de texto libre que seguían tirando "SQLSTATE[22001] Data too
     * long" en producción al guardar el perfil del voluntario:
     *   - Integrantes.hitos: ya se había ampliado 300 -> 500 y aun así lo
     *     excedía (los hitos reales son largos).
     *   - estudios.descripcion_educacion: seguía en VARCHAR(255) por defecto.
     * Mismo criterio que descripcion_rol (ver migración 2026_08_26): texto
     * libre y largo -> pasa a TEXT en vez de seguir subiendo el límite.
     */
    public function up()
    {
        Schema::table('Integrantes', function (Blueprint $table) {
            $table->text('hitos')->nullable()->change();
        });

        Schema::table('estudios', function (Blueprint $table) {
            $table->text('descripcion_educacion')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('Integrantes', function (Blueprint $table) {
            $table->string('hitos', 500)->nullable()->change();
        });

        Schema::table('estudios', function (Blueprint $table) {
            $table->string('descripcion_educacion', 255)->nullable()->change();
        });
    }
}
