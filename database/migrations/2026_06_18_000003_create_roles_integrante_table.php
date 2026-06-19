<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Catálogo de roles de integrante de equipo.
 *
 * Hasta ahora las opciones estaban hardcodeadas en los archivos de traducción
 * (backend.php > roles_integrantes) y el select de integrante-modal.vue las leía del i18n.
 * Integrante.rol ya guarda la CLAVE (ej. 'coordinacion'), así que el catálogo se
 * indexa por `clave` y NO hace falta migrar la columna Integrante.rol.
 */
class CreateRolesIntegranteTable extends Migration
{
    /** clave => nombre (es), tal como están hoy en lang/es_AR/backend.php > roles_integrantes */
    private $roles = [
        'coordinacion'       => 'Coordinación',
        'zonal'              => 'Zonal',
        'voluntariado_equipo' => 'Voluntariado de Equipo',
        'subcoordinacion'    => 'Subcoordinación',
        'implementador'      => 'Implementador/a',
        'gestor'             => 'Gestor/a',
        'jefatura_liderazgo' => 'Jefatura / Liderazgo',
    ];

    public function up()
    {
        Schema::create('roles_integrante', function (Blueprint $table) {
            $table->increments('id');
            $table->string('clave')->unique();
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        foreach ($this->roles as $clave => $nombre) {
            DB::table('roles_integrante')->insert([
                'clave'      => $clave,
                'nombre'     => $nombre,
                'activo'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('roles_integrante');
    }
}
