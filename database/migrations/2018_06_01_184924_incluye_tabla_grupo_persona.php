<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeTablaGrupoPersona extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('Grupo_Persona')) {
            Schema::create('Grupo_Persona', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('idPersona')->unsigned();
                $table->integer('idGrupo')->unsigned();
                $table->integer('idActividad')->unsigned();
                $table->string('rol')->nullable();
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
        Schema::dropIfExists('Grupo_Persona');
    }
}
