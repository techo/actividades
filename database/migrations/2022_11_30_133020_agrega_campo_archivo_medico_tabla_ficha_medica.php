<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgregaCampoArchivoMedicoTablaFichaMedica extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ficha_medicas', function (Blueprint $table) {
            $table->string('archivo_medico')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ficha_medicas', function (Blueprint $table) {
                $table->dropColumn('archivo_medico');
        });
    }
}

