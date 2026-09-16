<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Resolución de la solicitud de beca/exención por parte del coordinador.
 *
 * Complementa los campos de solicitud (scholarship_requested, etc.) con el
 * resultado de la revisión desde el backoffice:
 *  - scholarship_approved         (bool):      la beca fue aprobada.
 *  - scholarship_rejected         (bool):      la beca fue rechazada.
 *  - scholarship_rejection_reason (text):      motivo del rechazo (se envía por mail).
 *  - scholarship_resolved_at      (timestamp): cuándo se resolvió.
 *
 * Al aprobar se materializa la exención en `exento_pago` (misma vía que el
 * socio de Salesforce), así el estado queda CONFIRMADO y el pago satisfecho.
 */
class AddScholarshipResolutionToInscripcion extends Migration
{
    public function up()
    {
        Schema::table('Inscripcion', function (Blueprint $table) {
            $table->boolean('scholarship_approved')->default(false)->after('scholarship_requested_at');
            $table->boolean('scholarship_rejected')->default(false)->after('scholarship_approved');
            $table->text('scholarship_rejection_reason')->nullable()->after('scholarship_rejected');
            $table->timestamp('scholarship_resolved_at')->nullable()->after('scholarship_rejection_reason');
        });
    }

    public function down()
    {
        Schema::table('Inscripcion', function (Blueprint $table) {
            $table->dropColumn([
                'scholarship_approved',
                'scholarship_rejected',
                'scholarship_rejection_reason',
                'scholarship_resolved_at',
            ]);
        });
    }
}
