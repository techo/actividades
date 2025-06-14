<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEquipoReunionPersonaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipo_reunion_persona', function (Blueprint $table) {
            $table->unsignedInteger('idReunion');
            $table->integer('idPersona');
            $table->timestamps();
        
            $table->primary(['idReunion', 'idPersona']);
            $table->foreign('idReunion')->references('idReunion')->on('equipo_reunion')->onDelete('cascade');
            $table->foreign('idPersona')->references('idPersona')->on('Persona')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipo_reunion_persona');
    }
}
