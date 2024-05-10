<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCampoTipoInscrtoAplicadoTablaInscriptos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Inscripcion', function (Blueprint $table) {
            $table->json('inscripciones_aplicadas')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Inscripcion', function (Blueprint $table) {
            $table->dropColumn('inscripciones_aplicadas');
        });
    }
}