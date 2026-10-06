<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AmpliaTextosLibresInformeCierreAText extends Migration
{
    /**
     * Misma familia de "SQLSTATE[22001] Data too long" (ver 2026_09_22): el
     * informe de cierre de actividad guardaba sus textos libres en VARCHAR(191)
     * y en prod comentarios_adicionales desbordaba (159 errores entre el 30/9 y
     * el 6/10, un coordinador reintentando sin poder guardar). Se pasan a TEXT
     * todos los campos de texto libre de la tabla, no solo el que apareció, y la
     * URL de archivos adicionales (links de Drive largos).
     */
    public function up()
    {
        Schema::table('actividad_informe_cierre', function (Blueprint $table) {
            $table->text('programa')->nullable()->change();
            $table->text('soluciones_entregadas')->nullable()->change();
            $table->text('quienes_financiaron')->nullable()->change();
            $table->text('archivos_adicionales')->nullable()->change();
            $table->text('comentarios_adicionales')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('actividad_informe_cierre', function (Blueprint $table) {
            $table->string('programa')->nullable()->change();
            $table->string('soluciones_entregadas')->nullable()->change();
            $table->string('quienes_financiaron')->nullable()->change();
            $table->string('archivos_adicionales')->nullable()->change();
            $table->string('comentarios_adicionales')->nullable()->change();
        });
    }
}
