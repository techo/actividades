<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgregaCampoIdComunidadTablaEquipoReunion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipo_reunion', function (Blueprint $table) {
            $table->unsignedInteger('idComunidad')->nullable();

            $table->foreign('idComunidad')
                ->references('idComunidad')->on('Comunidad')
                ->onDelete('SET NULL');
                
        });
    }

    public function down()
    {
        Schema::table('equipo_reunion', function (Blueprint $table) {
            $table->dropColumn(['idComunidad']);
        });
    }
}
