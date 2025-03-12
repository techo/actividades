<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreaComunidadesTabla extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Comunidad', function (Blueprint $table) {
            $table->increments('idComunidad');
            $table->unsignedInteger('idOficina')->nullable();
            $table->unsignedInteger('idLocalidad')->nullable();
            $table->unsignedInteger('idPais');
            
            $table->string('nombre');
			$table->boolean('activo');
            $table->softDeletes();
            $table->timestamps();

        // Clave foránea de idOficina con SET NULL en cascada
            $table->foreign('idOficina')
                ->references('id')->on('atl_oficinas')
                ->onDelete('SET NULL');

            // Clave foránea de idLocalidad con SET NULL en cascada
            $table->foreign('idLocalidad')
                ->references('id')->on('atl_localidades')
                ->onDelete('SET NULL');


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Comunidad');
    }
}
