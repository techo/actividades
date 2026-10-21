<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Orden elegido por el usuario en un listado ("campo|dir[,campo|dir]", el mismo formato
 * que manda el datatable en `sort`). Se recuerda para no perderlo al recargar (reclamos
 * de "las inscripciones aparecen desordenadas" al tomar asistencia).
 *
 * `columnas` pasa a nullable: una fila puede existir solo por el orden, sin que el
 * usuario haya tocado las columnas (null = usar los defaults del catálogo).
 */
class AddOrdenToListadoPreferencias extends Migration
{
    public function up()
    {
        Schema::table('listado_preferencias', function (Blueprint $table) {
            $table->string('orden', 255)->nullable()->after('ocultas');
        });
        DB::statement('ALTER TABLE listado_preferencias MODIFY columnas JSON NULL');
    }

    public function down()
    {
        Schema::table('listado_preferencias', function (Blueprint $table) {
            $table->dropColumn('orden');
        });
    }
}
