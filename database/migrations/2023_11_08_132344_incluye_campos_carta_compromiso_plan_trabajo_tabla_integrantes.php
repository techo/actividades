<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCamposCartaCompromisoPlanTrabajoTablaIntegrantes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Integrantes', function (Blueprint $table) {
            $table->string('archivo_carta_compromiso')->nullable();
            $table->string('archivo_plan_de_trabajo')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Integrantes', function (Blueprint $table) {
            $table->dropColumn('archivo_carta_compromiso');
            $table->dropColumn('archivo_plan_de_trabajo');
        });
    }
}
