<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use App\IssueReport;
use App\IssueReportReply;
use Illuminate\Http\Request;

/**
 * "Mis reportes": la vista de QUIEN REPORTÓ (no la bandeja de triage).
 *
 * El mail de respuesta sale de una casilla noreply, así que la persona no puede contestarlo:
 * el mail linkea acá, donde ve su reporte, la conversación visible (nunca las notas
 * internas) y puede responder. Cada usuario ve SOLO sus propios reportes.
 */
class MisReportesController extends Controller
{
    public function index()
    {
        $reportes = IssueReport::where('idPersona', auth()->user()->idPersona)
            ->with('respuestas')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('backoffice.mis-reportes.index', compact('reportes'));
    }

    public function show($id)
    {
        $reporte = $this->reportePropio($id);
        $hilo = $reporte->respuestas->where('is_internal', false)->values();

        return view('backoffice.mis-reportes.show', compact('reporte', 'hilo'));
    }

    public function responder(Request $request, $id)
    {
        $reporte = $this->reportePropio($id);
        $data = $request->validate(['body' => 'required|string|max:5000']);
        $user = auth()->user();

        IssueReportReply::create([
            'issue_report_id' => $reporte->id,
            'idPersona'       => $user->idPersona,
            'author_name'     => $user->nombreCompleto,
            'tipo'            => IssueReportReply::TIPO_REPORTANTE,
            'body'            => $data['body'],
            'is_internal'     => false,
        ]);

        // Si ya estaba cerrado y la persona vuelve a escribir, se reabre para que el equipo
        // lo vea en la bandeja (que además lo marca como "respondió").
        if ($reporte->estaCerrado()) {
            $reporte->cambiarEstado(IssueReport::STATUS_TRIAGE);
            $reporte->save();
        }

        return redirect('/admin/mis-reportes/' . $reporte->id)
            ->with('success', __('backend.my_reports_reply_sent'));
    }

    private function reportePropio($id)
    {
        return IssueReport::where('idPersona', auth()->user()->idPersona)->findOrFail($id);
    }
}
