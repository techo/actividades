<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Agrega Comunidad.fecha_fin_trabajo (editable desde el backoffice) — marca cuándo
 * TECHO finalizó el trabajo con la comunidad. Habilita la métrica
 * "comunidades donde finalizamos el trabajo" (D5). Recrea reporting_dim_comunidad
 * para exponer el campo.
 */
class IncluyeCampoFechaFinTrabajoTablaComunidad extends Migration
{
    public function up()
    {
        Schema::table('Comunidad', function (Blueprint $table) {
            $table->date('fecha_fin_trabajo')->nullable()->after('fecha_plan_de_accion');
        });

        DB::statement("
            CREATE OR REPLACE VIEW reporting_dim_comunidad AS
            SELECT
                c.idComunidad, c.nombre, c.idPais, c.idOficina, c.activo,
                CASE WHEN c.diagnostico IS NOT NULL AND c.diagnostico <> '' THEN 1 ELSE 0 END AS tiene_diagnostico,
                c.fecha_diagnostico,
                CASE WHEN c.plan_de_accion IS NOT NULL AND c.plan_de_accion <> '' THEN 1 ELSE 0 END AS tiene_plan,
                c.fecha_plan_de_accion,
                c.fecha_fin_trabajo,
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
        DB::statement("
            CREATE OR REPLACE VIEW reporting_dim_comunidad AS
            SELECT
                c.idComunidad, c.nombre, c.idPais, c.idOficina, c.activo,
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

        Schema::table('Comunidad', function (Blueprint $table) {
            $table->dropColumn('fecha_fin_trabajo');
        });
    }
}
