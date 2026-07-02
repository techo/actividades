<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Rediseño de la sección "Confirmación y Pago" de la actividad.
 *
 * Agrega dos campos a Actividad:
 *
 *  - `metodos_pago` (JSON): qué métodos de pago habilitó el coordinador para
 *    la actividad. Estructura esperada:
 *      { "transferencia": bool, "link_pix": bool, "tarjeta": bool }
 *    Hasta ahora la app decidía qué mostrar de forma implícita (si había
 *    `descripcionPago`, `linkPago`, o Stripe configurado en el país). Este
 *    campo lo vuelve explícito. Nullable: las actividades existentes conservan
 *    el comportamiento implícito hasta que se edite la actividad.
 *
 *  - `permite_exencion` (bool): si está en 1, la app muestra "Solicitar exención
 *    o beca" para que el voluntario justifique por texto/adjunto por qué no puede
 *    pagar. El almacenamiento de esa justificación ya existe a nivel Inscripcion
 *    (scholarship_reason / scholarship_evidence_url, migración 2026_05_16).
 *    Es distinto de `Actividad.beca`, que es sólo una URL a un formulario externo.
 */
class AddMetodosPagoPermiteExencionToActividad extends Migration
{
    public function up()
    {
        Schema::table('Actividad', function (Blueprint $table) {
            $table->json('metodos_pago')->nullable()->after('linkPago');
            $table->boolean('permite_exencion')->default(false)->after('metodos_pago');
        });
    }

    public function down()
    {
        Schema::table('Actividad', function (Blueprint $table) {
            $table->dropColumn(['metodos_pago', 'permite_exencion']);
        });
    }
}
