<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Capa analítica (datamart) — vistas de participación.
 *
 * Primer slice de Fase 3: el modelo dimensional que consume reporting (Power BI,
 * y el propio backoffice vía App\Reporting). Las definiciones canónicas
 * (presente / movilizado / KPI) se codifican UNA sola vez acá, de modo que el
 * registry y Power BI lean de la misma fuente y no puedan diverger.
 *
 * Convención: vistas con prefijo `reporting_` en la misma BD (MySQL no tiene
 * schemas como Postgres). Un schema/BD dedicado + RLS + anonimización con
 * person_key surrogate es Fase 4.
 *
 * Reglas (confirmadas con negocio):
 *  - período por Actividad.fechaInicio.
 *  - movilizado = presente=1 (participaciones).
 *  - KPI = presente=1 AND tipo_indicador IN ('territorio','construccion_de_viviendas').
 */
class CreateReportingParticipacionViews extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW reporting_dim_actividad AS
            SELECT
                a.idActividad,
                a.nombreActividad,
                a.idTipo,
                t.tipo_indicador,
                t.idCategoria,
                c.nombre              AS categoria,
                a.alcance,
                a.idPais,
                a.idOficina,
                a.fechaInicio,
                a.fechaFin,
                YEAR(a.fechaInicio)   AS anio,
                MONTH(a.fechaInicio)  AS mes,
                a.vida_escuela
            FROM Actividad a
            LEFT JOIN Tipo t ON t.idTipo = a.idTipo
            LEFT JOIN atl_CategoriaActividad c ON c.id = t.idCategoria
            WHERE a.deleted_at IS NULL
        ");

        DB::statement("
            CREATE OR REPLACE VIEW reporting_fact_participacion AS
            SELECT
                i.idInscripcion,
                i.idActividad,
                i.idPersona,
                a.idPais,
                a.idOficina,
                t.tipo_indicador,
                a.fechaInicio            AS fecha_actividad,
                YEAR(a.fechaInicio)      AS anio,
                MONTH(a.fechaInicio)     AS mes,
                CASE WHEN i.presente = 1 THEN 1 ELSE 0 END AS es_presente,
                CASE WHEN i.presente = 1 THEN 1 ELSE 0 END AS es_movilizado,
                CASE
                    WHEN i.presente = 1
                     AND t.tipo_indicador IN ('territorio', 'construccion_de_viviendas')
                    THEN 1 ELSE 0
                END AS es_kpi
            FROM Inscripcion i
            JOIN Actividad a ON a.idActividad = i.idActividad AND a.deleted_at IS NULL
            LEFT JOIN Tipo t ON t.idTipo = a.idTipo
            WHERE i.deleted_at IS NULL
        ");
    }

    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS reporting_fact_participacion');
        DB::statement('DROP VIEW IF EXISTS reporting_dim_actividad');
    }
}
