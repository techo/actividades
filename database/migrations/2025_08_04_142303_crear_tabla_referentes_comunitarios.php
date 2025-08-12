<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CrearTablaReferentesComunitarios extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referentes_comunidad', function (Blueprint $table) {
            $table->increments('idReferenteComunidad');
            $table->unsignedInteger('idComunidad');
            $table->string('nombre');
            $table->string('rol');
            $table->string('telefono')->nullable();
            $table->string('mail')->nullable();
            $table->string('documento')->nullable();
            $table->string('comentarios')->nullable();
			$table->boolean('estado');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('idComunidad', 'fk_referente_comunidad')
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
        Schema::dropIfExists('referentes_comunidad');
    }
}
