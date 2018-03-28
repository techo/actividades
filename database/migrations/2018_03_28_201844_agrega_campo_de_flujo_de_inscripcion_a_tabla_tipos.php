<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgregaCampoDeFlujoDeInscripcionATablaTipos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('Tipo', 'flujo')) {
            //
            Schema::table('Tipo', function (Blueprint $table) {
                $table->enum('flujo',['CONSTRUCCION', 'GENERICO'])->default('GENERICO');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('Tipo', function (Blueprint $table) {
            $table->dropColumn('flujo');
        });
    }
}
