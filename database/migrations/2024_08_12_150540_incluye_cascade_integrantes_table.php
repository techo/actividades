<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCascadeIntegrantesTable extends Migration
{
    public function up()
    {
        Schema::table('Integrantes', function (Blueprint $table) {
            $table->unsignedInteger('idEquipo')->change();
            $table->foreign('idEquipo')
                  ->references('idEquipo')->on('Equipo')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('Integrantes', function (Blueprint $table) {
            $table->dropForeign(['idEquipo']);
        });
    }
}
