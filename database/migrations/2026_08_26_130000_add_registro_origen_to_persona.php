<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRegistroOrigenToPersona extends Migration
{
    /**
     * Origen del registro: 'app' | 'web'. Se usa en la verificación de email para
     * decidir si, tras verificar desde el navegador, hay que reabrir la app móvil
     * por deep link (solo para quienes se registraron desde la app). Nullable: las
     * personas ya existentes quedan en null y se tratan como 'web' (sin deep link).
     */
    public function up()
    {
        Schema::table('Persona', function (Blueprint $table) {
            $table->string('registro_origen', 20)->nullable()->after('canal_contacto');
        });
    }

    public function down()
    {
        Schema::table('Persona', function (Blueprint $table) {
            $table->dropColumn('registro_origen');
        });
    }
}
