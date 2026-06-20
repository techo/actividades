<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fase 4 — Row Level Security (mapeo para Power BI).
 *
 * MySQL 5.7 no tiene RLS nativo; el filtrado se hace en el modelo de Power BI
 * (roles RLS con DAX) usando esta vista de mapeo usuario -> país.
 *
 * Una fila por usuario de backoffice (rol admin o coordinador), con su país
 * permitido. Convención: idPais NULL o 0 => acceso global (ve todos los países),
 * marcado con es_global = 1.
 *
 * Configuración en Power BI (rol RLS sobre las tablas de hechos):
 *   VAR u = LOOKUPVALUE(reporting_acceso_usuario[idPais], reporting_acceso_usuario[email], USERPRINCIPALNAME())
 *   VAR g = LOOKUPVALUE(reporting_acceso_usuario[es_global], reporting_acceso_usuario[email], USERPRINCIPALNAME())
 *   RETURN g = 1 || fact[idPais] = u
 */
class CreateReportingAccesoUsuarioView extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW reporting_acceso_usuario AS
            SELECT DISTINCT
                p.mail AS email,
                p.idPaisPermitido AS idPais,
                CASE WHEN p.idPaisPermitido IS NULL OR p.idPaisPermitido = 0 THEN 1 ELSE 0 END AS es_global
            FROM Persona p
            JOIN model_roles mr ON mr.model_id = p.idPersona AND mr.model_type LIKE '%Persona'
            JOIN roles r ON r.id = mr.role_id AND r.name IN ('admin', 'coordinador')
            WHERE p.deleted_at IS NULL
              AND p.mail IS NOT NULL
        ");
    }

    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS reporting_acceso_usuario');
    }
}
