<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IncluyeCampoRolesAplicadosTablaInscripcion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Inscripcion', function (Blueprint $table) {
            $table->json('roles_aplicados')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Inscripcion', function (Blueprint $table) {
            $table->dropColumn('roles_aplicados');
        });
    }
}