<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgregaIdComunidadAActividadInformeCierre extends Migration
{
    public function up()
    {
        Schema::table('actividad_informe_cierre', function (Blueprint $table) {
            $table->integer('idComunidad')->nullable()->after('idActividad');
        });
    }

    public function down()
    {
        Schema::table('actividad_informe_cierre', function (Blueprint $table) {
            $table->dropColumn('idComunidad');
        });
    }
}
