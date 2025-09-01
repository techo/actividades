<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrearTablaEquipoReunionRefente extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipo_reunion_referente', function (Blueprint $table) {
            $table->unsignedInteger('idReunion');
            $table->unsignedInteger('idReferenteComunidad');
            $table->timestamps();
            $table->softDeletes();
            
            $table->primary(['idReunion', 'idReferenteComunidad']);
            $table->foreign('idReunion')->references('idReunion')->on('equipo_reunion')->onDelete('cascade');
            $table->foreign('idReferenteComunidad')->references('idReferenteComunidad')->on('referentes_comunidad')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipo_reunion_referente');
    }
}
