<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToActividadTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('Actividad', function(Blueprint $table)
		{
			$table->foreign('idTipo', 'Actividad_ibfk_3')->references('idTipo')->on('Tipo')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('Actividad', function(Blueprint $table)
		{
			$table->dropForeign('Actividad_ibfk_3');
		});
	}

}
