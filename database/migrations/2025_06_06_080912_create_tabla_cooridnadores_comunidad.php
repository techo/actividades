<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTablaCooridnadoresComunidad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('coordinadores_comunidad', function (Blueprint $table) {
            $table->increments('idCoordinadorComunidad');
            $table->integer('idPersona')->index('fk_coordinadores_comunidad_2_idx');
            $table->unsignedInteger('idComunidad')->index('fk_coordinadores_comunidad_3_idx');
            $table->timestamps();

            $table->foreign('idComunidad')
            ->references('idComunidad')->on('Comunidad')
            ->onDelete('CASCADE');

            $table->foreign('idPersona')
                ->references('idPersona')->on('Persona')
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
        Schema::dropIfExists('coordinadores_comunidad');
    }
}
