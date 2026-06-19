<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Capa analítica (datamart) — vistas de evaluación y geografía.
 *
 * Backbone del tablero de Evaluación (DATASPOT): puntaje, NPS, impacto y tags.
 *
 * Umbrales canónicos (confirmados): promotor >= 9, detractor <= 6 (pasivo 7-8);
 * impacto "alto" >= 8. NPS = (promotores - detractores) / total * 100, lo calcula
 * Power BI a partir de los flags es_promotor / es_detractor.
 *
 * Los tags (tags_positivos / tags_negativos) se exponen como JSON para que Power
 * BI los desanide; MySQL 5.7 no tiene JSON_TABLE para normalizarlos en la vista.
 *
 * No se crea dim_tiempo: Power BI genera su propia tabla de fechas (time
 * intelligence nativo).
 */
class CreateReportingEvaluacionGeografiaViews extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW reporting_fact_evaluacion_actividad AS
            SELECT
                ea.idEvaluacion,
                ea.idActividad,
                ea.idPersona,
                a.idPais,
                a.idOficina,
                t.tipo_indicador,
                a.fechaInicio          AS fecha_actividad,
                YEAR(a.fechaInicio)    AS anio,
                ea.puntaje,
                ea.tags_positivos,
                ea.tags_negativos,
                CASE WHEN ea.comentario IS NOT NULL AND ea.comentario <> '' THEN 1 ELSE 0 END AS tiene_comentario
            FROM EvaluacionActividad ea
            JOIN Actividad a ON a.idActividad = ea.idActividad AND a.deleted_at IS NULL
            LEFT JOIN Tipo t ON t.idTipo = a.idTipo
        ");

        DB::statement("
            CREATE OR REPLACE VIEW reporting_fact_evaluacion_impacto AS
            SELECT
                ei.idEvaluacionImpacto,
                ei.idActividad,
                ei.idPersona,
                a.idPais,
                a.idOficina,
                a.fechaInicio          AS fecha_actividad,
                YEAR(a.fechaInicio)    AS anio,
                ei.impacto_habilidades_capacidades,
                ei.impacto_percepcion_realidad,
                ei.impacto_recomendaria_experiencia,
                CASE WHEN ei.impacto_recomendaria_experiencia >= 9 THEN 1 ELSE 0 END AS es_promotor,
                CASE WHEN ei.impacto_recomendaria_experiencia <= 6 THEN 1 ELSE 0 END AS es_detractor,
                CASE
                    WHEN ei.impacto_recomendaria_experiencia >= 9 THEN 'promotor'
                    WHEN ei.impacto_recomendaria_experiencia <= 6 THEN 'detractor'
                    ELSE 'pasivo'
                END AS nps_categoria
            FROM EvaluacionImpactoActividad ei
            JOIN Actividad a ON a.idActividad = ei.idActividad AND a.deleted_at IS NULL
        ");

        DB::statement("
            CREATE OR REPLACE VIEW reporting_dim_geografia AS
            SELECT
                l.id            AS idLocalidad,
                l.localidad,
                pr.id           AS idProvincia,
                pr.provincia,
                o.id            AS idOficina,
                o.nombre        AS oficina,
                pa.id           AS idPais,
                pa.nombre       AS pais
            FROM atl_localidades l
            LEFT JOIN atl_provincias pr ON pr.id = l.id_provincia
            LEFT JOIN atl_oficinas o   ON o.id  = pr.idOficina
            LEFT JOIN atl_pais pa      ON pa.id = pr.id_pais
        ");
    }

    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS reporting_fact_evaluacion_actividad');
        DB::statement('DROP VIEW IF EXISTS reporting_fact_evaluacion_impacto');
        DB::statement('DROP VIEW IF EXISTS reporting_dim_geografia');
    }
}
