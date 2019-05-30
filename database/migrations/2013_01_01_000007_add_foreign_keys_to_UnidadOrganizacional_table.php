<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToUnidadOrganizacionalTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('UnidadOrganizacional', function(Blueprint $table)
		{
			$table->foreign('idUnidadPadre', 'UnidadOrganizacional_ibfk_2')->references('idUnidadOrganizacional')->on('UnidadOrganizacional')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('UnidadOrganizacional', function(Blueprint $table)
		{
			$table->dropForeign('UnidadOrganizacional_ibfk_2');
		});
	}

}
