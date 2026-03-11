<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCamposTraduccionTablaTipo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Tipo', function (Blueprint $table) {
            $table->string('nombre_pt')->nullable();
            $table->string('nombre_en')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Tipo', function (Blueprint $table) {
            $table->dropColumn('nombre_pt');
            $table->dropColumn('nombre_en');
        });
    }
}
