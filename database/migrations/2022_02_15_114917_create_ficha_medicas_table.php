<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFichaMedicasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ficha_medicas', function (Blueprint $table) {
            $table->increments('idFichaMedica');
            $table->integer('idPersona')->index('idPersona');
            $table->string('contacto_nombre')->nullable();
            $table->string('contacto_telefono')->nullable();
            $table->string('contacto_relacion')->nullable();
            $table->string('grupo_sanguinieo')->nullable();
            $table->string('cobertura_nombre')->nullable();
            $table->string('cobertura_numero')->nullable();
            $table->string('confirma_datos')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ficha_medicas');
    }
}
