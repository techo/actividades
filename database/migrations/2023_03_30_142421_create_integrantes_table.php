<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntegrantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Integrantes', function (Blueprint $table) {
            $table->increments('idIntegrante');
            $table->integer('idEquipo')->index('idEquipo');
            $table->integer('idPersona')->index('idPersona');
            $table->string('rol');
			$table->boolean('estado');
            $table->dateTime('fechaInicio');
            $table->dateTime('fechaFin')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Integrantes');
    }
}
