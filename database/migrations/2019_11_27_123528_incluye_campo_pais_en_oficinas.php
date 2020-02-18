<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCampoPaisEnOficinas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('atl_oficinas', function (Blueprint $table) {
            $table->integer('id_pais')->unsigned()->nullable();
        });

        Schema::table('atl_oficinas', function (Blueprint $table){
            $table->integer('id_pais')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('atl_oficinas', function (Blueprint $table) {
                $table->dropColumn('id_pais');
        });
    }
}
