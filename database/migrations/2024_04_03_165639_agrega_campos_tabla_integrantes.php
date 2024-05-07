<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgregaCamposTablaIntegrantes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Integrantes', function (Blueprint $table) {
            $table->string('descripcion_rol', 300)->nullable();
            $table->string('meta', 300)->nullable();
            $table->string('hitos', 300)->nullable();
            $table->string('dia_hora_reunion')->nullable();
            $table->string('periodicidad_reunion')->nullable();
            $table->string('impacto', 300)->nullable();
            $table->string('capacidades', 300)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Integrantes', function (Blueprint $table) {
            $table->dropColumn('descripcion_rol');
            $table->dropColumn('meta');
            $table->dropColumn('hitos');
            $table->dropColumn('dia_hora_reunion');
            $table->dropColumn('periodicidad_reunion');
            $table->dropColumn('impacto');
            $table->dropColumn('capacidades');
        });
    }
}
