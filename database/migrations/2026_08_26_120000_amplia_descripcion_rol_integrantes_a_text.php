<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AmpliaDescripcionRolIntegrantesAText extends Migration
{
    /**
     * descripcion_rol era VARCHAR(300) y las descripciones reales lo excedían
     * ("SQLSTATE[22001] Data too long for column 'descripcion_rol'" al guardar
     * integrantes). Es texto libre y largo → pasa a TEXT.
     */
    public function up()
    {
        Schema::table('Integrantes', function (Blueprint $table) {
            $table->text('descripcion_rol')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('Integrantes', function (Blueprint $table) {
            $table->string('descripcion_rol', 300)->nullable()->change();
        });
    }
}
