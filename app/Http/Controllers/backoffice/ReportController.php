<?php

namespace App\Http\Controllers\backoffice;

use App\Actividad;
use App\Exports\ActividadesExport;
use App\Exports\InscripcionesExport;
use App\Exports\MisActividadesExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function exportarActividades(Request $request)
    {
        $actividades = (new ActividadesExport($request->filter));
        return Excel::download($actividades, 'actividades.xlsx');
    }

    public function exportarMisActividades(Request $request)
    {
        $actividades = (new MisActividadesExport($request->filter));
        return Excel::download($actividades, 'mis-actividades.xlsx');
    }

    public function exportarInscripciones($id, Request $request)
    {
        $inscripciones = (new InscripcionesExport($id, $request->filter));
        return Excel::download($inscripciones, 'inscripciones_' . $id . '.xlsx');
    }
}
