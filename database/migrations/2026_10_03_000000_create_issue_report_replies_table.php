<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hilo de respuestas de un reporte (bandeja de triage del widget "Reportar un problema").
 *
 * Cada fila es un mensaje del hilo. Dos usos conviven en la misma tabla:
 *   - `is_internal = true`  → nota interna de triage, NO se muestra ni se notifica a quien reportó.
 *   - `is_internal = false` → respuesta visible para quien reportó; si se decide avisar,
 *                             se dispara un mail y se sella `notified_at`.
 *
 * `tipo` distingue un mensaje escrito a mano (`mensaje`) de un evento del sistema
 * (`resuelto`: se generó solo al marcar el reporte como resuelto). Extensible sin migración.
 *
 * Igual que `issue_reports`, convención de tabla nueva: snake_case, PK `id`, sin FK a la
 * tabla legacy `Persona`; se guarda `author_name` como snapshot para que el hilo siga siendo
 * legible aunque el autor cambie de datos o se borre.
 */
class CreateIssueReportRepliesTable extends Migration
{
    public function up()
    {
        Schema::create('issue_report_replies', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('issue_report_id');

            // Autor (snapshot; null = sistema)
            $table->integer('idPersona')->nullable();
            $table->string('author_name')->nullable();

            $table->string('tipo', 20)->default('mensaje');   // mensaje | resuelto
            $table->text('body')->nullable();
            $table->boolean('is_internal')->default(false);
            $table->timestamp('notified_at')->nullable();      // cuándo se avisó por mail (si se avisó)

            $table->timestamps();

            $table->index('issue_report_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('issue_report_replies');
    }
}
