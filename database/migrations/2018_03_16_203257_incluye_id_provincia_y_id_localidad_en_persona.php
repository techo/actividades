<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeIdProvinciaYIdLocalidadEnPersona extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('Persona', 'idProvincia')) {
            //
            Schema::table('Persona', function (Blueprint $table) {
                $table->integer('idLocalidad')->unsigned()->after('idPais');
                $table->integer('idProvincia')->unsigned()->after('idPais');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Persona', function (Blueprint $table) {
            $table->dropColumn(['idProvincia', 'idLocalidad']);
        });

    }
}
