<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Grupo_Persona no tenía ningún índice secundario: la subquery correlacionada
 * nombreGrupo del listado de inscripciones (InscripcionesSearch) hacía un full
 * scan de la tabla por cada fila. Inscripcion.deleted_at tampoco estaba indexado.
 */
class AgregaIndicesGrupoPersonaEInscripcion extends Migration
{
    public function up()
    {
        Schema::table('Grupo_Persona', function (Blueprint $table) {
            $table->index(['idPersona', 'idActividad'], 'grupo_persona_persona_actividad_index');
            $table->index('idGrupo', 'grupo_persona_grupo_index');
        });

        Schema::table('Inscripcion', function (Blueprint $table) {
            $table->index('deleted_at', 'inscripcion_deleted_at_index');
        });
    }

    public function down()
    {
        Schema::table('Grupo_Persona', function (Blueprint $table) {
            $table->dropIndex('grupo_persona_persona_actividad_index');
            $table->dropIndex('grupo_persona_grupo_index');
        });

        Schema::table('Inscripcion', function (Blueprint $table) {
            $table->dropIndex('inscripcion_deleted_at_index');
        });
    }
}
