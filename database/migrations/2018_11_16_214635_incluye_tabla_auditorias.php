<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeTablaAuditorias extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->string('tabla');
            $table->index('tabla');
            $table->integer('idPersona')->nullable();
            $table->foreign('idPersona')->references('idPersona')->on('Persona')->onDelete('set null');

            $table->integer('id_registro')->unisgned();

            $table->text('informacion');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auditorias');
    }
}
