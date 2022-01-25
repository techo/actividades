<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgregaCampoPuntajeGeneroTablaEvaluacionPersona extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('EvaluacionPersona', function (Blueprint $table) {
            $table->integer('puntajeGenero')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('EvaluacionPersona', function (Blueprint $table) {
                $table->dropColumn('puntajeGenero');
        });
    }
}

