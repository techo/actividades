<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Exención de pago por ser socio/donante (verificado contra Salesforce).
 *
 * Cuando un voluntario de Argentina (país 13) que es socio de TECHO se inscribe
 * a una actividad paga, el backend materializa acá la exención para que el flujo
 * de pago quede satisfecho server-side (no depende de que el cliente la respete).
 *
 *  - `exento_pago`   (bool):      la inscripción no requiere pago.
 *  - `exento_motivo` (string):    por qué (hoy siempre 'socio_salesforce').
 *  - `exento_at`     (timestamp): cuándo se determinó la exención.
 *
 * EstadoInscripcion::resolve() trata `exento_pago` como pago satisfecho, así que
 * el estado resultante es CONFIRMADO (o ESPERAR CONFIRMACIÓN si además la
 * actividad requiere confirmación del coordinador).
 */
class AddExentoPagoToInscripcion extends Migration
{
    public function up()
    {
        Schema::table('Inscripcion', function (Blueprint $table) {
            $table->boolean('exento_pago')->default(false)->after('pago');
            $table->string('exento_motivo', 50)->nullable()->after('exento_pago');
            $table->timestamp('exento_at')->nullable()->after('exento_motivo');
        });
    }

    public function down()
    {
        Schema::table('Inscripcion', function (Blueprint $table) {
            $table->dropColumn(['exento_pago', 'exento_motivo', 'exento_at']);
        });
    }
}
