<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePersonaTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('Persona', function(Blueprint $table)
		{
			$table->integer('idPersona', true);

			$table->integer('idPais')->index('idPais');
			$table->integer('idUnidadOrganizacional');

			$table->string('nombres', 250);
			$table->string('apellidoPaterno', 250);
			$table->string('apellidoMaterno', 250)->nullable();
			$table->dateTime('fechaNacimiento');
			$table->string('telefono', 250)->nullable();
			$table->string('telefonoMovil', 250);
			$table->string('genero', 1)->nullable();
			$table->string('dni', 50);
			$table->string('mail', 250)->index('mail');
			$table->string('password', 250);

			$table->integer('recibirMails')->nullable();
			$table->string('remember_token', 100)->nullable();
			$table->index(['apellidoPaterno','apellidoMaterno','nombres'], 'idx_Persona_1');

		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('Persona');
	}

}
