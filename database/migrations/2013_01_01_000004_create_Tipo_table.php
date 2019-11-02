<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTipoTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('Tipo', function(Blueprint $table)
		{
			$table->integer('idTipo', true);

			$table->string('nombre', 300);

			$table->integer('idCategoria')->unsigned()->nullable();

			/*$table->boolean('hs');
			$table->boolean('fyv');
			$table->string('alias', 10)->nullable();*/
			
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('Tipo');
	}

}
