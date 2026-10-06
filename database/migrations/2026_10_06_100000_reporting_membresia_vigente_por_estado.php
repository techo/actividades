<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Capa analítica — equipo permanente: "vigente" exige estado activo + foto mensual.
 *
 * Problema (oct-2026, Argentina): el indicador "Voluntarios/as en equipo permanente
 * (TOTAL)" daba 1.109 personas contra ~720 reales. La vista consideraba vigente
 * toda membresía con fechaFin NULL/futura, pero en prod hay ~740 membresías
 * marcadas Inactivas (estado=0) SIN fechaFin — cargadas 2024-2025, antes de que el
 * modal de integrante exigiera fecha de fin al inactivar. Esas 579 personas
 * inflaban el conteo.
 *
 * Cambios:
 *  - vigente = estado=1 AND (fechaFin NULL o futura).
 *  - Se expone `estado` y `fecha_fin_efectiva`: fechaFin, o para las inactivas
 *    legacy sin fecha, DATE(updated_at) (mejor aproximación de cuándo se las
 *    inactivó). Con esto el MetricRegistry arma la foto a fin de cada mes
 *    (periodo 'snapshot'): una baja impacta desde su mes, un alta suma desde su
 *    mes, y no es acumulativo.
 */
class ReportingMembresiaVigentePorEstado extends Migration
{
    public function up()
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

    public function down()
    {
        DB::statement("
            CREATE OR REPLACE VIEW reporting_fact_membresia AS
            SELECT ig.idIntegrante, rp.person_key, ig.idEquipo,
                   e.idOficina, e.idPais, e.area_id, ig.idComunidad, ig.rol,
                   ig.fechaInicio, ig.fechaFin,
                   CASE WHEN ig.fechaFin IS NULL OR ig.fechaFin > NOW() THEN 1 ELSE 0 END AS vigente
            FROM Integrantes ig
            JOIN Equipo e ON e.idEquipo=ig.idEquipo AND e.deleted_at IS NULL
            LEFT JOIN reporting_person rp ON rp.idPersona=ig.idPersona
            WHERE ig.deleted_at IS NULL
        ");
    }
}
