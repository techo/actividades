<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeTablaEquipoComunidad extends Migration
{
    public function up()
    {
        Schema::create('equipo_comunidad', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('idComunidad');
            $table->unsignedInteger('idEquipo');
            $table->timestamps();

            // Clave foránea de idComunidad con eliminación en cascada
            $table->foreign('idComunidad')
                ->references('idComunidad')->on('Comunidad')
                ->onDelete('CASCADE');

            // Clave foránea de idEquipo con eliminación en cascada
            $table->foreign('idEquipo')
                ->references('idEquipo')->on('Equipo')
                ->onDelete('CASCADE');
        });
    }

    public function down()
    {
        Schema::dropIfExists('equipo_comunidad');
    }
};