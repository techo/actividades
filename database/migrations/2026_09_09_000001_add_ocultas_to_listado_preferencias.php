<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Las columnas de seguimiento (custom_*) pasan a estar visibles por defecto para
 * todo el equipo. `ocultas` guarda, por usuario, las keys que ese usuario decidió
 * ocultar para sí; el resto de las custom se muestran aunque no estén en `columnas`.
 */
class AddOcultasToListadoPreferencias extends Migration
{
    public function up()
    {
        Schema::table('listado_preferencias', function (Blueprint $table) {
            // Array JSON de keys ocultadas explícitamente por el usuario: ["custom_5"]
            $table->json('ocultas')->nullable()->after('columnas');
        });
    }

    public function down()
    {
        Schema::table('listado_preferencias', function (Blueprint $table) {
            $table->dropColumn('ocultas');
        });
    }
}
