<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSuscribesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Suscripciones', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
			$table->string('mail', 250);
			$table->string('filtro_categorias', 500)->nullable();
			$table->string('filtro_ubicaciones', 500)->nullable();
            $table->integer('idPersona')->nullable();
			$table->integer('idPais')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Suscripciones');
    }
}
