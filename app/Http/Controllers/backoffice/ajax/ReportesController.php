<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\IssueReport;
use App\IssueReportReply;
use App\Jobs\EnviarMailTransaccionalSes;
use App\Mail\MailReporteRespondido;
use App\Services\Github\GithubIssueService;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Endpoints ajax del sistema de reportes de problemas / sugerencias.
 *
 *  - store / captura: los usa el widget "Reportar un problema" (cualquier usuario del
 *    backoffice). La identidad del reporte SIEMPRE se toma del server (auth), nunca del
 *    cliente, para no confiar en payload manipulable.
 *  - index / update: los usa la bandeja de triage (solo admin; gate en las rutas).
 */
class ReportesController extends Controller
{
    const CAPTURA_DIR = 'bug_reports'; // directorio privado en storage/app

    /**
     * Alta de un reporte. Devuelve el id para que el widget adjunte la captura después.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'type'              => 'required|in:bug,suggestion',
            'description'       => 'required|string|max:5000',
            'severity'          => 'nullable|in:low,medium,high,critical',
            'area'              => 'nullable|string|max:50',
            'platform'          => 'nullable|in:' . implode(',', IssueReport::PLATFORMS),
            'url'               => 'nullable|string|max:2000',
            'route_name'        => 'nullable|string|max:255',
            'os'                => 'nullable|string|max:50',
            'browser'           => 'nullable|string|max:50',
            'screen_resolution' => 'nullable|string|max:30',
            'viewport'          => 'nullable|string|max:30',
            'locale'            => 'nullable|string|max:10',
            'user_agent'        => 'nullable|string|max:1000',
            'console_errors'    => 'nullable|array',
            'context'           => 'nullable|array',
            'sentry_event_id'   => 'nullable|string|max:64',
        ]);

        $report = new IssueReport($data);
        $report->status  = IssueReport::STATUS_NUEVO;
        $report->release = config('sentry.release');

        // Snapshot de identidad desde el server (no del cliente).
        $user = auth()->user();
        if ($user) {
            $report->idPersona      = $user->idPersona;
            $report->reporter_name  = $user->nombreCompleto;
            $report->reporter_email = $user->mail;
            $report->reporter_role  = $user->getRoleNames()->first();
            $report->idPais         = $user->idPais;
        }

        $report->save();

        return response()->json(['id' => $report->id], 201);
    }

    /**
     * Adjunta la captura de pantalla a un reporte recién creado. Best-effort: si falla,
     * el reporte ya quedó guardado igual.
     */
    public function captura(Request $request, $id)
    {
        $request->validate([
            'captura' => 'required|file|mimes:jpg,jpeg,png|max:5120', // 5 MB
        ]);

        $report = IssueReport::findOrFail($id);

        $user = auth()->user();
        abort_unless(
            $user && ((int) $report->idPersona === (int) $user->idPersona || $user->can('ver_reportes')),
            403
        );

        $path = ImageUploadService::store($request->file('captura'), self::CAPTURA_DIR);
        $report->screenshot_path = $path;
        $report->save();

        return response()->json(['ok' => true]);
    }

