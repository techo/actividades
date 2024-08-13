<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreaIncripcionJornadaTable extends Migration
{   
     /**
    * Run the migrations.
    *
    * @return void
    */
   public function up()
   {
        Schema::create('InscripcionJornada', function (Blueprint $table) {
            $table->increments('idInscripcionJornada');
            $table->integer('idInscripcion');
            $table->unsignedInteger('idJornada');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('idInscripcion')->references('idInscripcion')->on('Inscripcion')->onDelete('cascade');
            $table->foreign('idJornada')->references('idJornada')->on('Jornada')->onDelete('cascade');

            $table->unique(['idInscripcion', 'idJornada']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('InscripcionJornada');
    }
}
