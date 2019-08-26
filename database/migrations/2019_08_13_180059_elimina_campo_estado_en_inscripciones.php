<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EliminaCampoEstadoEnInscripciones extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Inscripcion', function (Blueprint $table) {
            $table->dropColumn(['estado']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('Inscripcion', function (Blueprint $table) {
            $table->string('estado', 200);
        });
    }
}