    /**
     * Listado paginado para la bandeja (formato que consume Vuetable).
     */
    public function index(Request $request)
    {
        $query = IssueReport::query()->with(['asignadoA', 'respuestas']);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('description', 'like', "%{$q}%")
                    ->orWhere('reporter_name', 'like', "%{$q}%")
                    ->orWhere('reporter_email', 'like', "%{$q}%");
            });
        }
        if ($request->filled('status'))   { $query->where('status', $request->status); }
        if ($request->filled('type'))     { $query->where('type', $request->type); }
        if ($request->filled('severity')) { $query->where('severity', $request->severity); }
        if ($request->filled('platform')) { $query->where('platform', $request->platform); }
        if (filter_var($request->input('pendientes'), FILTER_VALIDATE_BOOLEAN)) {
            $query->conRespuestaPendiente();
        }

        // Los que tienen respuesta de quien reportó sin contestar van siempre primero.
        $query->orderByRaw(IssueReport::SQL_RESPUESTA_PENDIENTE . ' DESC');

        $sortable = ['id', 'type', 'status', 'severity', 'area', 'platform', 'reporter_name', 'created_at'];
        if ($request->filled('sort')) {
            $parts = explode('|', $request->sort);
            $field = $parts[0];
            $dir   = (isset($parts[1]) && strtolower($parts[1]) === 'asc') ? 'asc' : 'desc';
            if (in_array($field, $sortable, true)) {
                $query->orderBy($field, $dir);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $perPage = $request->filled('per_page') ? (int) $request->per_page : 25;
        $result  = $query->paginate($perPage);

        $result->getCollection()->transform(function (IssueReport $r) {
            return [
                'id'             => $r->id,
                'type'           => $r->type,
                'status'         => $r->status,
                'severity'       => $r->severity,
                'area'           => $r->area,
                'platform'       => $r->platform,
                'platform_label' => $r->platform ? IssueReport::PLATFORM_LABELS[$r->platform] : '—',
                'description'    => $r->description,
                'resumen'        => Str::limit((string) $r->description, 90),
                'reporter_name'  => $r->reporter_name,
                'reporter_email' => $r->reporter_email,
                'reporter_role'  => $r->reporter_role,
                'url'            => $r->url,
                'route_name'     => $r->route_name,
                'os'             => $r->os,
                'browser'        => $r->browser,
                'screen_resolution' => $r->screen_resolution,
                'viewport'       => $r->viewport,
                'locale'         => $r->locale,
                'release'        => $r->release,
                'user_agent'     => $r->user_agent,
                'console_errors' => $r->console_errors,
                'context'        => $r->context,
                'has_screenshot' => ! empty($r->screenshot_path),
                'screenshot_url' => empty($r->screenshot_path) ? null : '/admin/reportes/' . $r->id . '/captura',
                'sentry_event_id' => $r->sentry_event_id,
                'github_issue_url' => $r->github_issue_url,
                'assigned_to'    => $r->assigned_to,
                'assigned_name'  => optional($r->asignadoA)->nombreCompleto,
                'created_at'     => optional($r->created_at)->format('Y-m-d H:i'),
                'resolved_at'    => optional($r->resolved_at)->format('Y-m-d H:i'),
                // Quien reportó contestó desde "Mis reportes" y falta responderle.
                'respuesta_pendiente' => $r->tieneRespuestaPendiente(),
            ];
        });

        return response()->json($result);
    }

    /**
     * Update parcial "por presencia de campo" (convención del proyecto para updates ajax).
     */
    public function update(Request $request, $id)
    {
        $report = IssueReport::findOrFail($id);

        // Si el reporte pasa a "resuelto" (y no lo estaba), avisamos a quien lo reportó.
        $avisarResuelto = false;

        if ($request->has('status')) {
            $request->validate(['status' => 'in:' . implode(',', IssueReport::STATUSES)]);
            $estadoPrevio  = $report->status;
            $report->cambiarEstado($request->status);

            $avisarResuelto = $request->status === IssueReport::STATUS_RESUELTO
                && $estadoPrevio !== IssueReport::STATUS_RESUELTO
                && ! empty($report->reporter_email);
        }

        if ($request->has('severity')) {
            $request->validate(['severity' => 'nullable|in:low,medium,high,critical']);
            $report->severity = $request->severity ?: null;
        }

        if ($request->has('area')) {
            $report->area = $request->area ?: null;
        }

        if ($request->has('platform')) {
            $request->validate(['platform' => 'nullable|in:' . implode(',', IssueReport::PLATFORMS)]);
            $report->platform = $request->platform ?: null;
        }

        if ($request->has('assigned_to')) {
            $report->assigned_to = $request->assigned_to ?: null;
        }

        $report->save();

        if ($avisarResuelto) {
            $reply = IssueReportReply::create([
                'issue_report_id' => $report->id,
                'idPersona'       => optional(auth()->user())->idPersona,
                'author_name'     => IssueReportReply::AUTOR_TECHITA,
                'tipo'            => IssueReportReply::TIPO_RESUELTO,
                'is_internal'     => false,
            ]);
            $this->notificar($report, $reply);
        }

        return response()->json(['ok' => true, 'notificado' => $avisarResuelto]);
    }

    /**
     * Hilo de respuestas de un reporte (para el modal de la bandeja).
     */
    public function respuestas($id)
    {
        $report = IssueReport::findOrFail($id);

        $hilo = $report->respuestas->map(function (IssueReportReply $r) {
            return $this->presentarRespuesta($r);
        });

        return response()->json([
            'reporter_email' => $report->reporter_email,
            'respuestas'     => $hilo,
        ]);
    }

    /**
     * Agrega una respuesta al hilo. Si `visible` (default true), se le avisa por mail a
     * quien reportó; si no, queda como nota interna de triage. Si `como_techita` (default
     * true), se firma como Techita; el admin que la escribió queda en `idPersona`.
     */
    public function responder(Request $request, $id)
    {
        $data = $request->validate([
            'body'    => 'required|string|max:5000',
            'visible' => 'nullable|boolean',
            'como_techita' => 'nullable|boolean',
            'estado_propuesto' => 'nullable|in:' . implode(',', IssueReport::STATUSES),
        ]);

        $report  = IssueReport::findOrFail($id);
        // Request::boolean() no existe en Laravel 5.7; default true si no viene el campo.
        $visible = filter_var($request->input('visible', true), FILTER_VALIDATE_BOOLEAN);
        $comoTechita = filter_var($request->input('como_techita', true), FILTER_VALIDATE_BOOLEAN);
        $user    = auth()->user();

        $reply = IssueReportReply::create([
            'issue_report_id' => $report->id,
            'idPersona'       => optional($user)->idPersona,
            'author_name'     => $comoTechita ? IssueReportReply::AUTOR_TECHITA : optional($user)->nombreCompleto,
            'tipo'            => IssueReportReply::TIPO_MENSAJE,
            'body'            => $data['body'],
            'is_internal'     => ! $visible,
            // Solo una nota interna es "propuesta": una respuesta visible ya se publicó.
            'estado_propuesto' => $visible ? null : ($data['estado_propuesto'] ?? null),
        ]);

        $notificado = false;
        if ($visible) {
            $notificado = $this->notificar($report, $reply);
        }

        return response()->json([
            'ok'         => true,
            'notificado' => $notificado,
            'reply'      => $this->presentarRespuesta($reply->fresh('autor')),
        ]);
    }

    /**
     * "Aprobar y enviar" una respuesta PROPUESTA (nota interna): la publica, aplica su
     * estado propuesto y avisa a quien reportó con UN solo mail. Si el estado es
     * "resuelto", el mail es el de "Resolvimos tu reporte" con el texto adentro (antes,
     * responder + marcar resuelto mandaba dos mails).
     */
    public function publicar($id, $replyId)
    {
        $report = IssueReport::findOrFail($id);
        $reply  = IssueReportReply::where('issue_report_id', $report->id)->findOrFail($replyId);

        if (! $reply->is_internal) {
            return response()->json(['error' => 'Esta respuesta ya está publicada.'], 422);
        }

        $reply->is_internal = false;
        if ($reply->estado_propuesto === IssueReport::STATUS_RESUELTO) {
            $reply->tipo = IssueReportReply::TIPO_RESUELTO;
        }
        $reply->save();

        if ($reply->estado_propuesto) {
            $report->cambiarEstado($reply->estado_propuesto);
            $report->save();
        }

        $notificado = $this->notificar($report, $reply);

        return response()->json([
            'ok'         => true,
            'notificado' => $notificado,
            'status'     => $report->status,
            'reply'      => $this->presentarRespuesta($reply->fresh('autor')),
        ]);
    }

    private function presentarRespuesta(IssueReportReply $r)
    {
        return [
            'id'               => $r->id,
            'tipo'             => $r->tipo,
            'body'             => $r->body,
            'is_internal'      => (bool) $r->is_internal,
            'estado_propuesto' => $r->estado_propuesto,
            'author_name'      => $r->author_name,
            'escrito_por'      => $r->esDeTechita() ? optional($r->autor)->nombreCompleto : null,
            'del_reportante'   => $r->esDelReportante(),
            'notificado'       => ! empty($r->notified_at),
            'created_at'       => optional($r->created_at)->format('Y-m-d H:i'),
        ];
    }

    /**
     * Dispara el aviso por mail (Amazon SES, transaccional) a quien reportó y sella
     * `notified_at`. Si el reporte no tiene email de contacto, no hace nada.
     */
    private function notificar(IssueReport $report, IssueReportReply $reply)
    {
        if (empty($report->reporter_email)) {
            return false;
        }

        // Helper dispatch() (como EnviarMailBulkSes): el job no usa Dispatchable, así que
        // EnviarMailTransaccionalSes::dispatch() no existía → 500 y el mail nunca salía.
        // notified_at se sella DESPUÉS de encolar: si falla, la respuesta no figura avisada.
        dispatch(new EnviarMailTransaccionalSes(
            new MailReporteRespondido($report, $reply),
            $report->reporter_email
        ));

        $reply->notified_at = now();
        $reply->save();

        return true;
    }

    /**
     * Crea un issue de GitHub a partir del reporte y guarda la URL (Fase 3).
     * Idempotente: si ya tiene issue, devuelve la URL existente.
     */
    public function github($id, GithubIssueService $github)
    {
        $report = IssueReport::findOrFail($id);

        if (! empty($report->github_issue_url)) {
            return response()->json(['github_issue_url' => $report->github_issue_url, 'ya_existia' => true]);
        }

        if (! $github->enabled()) {
            return response()->json(['error' => 'La integración con GitHub no está configurada.'], 422);
        }

        try {
            $url = $github->crearDesdeReporte($report);
        } catch (\RuntimeException $e) {
            return response()->json(['error' => $e->getMessage()], 502);
        }

        $report->github_issue_url = $url;
        // Al pasar a la cola de trabajo, el reporte deja de estar "nuevo".
        if ($report->status === IssueReport::STATUS_NUEVO) {
            $report->status = IssueReport::STATUS_EN_PROGRESO;
        }
        $report->save();

        return response()->json(['github_issue_url' => $url]);
    }
}
