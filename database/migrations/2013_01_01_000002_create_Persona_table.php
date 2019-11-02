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

			/*
			$table->integer('idPaisResidencia')->index('idPaisResidencia');
			$table->integer('idCiudad')->index('idCiudad');
			$table->integer('idUniversidad')->nullable()->index('fk_Persona_Universidad');
			$table->integer('idColegio')->nullable()->index('fk_Persona_Colegio');
			$table->string('universidad_string', 300)->nullable();
			$table->string('carrera', 300);
			$table->string('anoEstudio', 20);
			$table->string('loginActiveDirectory', 100)->nullable();
			$table->string('loginMailUTPMP', 200)->nullable();
			$table->boolean('nuevaPortada')->default(0);
			$table->string('idContactoCTCT', 300);
			$table->string('statusCTCT', 30);
			$table->string('lenguaje', 300);
			$table->string('captacion', 500)->nullable();
			$table->binary('configuracion', 65535)->nullable();
			$table->integer('idEmpresa')->nullable()->index('Persona_ibfk_4');
			$table->boolean('terminosVoluntarioPermanente')->nullable()->default(0);
			$table->boolean('terminosVoluntarioMasivo')->nullable()->default(0);
			$table->integer('idRegionLT')->index('fk_Persona_RegionLT_idx');
			$table->string('calle')->nullable();
			$table->string('numero')->nullable();
			$table->string('piso')->nullable();
			$table->string('dpto')->nullable();
			$table->dateTime('fechaInscripcion')->nullable();
			$table->dateTime('ultimaEntrada')->nullable();
			$table->dateTime('ultimaActualizacion')->nullable();
			$table->string('dispHoraria', 400)->nullable();
			$table->integer('idCarrera')->nullable()->index('fk_Persona_Carrera_idx');
			$table->integer('idAreaEstudio')->nullable()->index('Persona_ibfk_5_idx');
			$table->char('Vecino_Voluntario', 1)->nullable();
			$table->string('Barrio_Vecino', 40)->nullable();
			$table->index(['idPaisResidencia','dni'], 'ix_Persona_DniPais');
			*/
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
