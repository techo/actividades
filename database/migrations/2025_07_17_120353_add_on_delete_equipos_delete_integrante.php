<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOnDeleteEquiposDeleteIntegrante extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Integrantes', function (Blueprint $table) {
            $table->foreign('idEquipo', 'fk_equipo_integrantes')
                ->references('idEquipo')->on('Equipo')
                ->onDelete('CASCADE');
        });
    }

    public function down()
    {
        Schema::table('Integrantes', function (Blueprint $table) {
            $table->dropForeign('fk_equipo_integrantes');
        });
    }
}
