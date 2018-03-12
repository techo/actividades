<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class IncluyeTablaAtlLocalidades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('atl_localidades')) {
            //
            Schema::create('atl_localidades', function (Blueprint $table) {
                $table->increments('id');
                $table->integer('id_provincia')->unsigned();
                $table->string('localidad');
            });

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('atl_localidades');
    }
}
