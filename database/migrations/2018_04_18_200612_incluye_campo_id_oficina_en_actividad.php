<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCampoIdOficinaEnActividad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('Actividad', 'idOficina')) {
            //
            Schema::table('Actividad', function (Blueprint $table) {
                $table->integer('idOficina')
                    ->unsigned()
                    ->nullable()
                    ->after('idUnidadOrganizacional');
            });
            DB::table('UnidadOrganizacional')->insert([
                [
                    'idUnidadPadre' => null,
                    'idPais' => 1,
                    'heredarPermisos' => 0,
                    'idCiudad' => 167,
                    'claveApiActiveCampaign' => ' ',
                    'nombre' => 'No Aplica',
                    'direccion' => '',

                ]
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::table('Actividad', function (Blueprint $table) {
            if (Schema::hasColumn('Actividad', 'idOficina')) {
                $table->dropColumn('idOficina');
            }
        });

        DB::table('UnidadOrganizacional')->where('nombre', 'No Aplica')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
