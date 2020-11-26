<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgregaCampoLocaleATablaPais extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table('atl_pais', function (Blueprint $table) {
            $table->string('locale')->default('es_AR');
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
            $table->dropColumn('locale');
        });
    }
}
