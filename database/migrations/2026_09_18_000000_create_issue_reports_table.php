<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reportes de problemas / sugerencias enviados desde el widget "Reportar un problema"
 * del backoffice (Fase 1 y 2).
 *
 * Diseño pensado "agent-ready" desde el día uno: además del texto libre que escribe la
 * persona, guardamos contexto estructurado y reproducible (URL, ruta, rol, país, errores
 * JS del navegador, release/commit) para que más adelante (Fase 3) un agente o una
 * automatización pueda levantar el reporte, reconstruir "qué estaba haciendo el usuario"
 * y abrir un issue de trabajo. Por eso las columnas JSON (`console_errors`, `breadcrumbs`,
 * `context`) y las de puente (`sentry_event_id`, `github_issue_url`) ya existen aunque no
 * se llenen todas en estas fases.
 *
 * Convención de tabla nueva: snake_case en inglés, PK `id`. Sin FK a la tabla legacy
 * `Persona` (idPersona) a propósito, para no acoplar tipos ni engines; se guarda además un
 * snapshot desnormalizado (nombre/mail/rol/país) porque un reporte es un hecho histórico:
 * debe seguir siendo legible aunque la persona luego cambie de datos o se borre.
 */
class CreateIssueReportsTable extends Migration
{
    public function up()
    {
        Schema::create('issue_reports', function (Blueprint $table) {
            $table->increments('id');

            // Clasificación
            $table->string('type', 20)->default('bug');          // bug | suggestion
            $table->string('status', 20)->default('nuevo');       // nuevo | triage | en_progreso | resuelto | descartado
            $table->string('severity', 20)->nullable();           // low | medium | high | critical (Fase 2, triage)
            $table->string('area', 50)->nullable();               // módulo afectado: inscripcion, pagos, listados, ... (Fase 2)

            // Contenido que escribe la persona
            $table->text('description');

            // Quién reporta (snapshot desnormalizado; ver nota de cabecera)
            $table->integer('idPersona')->nullable();
            $table->string('reporter_name')->nullable();
            $table->string('reporter_email')->nullable();
            $table->string('reporter_role', 50)->nullable();
            $table->integer('idPais')->nullable();

            // Contexto reproducible del navegador / app
            $table->text('url')->nullable();
            $table->string('route_name')->nullable();             // ruta Laravel o vista Vue actual
            $table->string('os', 50)->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('screen_resolution', 30)->nullable();
            $table->string('viewport', 30)->nullable();
            $table->string('locale', 10)->nullable();
            $table->string('release', 40)->nullable();            // commit/sha del deploy (config('sentry.release'))
            $table->text('user_agent')->nullable();

            // Datos estructurados para automatización (Fase 3)
            $table->json('console_errors')->nullable();           // buffer de errores JS recientes
            $table->json('breadcrumbs')->nullable();              // navegación/acciones recientes (futuro)
            $table->json('context')->nullable();                  // cajón extensible sin migración

            // Adjuntos y puentes externos
            $table->string('screenshot_path')->nullable();        // storage privado
            $table->string('sentry_event_id', 64)->nullable();    // correlación con Sentry si existe
            $table->string('github_issue_url')->nullable();       // round-trip de la cola de trabajo (Fase 3)

            // Triage
            $table->integer('assigned_to')->nullable();           // idPersona del responsable
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('type');
            $table->index('idPersona');
            $table->index('idPais');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('issue_reports');
    }
}
