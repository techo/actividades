<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Capa analítica (datamart) — soluciones/impacto (E), campañas (C1) y comunidades (D).
 *
 * Implementa las familias del backlog (docs/reporting-backlog.md) que ya estaban
 * definidas y no tenían vista propia:
 *  - reporting_fact_solucion : 1 fila por informe de cierre, con total_soluciones
 *    (suma de las 5 cant_soluciones_*) por tipo de solución. Familia E.
 *  - reporting_fact_campania : campañas con tipo / es_nacional (oficina null) / fechas. C1.
 *  - reporting_dim_comunidad : comunidad + ficha + flags de mesa (diagnóstico/plan). D.
 *
 * Período por Actividad.fechaInicio (soluciones) y por fechas propias (campañas).
 */
class CreateReportingSolucionCampaniaComunidadViews extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW reporting_fact_solucion AS
            SELECT
                ic.idActividadInformeCierre,
                ic.idActividad,
                ic.idComunidad,
                a.idPais,
                a.idOficina,
                a.fechaInicio          AS fecha_actividad,
                YEAR(a.fechaInicio)    AS anio,
                ic.soluciones_entregadas AS tipo_solucion,
                (COALESCE(ic.cant_soluciones_voluntariado, 0)
                 + COALESCE(ic.cant_soluciones_corporativos, 0)
                 + COALESCE(ic.cant_soluciones_secundarios, 0)
                 + COALESCE(ic.cant_soluciones_universitarios, 0)
                 + COALESCE(ic.cant_soluciones_familias, 0)) AS total_soluciones,
                ic.numero_participantes,
                ic.numero_beneficiados
            FROM actividad_informe_cierre ic
            JOIN Actividad a ON a.idActividad = ic.idActividad AND a.deleted_at IS NULL
        ");

        DB::statement("
            CREATE OR REPLACE VIEW reporting_fact_campania AS
            SELECT
                c.id                AS idCampania,
                c.nombre,
                c.tipo,
                c.pais_id           AS idPais,
                c.oficina_id        AS idOficina,
                CASE WHEN c.oficina_id IS NULL THEN 1 ELSE 0 END AS es_nacional,
                c.activa,
                c.fecha_inicio,
                c.fecha_fin,
                YEAR(c.fecha_inicio) AS anio_inicio
            FROM campaigns c
        ");

        DB::statement("
            CREATE OR REPLACE VIEW reporting_dim_comunidad AS
            SELECT
                c.idComunidad,
                c.nombre,
                c.idPais,
                c.idOficina,
                c.activo,
                CASE WHEN c.diagnostico IS NOT NULL AND c.diagnostico <> '' THEN 1 ELSE 0 END AS tiene_diagnostico,
                c.fecha_diagnostico,
                CASE WHEN c.plan_de_accion IS NOT NULL AND c.plan_de_accion <> '' THEN 1 ELSE 0 END AS tiene_plan,
                c.fecha_plan_de_accion,
                CASE WHEN f.idFicha IS NOT NULL THEN 1 ELSE 0 END AS tiene_ficha,
                f.anio_inicio_techo,
                f.estado_legalizacion
            FROM Comunidad c
            LEFT JOIN comunidad_ficha_inicial f ON f.idComunidad = c.idComunidad AND f.deleted_at IS NULL
            WHERE c.deleted_at IS NULL
        ");
    }

    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS reporting_fact_solucion');
        DB::statement('DROP VIEW IF EXISTS reporting_fact_campania');
        DB::statement('DROP VIEW IF EXISTS reporting_dim_comunidad');
    }
}
