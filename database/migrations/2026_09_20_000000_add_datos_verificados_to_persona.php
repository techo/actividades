<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Verificación de datos de la persona (calidad de datos progresiva).
 *
 * Guarda CUÁNDO la persona confirmó por última vez que sus datos están bien y,
 * opcionalmente, QUÉ campos confirmó. Es el insumo para no volver a molestar a
 * quien ya validó hace poco (ver App\Services\CalidadDatos\CalidadDatosPersona)
 * y para la futura microvalidación en el flujo de inscripción.
 *
 *  - datos_verificados_at (timestamp): última confirmación explícita del usuario.
 *  - datos_verificados     (json):     detalle por campo, ej.
 *      {"documento":"2026-08-01","fechaNacimiento":"2026-08-01"} — permite que
 *      alguien confirme el nombre pero deje el documento pendiente.
 *
 * Ambas nullable: el estado por defecto ("nunca verificado") es null.
 */
class AddDatosVerificadosToPersona extends Migration
{
    public function up()
    {
        Schema::table('Persona', function (Blueprint $table) {
            $table->timestamp('datos_verificados_at')->nullable()->after('ultimo_acceso_app');
            $table->json('datos_verificados')->nullable()->after('datos_verificados_at');
        });
    }

    public function down()
    {
        Schema::table('Persona', function (Blueprint $table) {
            $table->dropColumn(['datos_verificados_at', 'datos_verificados']);
        });
    }
}
