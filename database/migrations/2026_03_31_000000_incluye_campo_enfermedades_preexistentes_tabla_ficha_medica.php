<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IncluyeCampoEnfermedadesPreexistentesTablaFichaMedica extends Migration
{
    public function up()
    {
        Schema::table('ficha_medicas', function (Blueprint $table) {
            $table->text('enfermedades_preexistentes')->nullable()->after('alimentacion');
        });
    }

    public function down()
    {
        Schema::table('ficha_medicas', function (Blueprint $table) {
            $table->dropColumn('enfermedades_preexistentes');
        });
    }
}
