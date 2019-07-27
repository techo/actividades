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

    public function inscripciones_por_actividad(Request $request)
    {
        
        if($request->filled('sort')) {
            if(strpos($request->sort, "|")) $sort = join(" ",explode("|", $request->sort));
            else $sort = $request->sort;
        }

        $per_page = 25;
        if($request->filled('per_page')) { $per_page = $request->per_page; }

        $año = ($request->filled('año'))?$request->año:Carbon::now()->format('Y');
        $pais = ($request->filled('pais'))?$request->pais:null;
        $oficina = ($request->filled('oficina'))?$request->oficina:null;

        $consulta = \App\Actividad::join('Inscripcion', 'Actividad.idActividad', '=', 'Inscripcion.idActividad')
            ->select(DB::raw('Actividad.idActividad as id, nombreActividad, count(*) as inscripciones, sum(if(presente=1,1,0)) as presentes'))
            ->whereYear('created_at', $año)
            ->groupBy('Actividad.idActividad', 'Actividad.nombreActividad')
            ->orderByRaw($sort);

        if($pais) $consulta->where('Actividad.idPais', $pais);
        if($oficina) $consulta->where('Actividad.idOficina', $oficina);
        
        $estadisticas = $consulta->paginate($per_page);

        return $estadisticas;
    }

    public function evaluaciones_por_actividad(Request $request)
    {
        
        if($request->filled('sort')) {
            if(strpos($request->sort, "|")) $sort = join(" ",explode("|", $request->sort));
            else $sort = $request->sort;
        }

        $per_page = 25;
        if($request->filled('per_page')) { $per_page = $request->per_page; }

        $año = ($request->filled('año'))?$request->año:Carbon::now()->format('Y');
        $pais = ($request->filled('pais'))?$request->pais:null;
        $oficina = ($request->filled('oficina'))?$request->oficina:null;

        $consulta = \App\Actividad::join('EvaluacionActividad', 'Actividad.idActividad', '=', 'EvaluacionActividad.idActividad')
            ->select(DB::raw('Actividad.idActividad as id, nombreActividad, avg(puntaje) as puntaje, count(puntaje) as cantidad'))
            ->whereYear('created_at', $año) 
            ->groupBy('Actividad.idActividad', 'Actividad.nombreActividad') 
            ->orderByRaw($sort);

        if($pais) $consulta->where('Actividad.idPais', $pais);
        if($oficina) $consulta->where('Actividad.idOficina', $oficina);
        
        $estadisticas = $consulta->paginate($per_page);

        return $estadisticas;
    }

    public function coordinadores(Request $request)
    {
        
        if($request->filled('sort')) {
            if(strpos($request->sort, "|")) $sort = join(" ",explode("|", $request->sort));
            else $sort = $request->sort;
        }

        $per_page = 25;
        if($request->filled('per_page')) { $per_page = $request->per_page; }

        $año = ($request->filled('año'))?$request->año:Carbon::now()->format('Y');
        $pais = ($request->filled('pais'))?$request->pais:null;
        $oficina = ($request->filled('oficina'))?$request->oficina:null;

        $consulta = \App\Actividad::join('Inscripcion', 'Actividad.idActividad', '=', 'Inscripcion.idActividad')
            ->join('Persona', 'Persona.idPersona', '=', 'Actividad.idCoordinador')
            ->select(DB::raw('Persona.idPersona as id, nombres, apellidoPaterno, count(*) as inscripciones, sum(if(presente=1,1,0)) as presentes'))
            ->whereYear('Inscripcion.created_at', $año) 
            ->groupBy(['Actividad.idCoordinador', 'nombres', 'apellidoPaterno']) 
            ->orderByRaw($sort);

        if($pais) $consulta->where('Actividad.idPais', $pais);
        if($oficina) $consulta->where('Actividad.idOficina', $oficina);
        
        $estadisticas = $consulta->paginate($per_page);

        return $estadisticas;
    }

    public function inscripciones(Request $request)
    {
        
        if($request->filled('sort')) {
            if(strpos($request->sort, "|")) $sort = join(" ",explode("|", $request->sort));
            else $sort = $request->sort;
        }

        $per_page = 25;
        if($request->filled('per_page')) { $per_page = $request->per_page; }

        $año = ($request->filled('año'))?$request->año:Carbon::now()->format('Y');
        $pais = ($request->filled('pais'))?$request->pais:null;
        $oficina = ($request->filled('oficina'))?$request->oficina:null;

        $consulta = \App\Persona::join('Inscripcion', 'Persona.idPersona', '=', 'Inscripcion.idPersona')
            ->join('Actividad', 'Inscripcion.idActividad', '=', 'Actividad.idActividad')
            ->select(DB::raw('Persona.idPersona as id, nombres, apellidoPaterno, count(*) as inscripciones, sum(if(presente=1,1,0)) as presentes'))
            ->whereYear('Persona.created_at', $año) 
            ->groupBy(['Persona.idPersona', 'nombres', 'apellidoPaterno']) 
            ->orderByRaw($sort);

        if($pais) $consulta->where('Actividad.idPais', $pais);
        if($oficina) $consulta->where('Actividad.idOficina', $oficina);
        
        $estadisticas = $consulta->paginate($per_page);

        return $estadisticas;
    }

    public function evaluaciones_sociales(Request $request)
    {
        
        if($request->filled('sort')) {
            if(strpos($request->sort, "|")) $sort = join(" ",explode("|", $request->sort));
            else $sort = $request->sort;
        }

        $per_page = 25;
        if($request->filled('per_page')) { $per_page = $request->per_page; }

        $año = ($request->filled('año'))?$request->año:Carbon::now()->format('Y');
        $pais = ($request->filled('pais'))?$request->pais:null;
        $oficina = ($request->filled('oficina'))?$request->oficina:null;

        $consulta= \App\Persona::join('EvaluacionPersona', 'Persona.idPersona', '=', 'EvaluacionPersona.idEvaluado')
            ->join('Actividad', 'Actividad.idActividad', '=', 'EvaluacionPersona.idActividad')
            ->select(DB::raw('Persona.idPersona as id, Persona.nombres, Persona.apellidoPaterno, avg(puntajeSocial) as puntaje, count(puntajeSocial) as cantidad'))
            ->whereYear('EvaluacionPersona.created_at', $año) 
            ->whereNotNull('EvaluacionPersona.puntajeSocial')
            ->groupBy('Persona.idPersona', 'Persona.nombres', 'Persona.apellidoPaterno') 
            ->orderByRaw($sort);

        if($pais) $consulta->where('Actividad.idPais', $pais);
        if($oficina) $consulta->where('Actividad.idOficina', $oficina);
        
        $estadisticas = $consulta->paginate($per_page);

        return $estadisticas;
    }

    public function evaluaciones_tecnicas(Request $request)
    {
        
        if($request->filled('sort')) {
            if(strpos($request->sort, "|")) $sort = join(" ",explode("|", $request->sort));
            else $sort = $request->sort;
        }

        $per_page = 25;
        if($request->filled('per_page')) { $per_page = $request->per_page; }

        $año = ($request->filled('año'))?$request->año:Carbon::now()->format('Y');
        $pais = ($request->filled('pais'))?$request->pais:null;
        $oficina = ($request->filled('oficina'))?$request->oficina:null;

        $consulta = \App\Persona::join('EvaluacionPersona', 'Persona.idPersona', '=', 'EvaluacionPersona.idEvaluado')
            ->join('Actividad', 'Actividad.idActividad', '=', 'EvaluacionPersona.idActividad')
            ->select(DB::raw('Persona.idPersona as id, Persona.nombres, Persona.apellidoPaterno, avg(puntajeSocial) as puntaje, count(puntajeSocial) as cantidad'))
            ->whereYear('EvaluacionPersona.created_at', $año) 
            ->whereNotNull('EvaluacionPersona.puntajeSocial')
            ->groupBy('Persona.idPersona', 'Persona.nombres', 'Persona.apellidoPaterno') 
            ->orderByRaw($sort);

        if($pais) $consulta->where('Actividad.idPais', $pais);
        if($oficina) $consulta->where('Actividad.idOficina', $oficina);
        
        $estadisticas = $consulta->paginate($per_page);

        return $estadisticas;
    }

}
