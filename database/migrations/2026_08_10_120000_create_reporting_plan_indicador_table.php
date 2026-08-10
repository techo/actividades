<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabla de valores PLANIFICADOS por indicador (Plan) — el único componente de
 * datos que faltaba para tener Plan vs. Real en vivo. El "Real" ya se calcula
 * hoy con App\Reporting\MetricRegistry sobre las vistas reporting_*; esta tabla
 * no duplica ninguna de esas reglas, solo guarda la meta.
 *
 * Cada fila es una versión: nunca se pisa un valor, se marca vigente=false y se
 * inserta una fila nueva con version+1. Así se conserva el historial de qué se
 * planificó y cuándo cambió — algo que hoy da, sin proponérselo, el archivo
 * Excel congelado de cada país/año, y que ningún dashboard "en vivo" resuelve
 * gratis (ver sección 3.3 del plan de trabajo).
 *
 * Sin foreign keys a propósito (mismo criterio que listado_columnas.created_by):
 * evita que esta migración falle en un entorno donde los nombres de tabla/columna
 * de Persona o de países difieran levemente: created_by/idPais quedan como
 * columnas indexadas, no como constraints.
 */
class CreateReportingPlanIndicadorTable extends Migration
{
    public function up()
    {
        Schema::create('reporting_plan_indicador', function (Blueprint $table) {
            $table->increments('id');

            // Clave de App\Reporting\MetricRegistry (ej. 'movilizados_territorio').
            $table->string('metric_key', 100);

            $table->unsignedInteger('idPais');
            $table->unsignedInteger('idOficina')->nullable();
            $table->unsignedSmallInteger('anio');
            // Nullable: hay indicadores "stock" (ej. equipo permanente TOTAL) que
            // se planifican a nivel año, no mes a mes.
            $table->unsignedTinyInteger('mes')->nullable();

            $table->decimal('valor_planificado', 12, 2);

            $table->unsignedInteger('version')->default(1);
            $table->boolean('vigente')->default(true);

            $table->unsignedInteger('created_by');
            $table->unsignedInteger('aprobado_by')->nullable();

            $table->timestamps();

            $table->index(['metric_key', 'idPais', 'anio', 'mes', 'vigente'], 'reporting_plan_indicador_scope');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reporting_plan_indicador');
    }
}
