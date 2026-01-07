<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCamposSuscripcionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Suscripciones', function (Blueprint $table) {
            $table->string('nombre')->nullable();
            $table->string('apellido')->nullable();
            $table->string('dni')->nullable();
			$table->string('genero', 1)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('telefono')->nullable();
            $table->unsignedInteger('idProvincia')->nullable();
            $table->unsignedInteger('idLocalidad')->nullable();
            $table->string('ocupacion_actual')->nullable();
            $table->string('canal_contacto')->nullable();
            $table->boolean('experiencia_previa')->default(false);

            // Clave foránea de idLocalidad con SET NULL en cascada
            $table->foreign('idLocalidad')
                ->references('id')->on('atl_localidades')
                ->onDelete('SET NULL');

            // Clave foránea de idLocalidad con SET NULL en cascada
            $table->foreign('idProvincia')
                ->references('id')->on('atl_provincias')
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
        //
    }
}
