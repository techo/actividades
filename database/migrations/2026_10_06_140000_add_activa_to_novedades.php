<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La barra de novedades del backoffice pasa de mostrar "la última" a rotar entre todas
 * las activas. Las filas existentes (el aviso de 2019 "Ahora sólo verás las Actividades
 * y Personas de tu País!") quedan desactivadas para que no vuelvan a aparecer.
 */
class AddActivaToNovedades extends Migration
{
    public function up()
    {
        Schema::table('novedades', function (Blueprint $table) {
            $table->boolean('activa')->default(true)->after('link');
            $table->index('activa');
        });

        DB::table('novedades')->update(['activa' => false]);
    }

    public function down()
    {
        Schema::table('novedades', function (Blueprint $table) {
            $table->dropIndex(['activa']);
            $table->dropColumn('activa');
        });
    }
}
