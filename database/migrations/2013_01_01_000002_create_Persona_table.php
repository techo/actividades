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

			$table->string('nombres', 300);
			$table->string('apellidoPaterno', 300);
			$table->string('apellidoMaterno', 300)->nullable();
			$table->dateTime('fechaNacimiento');
			$table->string('telefono', 300)->nullable();
			$table->string('telefonoMovil', 300);
			$table->string('sexo', 1)->nullable();
			$table->string('dni', 50);
			$table->string('mail', 300)->index('mail');
			$table->string('password', 400);

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
