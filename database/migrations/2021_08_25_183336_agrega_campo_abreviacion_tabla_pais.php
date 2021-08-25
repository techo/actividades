<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgregaCampoAbreviacionTablaPais extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('atl_pais', function (Blueprint $table) {
            $table->string('abreviacion')->default('');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('atl_pais', function (Blueprint $table) {
            $table->dropColumn('abreviacion');
        });
    }
}
