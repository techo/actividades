<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCamposFechaDiagnosticoFechaPlanDeAccionTablaComunidades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('Comunidad', function (Blueprint $table) {
            $table->date('fecha_diagnostico')->nullable();
            $table->date('fecha_plan_de_accion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Comunidad', function (Blueprint $table) {
            $table->dropColumn('fecha_diagnostico');
            $table->dropColumn('fecha_plan_de_accion');
        });
    }
}
