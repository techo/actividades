<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateInscripcionTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('Inscripcion', function(Blueprint $table)
		{	
			$table->integer('idInscripcion', true);

			$table->integer('idActividad')->nullable()->index('idActividad');
			$table->integer('idPersona')->index('idPersona');
			$table->integer('idPuntoEncuentro')->nullable()->index('Inscripcion_ibfk_46_idx');

			$table->string('rol', 300)->nullable();
			$table->dateTime('fechaInscripcion')->nullable();
			$table->string('estado', 200);

			$table->integer('presente')->nullable();
			$table->integer('pago')->nullable();

			$table->decimal('montoPago', 10, 0)->nullable();
			$table->string('moneda', 10)->nullable();
			$table->dateTime('fechaPago')->nullable();

			$table->integer('idPersonaModificacion')->nullable()->index('fk_Inscripcion_1_idx');
			$table->dateTime('fechaCreacion')->nullable();
			$table->dateTime('fechaModificacion')->nullable();

		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('Inscripcion');
	}

}
