<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AmpliaTextosLibresIntegranteReunionAText extends Migration
{
    /**
     * Cierra la familia de "SQLSTATE[22001] Data too long" en campos de texto
     * libre que en prod seguían desbordando al guardar el perfil del voluntario
     * y las reuniones de equipo.
     *
     * El perfil de Integrante actualiza meta/hitos/impacto/capacidades en un solo
     * UPDATE: si cualquiera excede su VARCHAR, todo el guardado tira 500. Ya se
     * habían pasado a TEXT descripcion_rol (2026_08_26) e hitos (2026_09_19); acá
     * van los que faltaban (meta 500, impacto 500, capacidades 300) más
     * equipo_reunion.descripcion (VARCHAR 3000, el que más aparecía en el log).
     *
     * Mismo criterio de siempre: texto libre y largo -> TEXT, en vez de seguir
     * subiendo el límite o rechazar input válido del usuario.
     */
    public function up()
    {
        Schema::table('Integrantes', function (Blueprint $table) {
            $table->text('meta')->nullable()->change();
            $table->text('impacto')->nullable()->change();
            $table->text('capacidades')->nullable()->change();
        });

        Schema::table('equipo_reunion', function (Blueprint $table) {
            $table->text('descripcion')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('Integrantes', function (Blueprint $table) {
            $table->string('meta', 500)->nullable()->change();
            $table->string('impacto', 500)->nullable()->change();
            $table->string('capacidades', 300)->nullable()->change();
        });

        Schema::table('equipo_reunion', function (Blueprint $table) {
            $table->string('descripcion', 3000)->nullable()->change();
        });
    }
}
