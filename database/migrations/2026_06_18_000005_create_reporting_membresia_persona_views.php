<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Capa analítica (datamart) — vistas de membresía permanente, equipo y persona.
 *
 * Backbone de los tableros "Equipo Permanente" y de la demografía (género / edad
 * por rango), que en Power BI hoy se reconstruyen mal.
 *
 * Reglas (confirmadas con negocio):
 *  - equipo permanente vigente = Integrante con fechaFin NULL o futura.
 *  - antigüedad en equipo permanente = desde la menor Integrante.fechaInicio (se
 *    calcula en consulta, no es flag de fila).
 *
 * dim_persona NO expone PII (sin nombre/mail/dni/teléfono/fecha de nacimiento):
 * solo género, rango de edad bucketizado y geografía. La person_key surrogate y
 * el RLS son Fase 4.
 */
class CreateReportingMembresiaPersonaViews extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW reporting_dim_persona AS
            SELECT
                p.idPersona,
                p.genero,
                CASE
                    WHEN p.fechaNacimiento IS NULL THEN NULL
                    WHEN TIMESTAMPDIFF(YEAR, p.fechaNacimiento, CURDATE()) BETWEEN 18 AND 21 THEN '18 a 21'
                    WHEN TIMESTAMPDIFF(YEAR, p.fechaNacimiento, CURDATE()) BETWEEN 22 AND 25 THEN '22 a 25'
                    ELSE '26 o más'
                END AS rango_edad,
                p.canal_contacto,
                p.idPais,
                p.idProvincia,
                p.idLocalidad
            FROM Persona p
            WHERE p.deleted_at IS NULL
        ");

        DB::statement("
            CREATE OR REPLACE VIEW reporting_dim_equipo AS
            SELECT
                e.idEquipo,
                e.nombre,
                e.idOficina,
                e.idPais,
                e.idEquipoPadre,
                e.area_id,
                ar.nombre AS area,
                e.activo,
                e.fechaInicio,
                e.fechaFin
            FROM Equipo e
            LEFT JOIN areas ar ON ar.id = e.area_id
            WHERE e.deleted_at IS NULL
        ");

        DB::statement("
            CREATE OR REPLACE VIEW reporting_fact_membresia AS
            SELECT
                ig.idIntegrante,
                ig.idPersona,
                ig.idEquipo,
                e.idOficina,
                e.idPais,
                e.area_id,
                ig.idComunidad,
                ig.rol,
                ig.fechaInicio,
                ig.fechaFin,
                CASE WHEN ig.fechaFin IS NULL OR ig.fechaFin > NOW() THEN 1 ELSE 0 END AS vigente
            FROM Integrantes ig
            JOIN Equipo e ON e.idEquipo = ig.idEquipo AND e.deleted_at IS NULL
            WHERE ig.deleted_at IS NULL
        ");
    }

    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS reporting_fact_membresia');
        DB::statement('DROP VIEW IF EXISTS reporting_dim_equipo');
        DB::statement('DROP VIEW IF EXISTS reporting_dim_persona');
    }
}
