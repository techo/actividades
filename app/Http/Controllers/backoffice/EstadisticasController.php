<?php

namespace App\Http\Controllers\backoffice;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstadisticasController extends Controller
{

    public function index()
    {
        $estadisticas['inscripciones_ciclo'] = 
        \App\Inscripcion::whereYear('created_at', Carbon::now()->format('Y')) 
            ->where('presente', 1)
            ->count();

        return view('backoffice.estadisticas.index', $estadisticas);
    }

    public function grafico_inscripciones(Request $request)
    {
        $año = ($request->filled('año'))?$request->año:Carbon::now()->format('Y');
        $pais = ($request->filled('pais'))?$request->pais:null;
        $oficina = ($request->filled('oficina'))?$request->oficina:null;


        $consulta = \App\Actividad::join('Inscripcion', 'Actividad.idActividad', '=', 'Inscripcion.idActividad')
            ->select(DB::raw('MONTH(created_at) mes, count(*) as inscriptos, sum(if(presente = 1, 1, 0)) as presentes'))
            ->whereYear('created_at', $año) 
            ->groupBy('mes');

        if($pais) $consulta->where('Actividad.idPais', $pais);
        if($oficina) $consulta->where('Actividad.idOficina', $oficina);
        
        $inscripciones = $consulta->get();

        $respuesta = [];
        foreach ($inscripciones as $i) {
            $respuesta['meses'][] = $i->mes;
            $respuesta['inscriptos'][] = $i->inscriptos;
            $respuesta['presentes'][] = $i->presentes;
        }

        return $respuesta;
    }

    public function grafico_actividades(Request $request)
    {
        $año = ($request->filled('año'))?$request->año:Carbon::now()->format('Y');
        $pais = ($request->filled('pais'))?$request->pais:null;
        $oficina = ($request->filled('oficina'))?$request->oficina:null;

        $consulta = \App\Actividad::join('Tipo', 'Tipo.idTipo', '=', 'Actividad.idTipo')
            ->join('atl_CategoriaActividad', 'atl_CategoriaActividad.id', '=', 'Tipo.idCategoria')
            ->select(DB::raw('MONTH(fechaCreacion) mes, atl_CategoriaActividad.nombre, color, count(*) cantidad'))
            ->whereYear('fechaCreacion', $año) 
            ->groupBy('mes', 'atl_CategoriaActividad.color', 'atl_CategoriaActividad.nombre');

        if($pais) $consulta->where('Actividad.idPais', $pais);
        if($oficina) $consulta->where('Actividad.idOficina', $oficina);
        
        $actividades = $consulta->get();

        $respuesta = [];
        foreach ($actividades as $m) {
            $respuesta[$m->nombre]['data'][] = [ "x" => $m->mes, "y" => $m->cantidad];
            $respuesta[$m->nombre]['color'][] = $m->color;
            $respuesta[$m->nombre]['labels'][] = $m->mes;
        }

        return $respuesta;
    }

    public function actividades()
    {
        $estadisticas['top_actividades_mas_convocantes'] = \App\Actividad::join('Inscripcion', 'Actividad.idActividad', '=', 'Inscripcion.idActividad')
            ->select(DB::raw('nombreActividad, count(*) as inscripciones, sum(if(presente=1,1,0)) as presentes'))
            ->whereYear('created_at', Carbon::now()->format('Y')) 
            ->groupBy('Actividad.idActividad', 'Actividad.nombreActividad') 
            ->orderByRaw('count(*) desc') 
            ->limit(10)
            ->get();

        $estadisticas['top_actividades_con_mejores_evaluaciones'] = \App\Actividad::join('EvaluacionActividad', 'Actividad.idActividad', '=', 'EvaluacionActividad.idActividad')
            ->select(DB::raw('nombreActividad, avg(puntaje) as puntaje, count(puntaje) as cantidad'))
            ->whereYear('created_at', Carbon::now()->format('Y')) 
            ->groupBy('Actividad.idActividad', 'Actividad.nombreActividad') 
            ->orderByRaw('avg(puntaje) desc') 
            ->limit(10)
            ->get();

        return view('backoffice.estadisticas.actividades', $estadisticas);
    }

    public function personas()
    {
        $estadisticas['top_personas_con_peores_evaluaciones_sociales'] = \App\Persona::join('EvaluacionPersona', 'Persona.idPersona', '=', 'EvaluacionPersona.idEvaluado')
            ->join('Actividad', 'Actividad.idActividad', '=', 'EvaluacionPersona.idActividad')
            ->select(DB::raw('Persona.nombres, Persona.apellidoPaterno, avg(puntajeSocial) as puntaje, count(puntajeSocial) as cantidad'))
            ->whereYear('EvaluacionPersona.created_at', Carbon::now()->format('Y')) 
            ->whereNotNull('EvaluacionPersona.puntajeSocial')
            ->where('Actividad.idOficina', '=', 6)
            ->groupBy('Persona.idPersona', 'Persona.nombres', 'Persona.apellidoPaterno') 
            ->orderByRaw('avg(puntajeSocial) asc, count(puntajeSocial) desc') 
            ->limit(10)
            ->get();

        $estadisticas['top_personas_con_peores_evaluaciones_tecnicas'] = \App\Persona::join('EvaluacionPersona', 'Persona.idPersona', '=', 'EvaluacionPersona.idEvaluado')
            ->join('Actividad', 'Actividad.idActividad', '=', 'EvaluacionPersona.idActividad')
            ->select(DB::raw('Persona.nombres, Persona.apellidoPaterno, avg(puntajeTecnico) as puntaje, count(puntajeTecnico) as cantidad'))
            ->whereYear('EvaluacionPersona.created_at', Carbon::now()->format('Y')) 
            ->whereNotNull('EvaluacionPersona.puntajeTecnico')
            ->where('Actividad.idOficina', '=', 6)
            ->groupBy('Persona.idPersona', 'Persona.nombres', 'Persona.apellidoPaterno') 
            ->orderByRaw('avg(puntajeTecnico) asc, count(puntajeTecnico) desc') 
            ->limit(10)
            ->get();

        $estadisticas['top_personas_mas_motivadas'] = \App\Persona::join('Inscripcion', 'Persona.idPersona', '=', 'Inscripcion.idPersona')
            ->select(DB::raw('nombres, apellidoPaterno, count(*) as inscripciones, sum(if(presente=1,1,0)) as presentes'))
            ->whereYear('Persona.created_at', Carbon::now()->format('Y')) 
            ->groupBy(['Persona.idPersona', 'nombres', 'apellidoPaterno']) 
            ->orderByRaw('count(*) desc') 
            ->limit(10)
            ->get();

        $estadisticas['top_coordinadores_mas_convocantes'] = \App\Actividad::join('Inscripcion', 'Actividad.idActividad', '=', 'Inscripcion.idActividad')
            ->join('Persona', 'Persona.idPersona', '=', 'Actividad.idCoordinador')
            ->select(DB::raw('nombres, apellidoPaterno, count(*) as inscripciones, sum(if(presente=1,1,0)) as presentes'))
            ->whereYear('Inscripcion.created_at', '>=', Carbon::now()->format('Y')) 
            ->groupBy(['Actividad.idCoordinador', 'nombres', 'apellidoPaterno']) 
            ->orderByRaw('count(*) desc') 
            ->limit(10)
            ->get();


        //dd($estadisticas);
        return view('backoffice.estadisticas.personas', $estadisticas);
    }

}
