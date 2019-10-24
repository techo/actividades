<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCampoEstadoEnPuntoencuentro extends Migration
{
    /**
    * Run the migrations.
    *
    * @return void
    */
    public function up()
    {
        Schema::table('PuntoEncuentro', function (Blueprint $table) {
            $table->integer('estado')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('PuntoEncuentro', function (Blueprint $table) {
                $table->dropColumn('estado');
        });
    }
}
