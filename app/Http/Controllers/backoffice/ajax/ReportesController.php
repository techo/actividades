<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Http\Controllers\Controller;
use App\IssueReport;
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
        $query = IssueReport::query()->with('asignadoA');

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

        $sortable = ['id', 'type', 'status', 'severity', 'area', 'reporter_name', 'created_at'];
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

        if ($request->has('status')) {
            $request->validate(['status' => 'in:' . implode(',', IssueReport::STATUSES)]);
            $report->status = $request->status;
            $terminales = [IssueReport::STATUS_RESUELTO, IssueReport::STATUS_DESCARTADO];
            $report->resolved_at = in_array($request->status, $terminales, true)
                ? ($report->resolved_at ?: now())
                : null;
        }

        if ($request->has('severity')) {
            $request->validate(['severity' => 'nullable|in:low,medium,high,critical']);
            $report->severity = $request->severity ?: null;
        }

        if ($request->has('area')) {
            $report->area = $request->area ?: null;
        }

        if ($request->has('assigned_to')) {
            $report->assigned_to = $request->assigned_to ?: null;
        }

        $report->save();

        return response()->json(['ok' => true]);
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
