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
			$table->engine = 'InnoDB';
			$table->charset = 'utf8';
			$table->collation = 'utf8_spanish_ci';
			
			$table->integer('idPuntoEncuentro', true);
			$table->integer('idZona')->nullable()->index('fk_PuntoEncuentro_1_idx');
			$table->string('punto', 100)->nullable();
			$table->time('horario')->nullable();
			$table->integer('idPersona')->nullable()->index('fk_PuntoEncuentro_2_idx');
			$table->integer('idActividad')->nullable()->index('fk_PuntoEncuentro_3_idx');
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
