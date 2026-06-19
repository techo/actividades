<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Catálogo oficial de áreas (fijo, igual en todos los países) y enlace desde Equipo.
 *
 * Hasta ahora el área era un <select> hardcodeado en resources/js/components/
 * backoffice/equipos/equipos-form.vue, cuyo `value` (etiqueta en español) se
 * guardaba como texto en Equipo.area. Esta migración lleva ese catálogo a la BD:
 *   - tabla `areas` con `clave` (i18n key, igual a la usada en lang/backend.php) y `nombre` (es).
 *   - Equipo.area_id (FK) con backfill por coincidencia exacta contra Equipo.area.
 * Al ser un select, el backfill mapea el 100% (no hay typos). La columna Equipo.area
 * se mantiene por ahora; el siguiente paso es que el Vue lea el catálogo vía API.
 */
class CreateAreasTableAndLinkEquipo extends Migration
{
    /** clave i18n => nombre (es), tal como están hoy en el <select> de equipos-form.vue */
    private $areas = [
        'administration_and_finance' => 'Administración y Finanzas',
        'communications'             => 'Comunicaciones',
        'construction_and_logistics' => 'Construcción y Logística',
        'fund_development'           => 'Desarrollo de Fondos',
        'housing_and_habitat'        => 'Vivienda y Hábitat',
        'detection_and_assignment'   => 'Detección y Asignación',
        'general_management'         => 'Dirección General',
        'training_and_volunteering'  => 'Formación y Voluntariado',
        'legal'                      => 'Legal',
        'people'                     => 'Personas',
        'community_management'       => 'Gestión Comunitaria',
        'programs_and_projects'      => 'Programas y Proyectos',
        'teams'                      => 'Equipos',
        'social_research_center'     => 'Centro de Investigación Social',
        'community_processes'        => 'Procesos Comunitarios',
    ];

    public function up()
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('clave')->unique();
            $table->string('nombre')->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        foreach ($this->areas as $clave => $nombre) {
            DB::table('areas')->insert([
                'clave'      => $clave,
                'nombre'     => $nombre,
                'activo'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('Equipo', function (Blueprint $table) {
            $table->unsignedInteger('area_id')->nullable()->after('area');
            $table->foreign('area_id')->references('id')->on('areas')->onDelete('set null');
        });

        // Backfill por coincidencia exacta (el área era un select: sin typos).
        DB::statement('
            UPDATE Equipo e
            JOIN areas a ON a.nombre = e.area
            SET e.area_id = a.id
        ');
    }

    public function down()
    {
        Schema::table('Equipo', function (Blueprint $table) {
            $table->dropForeign(['area_id']);
            $table->dropColumn('area_id');
        });

        Schema::dropIfExists('areas');
    }
}
