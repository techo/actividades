<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsSuscribeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Suscripciones', function (Blueprint $table) {
            $table->text('perfil_seleccionado')->nullable();
            $table->text('tematica')->nullable();
            $table->text('tiempo_disponible')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Suscripciones', function (Blueprint $table) {
            $table->dropColumn('perfil_seleccionado');
            $table->dropColumn('tematica');
            $table->dropColumn('tiempo_disponible');
        });
    }
}
