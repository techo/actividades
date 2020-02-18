<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePuntoEncuentroTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('PuntoEncuentro', function(Blueprint $table)
		{
			$table->integer('idPuntoEncuentro', true);

			$table->string('punto', 100)->nullable();
			$table->time('horario')->nullable();

			$table->integer('idPersona')->nullable()->index('fk_PuntoEncuentro_2_idx');
			$table->integer('idActividad')->nullable()->index('fk_PuntoEncuentro_3_idx');

			$table->integer('idPais')->unsigned();
            $table->integer('idProvincia')->unsigned();
            $table->integer('idLocalidad')->unsigned();

		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('PuntoEncuentro');
	}

}
