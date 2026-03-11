<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCampoVidaEscuelaAreaTablaActividades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Actividad', function (Blueprint $table) {
            $table->boolean('vida_escuela')->nullable();
            $table->unsignedInteger('idEquipo')->nullable();

            // Clave foránea de idEquipo con eliminación en cascada
            $table->foreign('idEquipo')
                ->references('idEquipo')->on('Equipo')
                ->onDelete('set null');
        });

        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Actividad', function (Blueprint $table) {
            // Eliminar la clave foránea antes de eliminar la columna
            $table->dropForeign(['idEquipo']);
            $table->dropColumn('idEquipo');
            $table->dropColumn('vida_escuela');
        });
    }
}
