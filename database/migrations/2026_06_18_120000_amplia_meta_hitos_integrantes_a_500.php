<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Amplía meta/hitos de VARCHAR(300) a VARCHAR(500).
 *
 * Con MySQL en modo estricto (config/database.php 'strict' => true), pegar un
 * texto de más de 300 caracteres provocaba un "Data too long for column" (500)
 * sin feedback claro en el modal de integrantes. Subimos el límite a 500 y lo
 * acompañamos con max:500 en la validación y maxlength en el front.
 */
class AmpliaMetaHitosIntegrantesA500 extends Migration
{
    public function up()
    {
        Schema::table('Integrantes', function (Blueprint $table) {
            $table->string('meta', 500)->nullable()->change();
            $table->string('hitos', 500)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('Integrantes', function (Blueprint $table) {
            $table->string('meta', 300)->nullable()->change();
            $table->string('hitos', 300)->nullable()->change();
        });
    }
}
