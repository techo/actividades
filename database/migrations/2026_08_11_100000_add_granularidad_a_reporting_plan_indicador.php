<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega granularidad al Plan por indicador: además del plan mensual original,
 * ahora se puede planificar por trimestre, semestre o año.
 *
 * - `granularidad`: 'mensual' | 'trimestral' | 'semestral' | 'anual'.
 * - `periodo`: índice del período dentro del año (mensual 1-12, trimestral 1-4,
 *   semestral 1-2, anual NULL). El `mes` original se conserva y se mantiene =
 *   periodo para las filas mensuales (compatibilidad).
 *
 * Cada granularidad es su propio plan (independientes): un indicador puede tener
 * un plan anual y/o planes trimestrales/mensuales a la vez; el scope de "vigente"
 * ahora incluye granularidad + periodo.
 */
class AddGranularidadAReportingPlanIndicador extends Migration
{
    public function up()
    {
        Schema::table('reporting_plan_indicador', function (Blueprint $table) {
            $table->string('granularidad', 12)->default('mensual')->after('anio');
            $table->unsignedTinyInteger('periodo')->nullable()->after('granularidad');
        });

        // Backfill de lo ya cargado: las filas con mes son mensuales (periodo = mes),
        // las que tenían mes NULL eran planes "a nivel año" -> anuales.
        DB::statement('UPDATE reporting_plan_indicador SET periodo = mes');
        DB::table('reporting_plan_indicador')->whereNull('mes')
            ->update(['granularidad' => 'anual']);

        Schema::table('reporting_plan_indicador', function (Blueprint $table) {
            $table->index(
                ['metric_key', 'idPais', 'anio', 'granularidad', 'periodo', 'vigente'],
                'reporting_plan_indicador_scope_gran'
            );
        });
    }

    public function down()
    {
        Schema::table('reporting_plan_indicador', function (Blueprint $table) {
            $table->dropIndex('reporting_plan_indicador_scope_gran');
            $table->dropColumn(['granularidad', 'periodo']);
        });
    }
}
