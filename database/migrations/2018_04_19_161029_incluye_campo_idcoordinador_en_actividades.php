<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCampoIdcoordinadorEnActividades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('Actividad', 'idCoordinador')) {
            Schema::table('Actividad', function (Blueprint $table) {
                // Nullable para que sea retro compatible con pilote
                $table->integer('idCoordinador')->unsigned()->nullable();
            });
        };
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Actividad', function (Blueprint $table) {
            $table->dropColumn('idCoordinador');
        });
    }
}
