<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncorporaCamposAcuerdoEspecificoMenoresTablaActividad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Actividad', function (Blueprint $table) {
            $table->string('acuerdo_especifico_url')->nullable();
            $table->string('acuerdo_menores_url')->nullable();
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
            $table->dropColumn('acuerdo_especifico_url');
            $table->dropColumn('acuerdo_menores_url');
        });
    }
}
