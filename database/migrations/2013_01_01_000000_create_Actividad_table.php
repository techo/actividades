<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateActividadTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('Actividad', function(Blueprint $table)
		{
			$table->integer('idActividad', true);

			$table->string('nombreActividad', 300);
			$table->string('descripcion', 3000)->nullable();
			$table->string('estadoConstruccion', 200)->default('Abierta');
			
			$table->integer('idTipo')->index('idTipo');

			$table->dateTime('fechaInicio');
			$table->dateTime('fechaFin');
			$table->dateTime('fechaInicioInscripciones')->nullable();
			$table->dateTime('fechaFinInscripciones')->nullable();
			$table->dateTime('fechaInicioEvaluaciones')->nullable();
			$table->dateTime('fechaFinEvaluaciones')->nullable();

			$table->string('lugar', 300);
			$table->integer('idPais')->unsigned();
            $table->integer('idProvincia')->unsigned();
            $table->integer('idLocalidad')->unsigned();

			$table->string('limiteInscripciones', 20);
			$table->text('mensajeInscripcion', 65535);
			$table->boolean('inscripcionInterna');

			$table->string('moneda', 10)->nullable();
			$table->decimal('costo', 10, 0)->nullable()->default(0);

			$table->dateTime('fechaCreacion');
			$table->dateTime('fechaModificacion');
			$table->integer('idPersonaModificacion')->nullable()->index('fk_Actividad_1_idx');

			//legado
			$table->integer('idUnidadOrganizacional')->index('idUnidadOrganizacional_actividad');
			
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('Actividad');
	}

}
