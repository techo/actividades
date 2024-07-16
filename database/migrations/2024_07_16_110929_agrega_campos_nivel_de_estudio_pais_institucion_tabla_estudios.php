<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgregaCamposNivelDeEstudioPaisInstitucionTablaEstudios extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('estudios', function (Blueprint $table) {
            $table->string('nivelDeEstudios')->nullable();
            $table->integer('idPaisInstitucion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('estudios', function (Blueprint $table) {
                $table->dropColumn('nivelDeEstudios');
                $table->dropColumn('idPaisInstitucion');
        });
    }
}
