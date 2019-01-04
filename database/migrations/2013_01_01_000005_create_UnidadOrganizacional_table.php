<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUnidadOrganizacionalTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('UnidadOrganizacional', function(Blueprint $table)
		{
			$table->engine = 'InnoDB';
			$table->charset = 'utf8';
			$table->collation = 'utf8_spanish_ci';
			
			$table->integer('idUnidadOrganizacional', true);
			$table->integer('idUnidadPadre')->nullable()->index('idUnidadPadre');
			$table->string('nombre', 300);
			$table->integer('idPais')->nullable()->index('idPais');
			$table->boolean('heredarPermisos');
			$table->integer('idEquipo')->nullable()->index('UnidadOrganizacional_Equipo');
			$table->string('claveApiActiveCampaign');
			$table->string('direccion');
			$table->integer('idCiudad')->index('IDX_62D8C141ED97AD04');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('UnidadOrganizacional');
	}

}
