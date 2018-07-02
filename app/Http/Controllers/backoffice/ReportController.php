<?php

namespace App\Http\Controllers\backoffice;

use App\Actividad;
use App\Exports\ActividadesExport;
use App\Exports\EvaluacionesActividadExport;
use App\Exports\InscripcionesExport;
use App\Exports\MisActividadesExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EvaluacionesPersonasExport;

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
        $filtros['idActividad'] = $id;

        if(!empty($request->filter)){
            $filtros['HotFilter'] = $request->filter;
            unset($filtros['filter']);
        }

        if(!empty($request->condiciones))
        {
            $condiciones = json_decode($request->condiciones, true);
            foreach ($condiciones as $condicion)
            {
                //$condicion = json_decode($condicion, true);
                $filtros[$condicion['campo']] = [
                    'condicion' => $condicion['condicion'],
                    'valor' => $condicion['valor']
                ];
            }
        }
        $inscripciones = (new InscripcionesExport($filtros));
        return Excel::download($inscripciones, 'inscripciones_' . $id . '.xlsx');
    }

    public function exportarEvaluacionesPersonas($id)
    {
        $actividad = Actividad::find($id);
        $evaluaciones = new EvaluacionesPersonasExport($actividad);

        return Excel::download($evaluaciones,'Evaluaciones de Participantes en '. $actividad->nombreActividad . '.xlsx');
    }

    public function exportarEvaluacionesActividad($id)
    {
        $actividad = Actividad::find($id);
        $evaluaciones = new EvaluacionesActividadExport($actividad);

        return Excel::download($evaluaciones,'Evaluaciones de '. $actividad->nombreActividad . '.xlsx');
    }

}
