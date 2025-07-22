<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCampoTipoReunionTablaEquipoReunion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipo_reunion', function (Blueprint $table) {
            $table->string('tipo_reunion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipo_reunion', function (Blueprint $table) {
                $table->dropColumn('tipo_reunion');
        });
    }
}
