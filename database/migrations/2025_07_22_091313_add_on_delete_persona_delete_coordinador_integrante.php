<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOnDeletePersonaDeleteCoordinadorIntegrante extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Coordinadores', function (Blueprint $table) {
            $table->foreign('idPersona', 'fk_persona_coordinador')
                ->references('idPersona')->on('Persona')
                ->onDelete('CASCADE');
        });

        Schema::table('Integrantes', function (Blueprint $table) {
            $table->foreign('idPersona', 'fk_persona_integrante')
                ->references('idPersona')->on('Persona')
                ->onDelete('CASCADE');
        });
    }

    public function down()
    {
        Schema::table('Coordinadores', function (Blueprint $table) {
            $table->dropForeign('fk_persona_coordinador');
        });

        Schema::table('Integrantes', function (Blueprint $table) {
            $table->dropForeign('fk_persona_integrante');
        });
    }
}
