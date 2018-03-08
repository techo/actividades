<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCamposEnTablaPuntoEncuentro extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('PuntoEncuentro', 'idPais')) {
            //
            Schema::table('PuntoEncuentro', function (Blueprint $table) {
                $table->integer('idPais')->unsigned();
                $table->integer('idProvincia')->unsigned();
                $table->integer('idLocalidad')->unsigned();
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
        Schema::table('PuntoEncuentro', function (Blueprint $table) {
            $table->dropColumn(['idPais', 'idProvincia', 'idLocalidad']);
        });
    }
}
