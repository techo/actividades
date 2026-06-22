<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Modelo estrella — dimensiones de nombres.
 *
 * Los hechos llevan ids (idPais, idOficina, tipo_indicador, idCategoria); las
 * etiquetas legibles viven en dimensiones. Esta migración agrega las dimensiones
 * de nombres que faltaban para que cada id de un hecho resuelva a su nombre:
 *   - reporting_dim_pais     (idPais -> pais)
 *   - reporting_dim_oficina  (idOficina -> oficina)
 *   - reporting_dim_indicador (tipo_indicador -> etiqueta)
 * y suma el nombre del tipo (Tipo.nombre) a reporting_dim_actividad.
 */
class CreateReportingDimNombres extends Migration
{
    public function up()
    {
        DB::statement("
            CREATE OR REPLACE VIEW reporting_dim_pais AS
            SELECT id AS idPais, nombre AS pais, iso2, abreviacion
            FROM atl_pais
        ");

        DB::statement("
            CREATE OR REPLACE VIEW reporting_dim_oficina AS
            SELECT o.id AS idOficina, o.nombre AS oficina, o.id_pais AS idPais
            FROM atl_oficinas o
        ");

        // tipo_indicador es un enum cuyas etiquetas viven en lang/*/backend.php.
        // Se expone acá como dimensión (etiquetas es) para que BI muestre nombres.
        DB::statement("
            CREATE OR REPLACE VIEW reporting_dim_indicador AS
            SELECT 'territorio' AS tipo_indicador, 'Territorio' AS indicador
            UNION ALL SELECT 'construccion_de_viviendas', 'Construcción de viviendas'
            UNION ALL SELECT 'colecta', 'Colecta'
            UNION ALL SELECT 'encuentros', 'Encuentros'
            UNION ALL SELECT 'captacion', 'Captación'
            UNION ALL SELECT 'gestion_y_acompañamiento', 'Gestión y Acompañamiento'
            UNION ALL SELECT 'insercion', 'Inserción'
            UNION ALL SELECT 'renovacion', 'Renovación'
            UNION ALL SELECT 'otras_actividades', 'Otras actividades'
        ");

        // dim_actividad: agrega el nombre del tipo (Tipo.nombre).
        DB::statement("
            CREATE OR REPLACE VIEW reporting_dim_actividad AS
            SELECT a.idActividad, a.nombreActividad, a.idTipo, t.nombre AS tipo,
                   t.tipo_indicador, t.idCategoria, c.nombre AS categoria, a.alcance,
                   a.idPais, a.idOficina, a.fechaInicio, a.fechaFin,
                   YEAR(a.fechaInicio) AS anio, MONTH(a.fechaInicio) AS mes, a.vida_escuela
            FROM Actividad a
            LEFT JOIN Tipo t ON t.idTipo = a.idTipo
            LEFT JOIN atl_CategoriaActividad c ON c.id = t.idCategoria
            WHERE a.deleted_at IS NULL
        ");
    }

    public function down()
    {
        DB::statement('DROP VIEW IF EXISTS reporting_dim_pais');
        DB::statement('DROP VIEW IF EXISTS reporting_dim_oficina');
        DB::statement('DROP VIEW IF EXISTS reporting_dim_indicador');

        // dim_actividad: vuelve a la versión sin el nombre del tipo.
        DB::statement("
            CREATE OR REPLACE VIEW reporting_dim_actividad AS
            SELECT a.idActividad, a.nombreActividad, a.idTipo, t.tipo_indicador,
                   t.idCategoria, c.nombre AS categoria, a.alcance,
                   a.idPais, a.idOficina, a.fechaInicio, a.fechaFin,
                   YEAR(a.fechaInicio) AS anio, MONTH(a.fechaInicio) AS mes, a.vida_escuela
            FROM Actividad a
            LEFT JOIN Tipo t ON t.idTipo = a.idTipo
            LEFT JOIN atl_CategoriaActividad c ON c.id = t.idCategoria
            WHERE a.deleted_at IS NULL
        ");
    }
}
