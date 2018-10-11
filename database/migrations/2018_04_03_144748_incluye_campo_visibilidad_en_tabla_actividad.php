<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class IncluyeCampoVisibilidadEnTablaActividad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('Actividad', 'visibilidad')) {
            Schema::table('Actividad', function (Blueprint $table) {
                $table->boolean('visibilidad')->default(true);
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
        if (Schema::hasColumn('Actividad', 'visibilidad')) {
            Schema::table('Actividad', function (Blueprint $table) {
                $table->dropColumn('visibilidad');
            });
        }
    }
}
