<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Capa analítica (datamart) — ciclo de voluntariado.
 *
 * El embudo Captados -> Inserción -> Transformación -> Cierre no tiene un campo
 * directo; se deriva del estado de cada persona (definición acordada):
 *  - transformación = es integrante de equipo vigente.
 *  - cierre        = fue integrante pero ninguna membresía vigente.
 *  - inserción     = tiene al menos una presencia y no es integrante.
 *  - captado       = está suscripto (Suscripciones/campaign) sin presencia ni membresía.
 *
 * La vista da el estado ACTUAL. Para el histórico y "Var. vs Año Ant." se guarda
 * un snapshot mensual (tabla reporting_snapshot_lifecycle) con el comando
 * reporting:snapshot-lifecycle, ya que una vista no puede conservar historia.
 */
class CreateReportingLifecycle extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW reporting_fact_lifecycle AS
            SELECT
                p.idPersona,
                p.idPais,
                COALESCE(mb.algun_vigente, 0)   AS es_integrante_vigente,
                COALESCE(mb.ex_integrante, 0)   AS fue_integrante,
                COALESCE(pr.tiene_presencia, 0) AS tiene_presencia,
                COALESCE(su.es_suscripto, 0)    AS es_suscripto,
                CASE
                    WHEN COALESCE(mb.algun_vigente, 0) = 1   THEN 'transformacion'
                    WHEN COALESCE(mb.ex_integrante, 0) = 1   THEN 'cierre'
                    WHEN COALESCE(pr.tiene_presencia, 0) = 1 THEN 'insercion'
                    WHEN COALESCE(su.es_suscripto, 0) = 1    THEN 'captado'
                    ELSE 'sin_etapa'
                END AS etapa
            FROM Persona p
            LEFT JOIN (
                SELECT idPersona,
                       MAX(vigente) AS algun_vigente,
                       MAX(CASE WHEN vigente = 0 THEN 1 ELSE 0 END) AS ex_integrante
                FROM reporting_fact_membresia
                GROUP BY idPersona
            ) mb ON mb.idPersona = p.idPersona
            LEFT JOIN (
                SELECT idPersona, 1 AS tiene_presencia
                FROM reporting_fact_participacion
                WHERE es_presente = 1
                GROUP BY idPersona
            ) pr ON pr.idPersona = p.idPersona
            LEFT JOIN (
                SELECT idPersona, 1 AS es_suscripto
                FROM Suscripciones
                WHERE idPersona IS NOT NULL
                GROUP BY idPersona
            ) su ON su.idPersona = p.idPersona
            WHERE p.deleted_at IS NULL
        ");

        Schema::create('reporting_snapshot_lifecycle', function (Blueprint $table) {
            $table->increments('id');
            $table->date('snapshot_date');
            $table->integer('idPais')->nullable();
            $table->string('etapa');
            $table->unsignedInteger('cantidad')->default(0);
            $table->timestamps();
            $table->index(['snapshot_date', 'idPais']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reporting_snapshot_lifecycle');
        DB::statement('DROP VIEW IF EXISTS reporting_fact_lifecycle');
    }
}
