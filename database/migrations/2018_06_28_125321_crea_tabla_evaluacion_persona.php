<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreaTablaEvaluacionPersona extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('EvaluacionPersona')) {
            //
            Schema::create('EvaluacionPersona', function (Blueprint $table) {
                $table->increments('idEvaluacionPersona');
                $table->integer('idEvaluador')->unsigned();
                $table->integer('idEvaluado')->unsigned();
                $table->integer('idActividad')->unsigned();
                $table->integer('puntajeSocial')->unsigned()->nullable();
                $table->integer('puntajeTecnico')->unsigned()->nullable();
                $table->text('comentario')->nullable();
                $table->timestamps();
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
        Schema::dropIfExists('EvaluacionPersona');
    }
}
