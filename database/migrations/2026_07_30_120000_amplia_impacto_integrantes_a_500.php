<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Amplía impacto de VARCHAR(300) a VARCHAR(500).
 *
 * Mismo caso que meta/hitos (ver AmpliaMetaHitosIntegrantesA500): con MySQL en
 * modo estricto, un texto de más de 300 caracteres tiraba "Data too long for
 * column 'impacto'" (500) al guardar un integrante. Subimos el límite a 500
 * para alinearlo con meta/hitos.
 */
class AmpliaImpactoIntegrantesA500 extends Migration
{
    public function up()
    {
        Schema::table('Integrantes', function (Blueprint $table) {
            $table->string('impacto', 500)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('Integrantes', function (Blueprint $table) {
            $table->string('impacto', 300)->nullable()->change();
        });
    }
}
