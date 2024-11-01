<?php

namespace App\Http\Controllers\backoffice;

use App\Actividad;
use App\Exports\ActividadesExport;
use App\Exports\SuscriptosExport;
use App\Exports\EvaluacionesActividadExport;
use App\Exports\EvaluacionesPersonasExport;
use App\Exports\EvaluacionesUsuarioExport;
use App\Exports\EvaluacionesGeneralesExport;
use App\Exports\EvaluadoresGeneralesExport;
use App\Exports\InscripcionesUsuarioExport;
use App\Exports\InscripcionesExport;
use App\Exports\InscripcionesGeneralExport;
use App\Exports\MisActividadesExport;
use App\Exports\PersonasInscriptasExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Session\Session as SessionSession;

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
        $actividad = Actividad::find($id);
        if ($actividad->idPais !== auth()->user()->idPaisPermitido){
            Session::flash('error', 'No tiene permisos para ver ese perfil.');
            return redirect()->back();
        }
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

    public function exportarPersonasInscriptas(Request $request)
    { 
        try {
            Log::info('Iniciando la exportación de Excel');

            $inscripciones = new PersonasInscriptasExport($request);
            Log::info('ya tnego las inscripciones');
            Log::info($inscripciones);
            return Excel::download($inscripciones, 'personasInscriptas.xlsx');

            Log::info('Exportación completada exitosamente');
        } catch (\Exception $e) {
            Log::error('Error en la exportación de Excel: ' . $e->getMessage());
            return response()->json(['error' => 'Error en la exportación'], 500);
        }
    }

    public function exportarEvaluacionesGenerales(Request $request)
    {
        $evaluaciones = new EvaluacionesGeneralesExport($request);
        return Excel::download($evaluaciones, 'evaluaciones.xlsx');
    }
    
    public function exportarEvaluadoresGenerales(Request $request)
    {
        $evaluadores = new EvaluadoresGeneralesExport($request);
        return Excel::download($evaluadores, 'evaluadores.xlsx');
    }

    public function exportarEvaluacionesPersonas($id)
    {
        $actividad = Actividad::find($id);
        if ($actividad->idPais !== auth()->user()->idPaisPermitido){
            Session::flash('error', 'No tiene permisos.');
            return redirect()->back();
        }
        $evaluaciones = new EvaluacionesPersonasExport($actividad);

        //Si el nombre de la actividad tiene alguno de estos caracteres, puede potencialmente romper la exportación
        $nombreActividad = str_replace(str_split('\\/:*?"<>|'), ' ', $actividad->nombreActividad);
        return Excel::download($evaluaciones,'Evaluaciones de Participantes en ' . $nombreActividad . '.xlsx');
    }

    public function exportarEvaluacionesActividad($id)
    {
        $actividad = Actividad::find($id);
        if ($actividad->idPais !== auth()->user()->idPaisPermitido){
            Session::flash('error', 'No tiene permisos.');
            return redirect()->back();
        }
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
    public function exportarSuscriptos(Request $request)
    {
        $suscriptos = (new SuscriptosExport($request->filter));
        return Excel::download($suscriptos, 'suscriptas.xlsx');
    }

}
