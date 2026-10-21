<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Capa analítica — equipo permanente: exponer `despliegue` para separar áreas vs
 * comunidades.
 *
 * Problema (oct-2026, Buenos Aires Norte): el indicador separaba áreas/comunidades
 * por `idComunidad IS NULL`, pero el equipo cuenta por el campo Despliegue del
 * integrante (Oficina | Comunidad | Otras). Hay membresías con despliegue
 * "Comunidad" sin comunidad cargada (caían en áreas) y con despliegue "Oficina"
 * con un idComunidad viejo (el select de comunidad se oculta pero conserva el
 * valor). En AR eran 32 membresías; en BA Norte la tabla daba 28/55 vs 24/59.
 *
 * Misma definición que 2026_10_06_100000 + columna `despliegue`.
 */
class ReportingMembresiaExponeDespliegue extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW reporting_fact_membresia AS
            SELECT ig.idIntegrante, rp.person_key, ig.idEquipo,
                   e.idOficina, e.idPais, e.area_id, ig.idComunidad, ig.despliegue, ig.rol, ig.estado,
                   ig.fechaInicio, ig.fechaFin,
                   CASE WHEN ig.fechaFin IS NOT NULL THEN ig.fechaFin
                        WHEN ig.estado = 0 THEN DATE(ig.updated_at)
                        ELSE NULL END AS fecha_fin_efectiva,
                   CASE WHEN ig.estado = 1 AND (ig.fechaFin IS NULL OR ig.fechaFin > NOW()) THEN 1 ELSE 0 END AS vigente
            FROM Integrantes ig
            JOIN Equipo e ON e.idEquipo=ig.idEquipo AND e.deleted_at IS NULL
            LEFT JOIN reporting_person rp ON rp.idPersona=ig.idPersona
            WHERE ig.deleted_at IS NULL
        ");
    }

    public function down()
    {
        DB::statement("
            CREATE OR REPLACE VIEW reporting_fact_membresia AS
            SELECT ig.idIntegrante, rp.person_key, ig.idEquipo,
                   e.idOficina, e.idPais, e.area_id, ig.idComunidad, ig.rol, ig.estado,
                   ig.fechaInicio, ig.fechaFin,
                   CASE WHEN ig.fechaFin IS NOT NULL THEN ig.fechaFin
                        WHEN ig.estado = 0 THEN DATE(ig.updated_at)
                        ELSE NULL END AS fecha_fin_efectiva,
                   CASE WHEN ig.estado = 1 AND (ig.fechaFin IS NULL OR ig.fechaFin > NOW()) THEN 1 ELSE 0 END AS vigente
            FROM Integrantes ig
            JOIN Equipo e ON e.idEquipo=ig.idEquipo AND e.deleted_at IS NULL
            LEFT JOIN reporting_person rp ON rp.idPersona=ig.idPersona
            WHERE ig.deleted_at IS NULL
        ");
    }
}
