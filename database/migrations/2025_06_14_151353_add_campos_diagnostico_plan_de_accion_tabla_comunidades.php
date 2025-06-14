<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCamposDiagnosticoPlanDeAccionTablaComunidades extends Migration
{
     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Comunidad', function (Blueprint $table) {
            $table->string('diagnostico', 3000)->nullable();
            $table->string('plan_de_accion', 3000)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Comunidad', function (Blueprint $table) {
                $table->dropColumn('diagnostico');
                $table->dropColumn('plan_de_accion');
        });
    }
}
