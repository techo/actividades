<?php

namespace App\Http\Controllers\backoffice;

use App\Actividad;
use App\Exports\ActividadesExport;
use App\Exports\EvaluacionesActividadExport;
use App\Exports\EvaluacionesPersonasExport;
use App\Exports\EvaluacionesUsuarioExport;
use App\Exports\InscripcionesUsuarioExport;
use App\Exports\InscripcionesExport;
use App\Exports\InscripcionesGeneralExport;
use App\Exports\MisActividadesExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

    public function exportarInscripcionesActividad($id, Request $request)
    {
        $filtros['idActividad'] = $id;

        if($request->filter){
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
        return Excel::download($inscripciones, 'inscripciones_actividad_' . $id . '.xlsx');
    }

    public function exportarInscripciones(Request $request)
    {
        $inscripciones = new InscripcionesGeneralExport($request);
        return Excel::download($inscripciones, 'inscripciones.xlsx');
    }

    public function exportarEvaluacionesPersonas($id)
    {
        $actividad = Actividad::find($id);
        $evaluaciones = new EvaluacionesPersonasExport($actividad);

        //Si el nombre de la actividad tiene alguno de estos caracteres, puede potencialmente romper la exportación
        $nombreActividad = str_replace(str_split('\\/:*?"<>|'), ' ', $actividad->nombreActividad);
        return Excel::download($evaluaciones,'Evaluaciones de Participantes en ' . $nombreActividad . '.xlsx');
    }

    public function exportarEvaluacionesActividad($id)
    {
        $actividad = Actividad::find($id);
        $evaluaciones = new EvaluacionesActividadExport($actividad);

        //Si el nombre de la actividad tiene alguno de estos caracteres, puede potencialmente romper la exportación
        $nombreActividad = str_replace(str_split('\\/:*?"<>|'), ' ', $actividad->nombreActividad);
        return Excel::download($evaluaciones,'Evaluaciones de '. $nombreActividad . '.xlsx');
    }

    public function exportarEvaluacionesUsuario($id)
    {
        $evaluaciones = new EvaluacionesUsuarioExport($id);
        return Excel::download($evaluaciones,'Evaluaciones de ' . $id . '.xlsx');
    }

    public function exportarInscripcionesUsuario($id)
    {
        $inscripciones = new InscripcionesUsuarioExport($id);
        return Excel::download($inscripciones,'Inscripciones de ' . $id . '.xlsx');
    }

}
