<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estado que se le aplicará al reporte cuando se apruebe una nota interna
 * (flujo propuesta → aprobación → respuesta de Techita).
 *
 * Una nota interna con `estado_propuesto` es una respuesta PROPUESTA: al aprobarla
 * ("Aprobar y enviar") se publica, se aplica ese estado y sale un único mail.
 */
class AddEstadoPropuestoToIssueReportReplies extends Migration
{
    public function up()
    {
        Schema::table('issue_report_replies', function (Blueprint $table) {
            $table->string('estado_propuesto', 20)->nullable()->after('is_internal');
        });
    }

    public function down()
    {
        Schema::table('issue_report_replies', function (Blueprint $table) {
            $table->dropColumn('estado_propuesto');
        });
    }
}
