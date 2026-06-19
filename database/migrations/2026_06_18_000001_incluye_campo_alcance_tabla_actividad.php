<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Agrega el campo `alcance` a Actividad para distinguir el alcance territorial
 * de la actividad (ej. encuentros locales vs nacionales) en reporting.
 * Valores esperados: 'local' | 'nacional' | 'regional'. Nullable: las actividades
 * existentes quedan sin alcance hasta el backfill (a definir).
 */
class IncluyeCampoAlcanceTablaActividad extends Migration
{
    public function up()
    {
        Schema::table('Actividad', function (Blueprint $table) {
            $table->string('alcance')->nullable()->after('estadoConstruccion');
        });
    }

    public function down()
    {
        Schema::table('Actividad', function (Blueprint $table) {
            $table->dropColumn('alcance');
        });
    }
}
