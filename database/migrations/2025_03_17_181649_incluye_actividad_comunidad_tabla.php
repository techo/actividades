<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeActividadComunidadTabla extends Migration
{
    public function up()
    {
        Schema::create('actividad_comunidad', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('idComunidad');
            $table->integer('idActividad');
            $table->timestamps();

            // Clave foránea de idComunidad con eliminación en cascada
            $table->foreign('idComunidad')
                ->references('idComunidad')->on('Comunidad')
                ->onDelete('CASCADE');

            // Clave foránea de idActividad con eliminación en cascada
            $table->foreign('idActividad')
                ->references('idActividad')->on('Actividad')
                ->onDelete('CASCADE');
        });
    }

    public function down()
    {
        Schema::dropIfExists('actividad_comunidad');
    }
};