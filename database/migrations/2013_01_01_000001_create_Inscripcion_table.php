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
			$table->engine = 'InnoDB';
			$table->charset = 'utf8';
			$table->collation = 'utf8_spanish_ci';
			
			$table->integer('idInscripcion', true);
			$table->integer('idActividad')->nullable()->index('idActividad');
			$table->integer('idPersona')->index('idPersona');
			$table->integer('idUnidadOrganizacional')->nullable()->index('idUnidadOrganizacional_inscripcion');
			$table->integer('idEscuelaRol')->nullable()->index('idEscuelaRol');
			$table->integer('idCuadrillaRol')->nullable()->index('idCuadrillaRol');
			$table->integer('idActividadRol')->nullable()->index('idActividadRol');
			$table->integer('idMesaTrabajoRol')->nullable()->index('idMesaTrabajoRol');
			$table->integer('idProgramaRol')->nullable()->index('idProgramaRol');
			$table->integer('idMesaTrabajoLTRol')->nullable()->index('idMesaTrabajoLTRol');
			$table->integer('idLocalidadRol')->nullable()->index('idLocalidadRol');
			$table->string('rol', 300)->nullable();
			$table->dateTime('fechaInscripcion')->nullable();
			$table->dateTime('fechaFin')->nullable();
			$table->string('estado', 200);
			$table->boolean('evaluacion');
			$table->boolean('aceptarCompromiso')->default(0);
			$table->string('acompanante', 200)->nullable();
			$table->text('comentarios', 65535)->nullable();
			$table->dateTime('fechaCreacion')->nullable();
			$table->dateTime('fechaModificacion')->nullable();
			$table->integer('idPersonaModificacion')->nullable()->index('fk_Inscripcion_1_idx');
			$table->decimal('montoPago', 10, 0)->nullable();
			$table->string('moneda', 10)->nullable();
			$table->decimal('subsidio', 10, 0)->nullable();
			$table->integer('idRazonSubsidio')->nullable()->index('Inscripcion_ibfk_45_idx');
			$table->integer('presente')->nullable();
			$table->string('puntoEnvio', 200)->nullable();
			$table->string('captacion', 200)->nullable();
			$table->integer('pago')->nullable();
			$table->dateTime('fechaPago')->nullable();
			$table->string('fechaSubsidio', 45)->nullable();
			$table->integer('idPuntoEncuentro')->nullable()->index('Inscripcion_ibfk_46_idx');
			$table->integer('idAreadeInteres')->nullable()->index('fk_Inscripcion_1_idx1');
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
