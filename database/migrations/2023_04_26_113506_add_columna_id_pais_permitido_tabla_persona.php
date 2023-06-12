<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnaIdPaisPermitidoTablaPersona extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('Persona', function (Blueprint $table) {
            $table->integer('idPaisPermitido')->index('idPaisPermitido')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Persona', function (Blueprint $table) {
            $table->dropColumn('idPaisPermitido');
        });
    }
}
