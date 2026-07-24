<?php

namespace App\Http\Controllers\backoffice;

use App\Campaign;
use App\Exports\CampanaSuscriptosExport;
use App\Http\Controllers\Controller;
use App\Oficina;
use Maatwebsite\Excel\Facades\Excel;

class CampanasController extends Controller
{
    public function index()
    {
        $datatableConfig = config('datatables.campanas');
        $fields    = json_encode($datatableConfig['fields']);
        $sortOrder = json_encode($datatableConfig['sortOrder']);
        return view('backoffice.campanas.index', compact('fields', 'sortOrder'));
    }

    public function create()
    {
        return view('backoffice.campanas.create');
    }

    public function show($id)
    {
        $campana  = Campaign::findOrFail($id);
        $this->authorize('view', $campana);
        $oficinas = Oficina::where('id_pais', auth()->user()->idPaisPermitido)
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return view('backoffice.campanas.show', compact('campana', 'oficinas'));
    }

    public function preguntas($id)
    {
        $campana = Campaign::findOrFail($id);
        $this->authorize('view', $campana);
        return view('backoffice.campanas.preguntas', compact('campana'));
    }

    public function suscriptos($id)
    {
        $campana = Campaign::findOrFail($id);
        $this->authorize('view', $campana);

        // Fields para el listado configurable (mismo patrón que inscripciones/integrantes).
        $fields = json_encode((new \App\Services\Listados\SuscriptosCatalogo)->defaultFields($id));
        $sortOrder = json_encode(config('datatables.suscriptos.sortOrder'));

        return view('backoffice.campanas.suscriptos', compact('campana', 'fields', 'sortOrder'));
    }

    public function exportar($id)
    {
        $campana = Campaign::with('preguntas')->findOrFail($id);
        $this->authorize('view', $campana);
        $export = new CampanaSuscriptosExport($campana);
        $filename = 'suscriptos_' . str_slug($campana->nombre) . '.xlsx';
        return Excel::download($export, $filename);
    }
}
