<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEquipoReunionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipo_reunion', function (Blueprint $table) {
            $table->increments('idReunion');
            $table->unsignedInteger('idEquipo');
            $table->string('nombre');
            $table->dateTime('fecha');
            $table->string('despliegue');
			$table->string('descripcion', 3000)->nullable();
			$table->string('compromisos', 3000)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('idEquipo', 'fk_reunion_equipo')
                ->references('idEquipo')->on('Equipo')
                ->onDelete('CASCADE');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipo_reunion');
    }
}
