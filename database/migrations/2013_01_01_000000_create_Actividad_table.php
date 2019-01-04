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
			$table->engine = 'InnoDB';
			$table->charset = 'utf8';
			$table->collation = 'utf8_spanish_ci';
			
			$table->integer('idActividad', true);
			$table->integer('idTipo')->index('idTipo');
			$table->dateTime('fechaCreacion');
			$table->dateTime('fechaModificacion');
			$table->dateTime('fechaInicio');
			$table->dateTime('fechaFin');
			$table->string('fechaInicioFinFormato', 20)->nullable()->default('d/m/Y');
			$table->dateTime('fechaInicioInscripciones')->nullable();
			$table->dateTime('fechaFinInscripciones')->nullable();
			$table->string('limiteInscripciones', 20);
			$table->integer('idUnidadOrganizacional')->index('idUnidadOrganizacional');
			$table->string('nombreActividad', 300);
			$table->string('descripcion', 3000)->nullable();
			$table->string('lugar', 300);
			$table->string('casasPlanificadas', 20);
			$table->string('casasConstruidas', 20);
			$table->text('comentarios', 65535);
			$table->string('tipoConstruccion', 300);
			$table->string('estadoConstruccion', 200)->default('Abierta');
			$table->integer('idPrograma')->nullable()->index('idPrograma');
			$table->text('mensajeInscripcion', 65535);
			$table->integer('idEncuestaDinamica');
			$table->integer('numConstruccion')->nullable();
			$table->boolean('pApMat')->default(1);
			$table->boolean('pDNI')->default(1);
			$table->boolean('pFonoMovil')->default(1);
			$table->boolean('pUniversidad')->default(1);
			$table->boolean('pCarrera')->default(1);
			$table->boolean('pAnoEstudio')->default(1);
			$table->boolean('pAcompanante')->default(0);
			$table->boolean('tPortugues')->default(0);
			$table->boolean('enviarMail')->default(1);
			$table->boolean('actividadSecundaria');
			$table->boolean('compromiso')->default(1);
			$table->string('idListaCTCT', 300);
			$table->boolean('mostrarFB');
			$table->string('presupuesto', 300)->nullable();
			$table->string('inscripcion', 300)->nullable();
			$table->boolean('inscripcionInterna');
			$table->integer('idPersonaModificacion')->nullable()->index('fk_Actividad_1_idx');
			$table->integer('idEmpresa')->nullable()->index('Actividad_ibfk_7');
			$table->decimal('costo', 10, 0)->nullable()->default(0);
			$table->string('moneda', 10)->nullable();
			$table->string('estadoDefault', 100)->nullable();
			$table->text('puntosEnvio', 65535)->nullable();
			$table->text('captaciones', 65535)->nullable();
			$table->boolean('pAcompanantePost')->nullable();
			$table->text('mailBeca', 65535)->nullable();
			$table->integer('idFormulario')->nullable()->index('Actividad_ibfk_8_idx');
			$table->dateTime('fechaInicioEvaluaciones')->nullable();
			$table->dateTime('fechaFinEvaluaciones')->nullable();
			$table->integer('idAsentamiento')->nullable()->index('Actividad_ibfk_9_idx');
			$table->integer('idZona')->nullable()->index('fk_Actividad_1_idx1');
			$table->string('linkFormularioEvaluacion', 300)->nullable();
			$table->integer('statusMailSeguimiento')->nullable();
			$table->text('mailSeguimiento', 65535)->nullable();
			$table->boolean('destacada')->nullable();
			$table->boolean('EnviarMailPago')->nullable();
			$table->text('MailPago', 65535)->nullable();
			$table->string('CostoConMoneda', 5)->nullable();
			$table->string('LinkPago', 1000)->nullable();
			$table->text('PuntosEnvioUL', 65535)->nullable();
			$table->text('CaptacionesUL', 65535)->nullable();
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
