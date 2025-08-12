<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrearTablaRedesComunitarias extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redes_comunidad', function (Blueprint $table) {
            $table->increments('idRedComunidad');
            $table->unsignedInteger('idComunidad');
            $table->string('nombre');
            $table->string('tipo');
            $table->string('relacion');
            $table->string('presencia');
            $table->string('nombre_contacto')->nullable();
            $table->string('telefono_contacto')->nullable();
            $table->string('mail_contacto')->nullable();
            $table->text('comentarios')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('idComunidad', 'fk_red_comunidad')
                ->references('idComunidad')->on('Comunidad')
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
        Schema::dropIfExists('redes_comunidad');
    }
}
