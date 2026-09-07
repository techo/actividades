<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Contadores de resultado REAL del envío por comunicación. `destinatarios_count` es
 * cuántos se ENCOLARON; estos dos reflejan el resultado efectivo:
 *  - enviados_ok    → mails que salieron (evento MessageSent).
 *  - enviados_error → mails que fallaron (job fallado).
 * Pendientes (aún en cola / throttle) = destinatarios_count - ok - error.
 */
class AddEnviadosCountersToComunicacionesTable extends Migration
{
    public function up()
    {
        Schema::table('comunicaciones', function (Blueprint $table) {
            $table->unsignedInteger('enviados_ok')->default(0)->after('destinatarios_count');
            $table->unsignedInteger('enviados_error')->default(0)->after('enviados_ok');
        });
    }

    public function down()
    {
        Schema::table('comunicaciones', function (Blueprint $table) {
            $table->dropColumn(['enviados_ok', 'enviados_error']);
        });
    }
}
