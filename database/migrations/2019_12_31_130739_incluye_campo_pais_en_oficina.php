<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCampoPaisEnOficina extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('atl_oficinas', 'id_pais')) {
            Schema::table('atl_oficinas', function (Blueprint $table) {
                $table->integer('id_pais')->unsigned()->nullable();
            });
        };
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('atl_oficinas', 'id_pais')) {
            Schema::table('atl_oficinas', function (Blueprint $table) {
                $table->dropColumn('id_pais');
            });
        };
    }
}
