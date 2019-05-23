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
			$table->engine = 'InnoDB';
			$table->charset = 'utf8';
			$table->collation = 'utf8_spanish_ci';
			
			$table->integer('idTipo', true);
			$table->string('nombre', 300);
			$table->boolean('hs');
			$table->boolean('fyv');
			$table->string('alias', 10)->nullable();

			$table->integer('idCategoria')->unsigned()->nullable();
			
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
