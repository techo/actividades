<?php

namespace App\Http\Controllers\backoffice;

use App\EvaluacionActividad;
use App\EvaluacionImpactoActividad;
use App\EvaluacionPersona;
use App\EvaluacionPersonaRespuesta;
use App\Http\Controllers\Controller;
use App\Inscripcion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EstadisticasController extends Controller
{

    public function index()
    {
        // Movilizados del ciclo (presente=1) = definición única en MovilizacionMetrics.
        $estadisticas['inscripciones_ciclo'] =
            \App\Reporting\MovilizacionMetrics::movilizadosTotal(Carbon::now()->format('Y'));

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

        // Actividades realizadas por mes = por fecha de la actividad (fechaInicio).
        $consulta = \App\Actividad::join('Tipo', 'Tipo.idTipo', '=', 'Actividad.idTipo')
            ->join('atl_CategoriaActividad', 'atl_CategoriaActividad.id', '=', 'Tipo.idCategoria')
            ->select(DB::raw('MONTH(Actividad.fechaInicio) mes, atl_CategoriaActividad.nombre, color, count(*) cantidad'))
            ->whereYear('Actividad.fechaInicio', $año)
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

    public function grafico_evaluaciones(Request $request)
    {
        $año = ($request->filled('año'))?$request->año:Carbon::now()->format('Y');
        $pais = ($request->filled('pais'))?$request->pais:null;
        $oficina = ($request->filled('oficina'))?$request->oficina:null;

        $consulta = \App\EvaluacionPersona::join('Actividad', 'Actividad.idActividad', '=', DB::raw('EvaluacionPersona.idActividad'))
            ->select(DB::raw('MONTH(Actividad.fechaCreacion) as mes, AVG(EvaluacionPersona.puntajeSocial) as puntajeSocial, AVG(EvaluacionPersona.puntajeTecnico) as puntajeTecnico'))
            ->groupBy('mes')
            ->whereYear('Actividad.fechaCreacion', $año);

        if($pais) $consulta->where('Actividad.idPais', $pais);
        if($oficina) $consulta->where('Actividad.idOficina', $oficina);
        
        $evaluaciones = $consulta->get();

        $respuesta = [];
        foreach ($evaluaciones as $e) {
            $respuesta['meses'][] = $e->mes;
            $respuesta['puntajeSocial'][] = $e->puntajeSocial;
            $respuesta['puntajeTecnico'][] = $e->puntajeTecnico;
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
            ->orderByRaw(\App\Search\SortSanitizer::sanitize($sort ?? null, 'id desc'));

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
            ->orderByRaw(\App\Search\SortSanitizer::sanitize($sort ?? null, 'id desc'));

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
            ->orderByRaw(\App\Search\SortSanitizer::sanitize($sort ?? null, 'id desc'));

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

        $fecha_desde = ($request->filled('fecha_desde'))?$request->fecha_desde:Carbon::now()->startOfYear()->format('Y-m-d');
        $fecha_hasta = ($request->filled('fecha_hasta'))?$request->fecha_hasta:Carbon::now()->format('Y-m-d');

        $edad_desde = ($request->filled('edad_desde')) ? $request->edad_desde : 0;
        $edad_hasta = ($request->filled('edad_hasta')) ? $request->edad_hasta : null;

        $oficina = ($request->filled('oficina'))?$request->oficina:null;

        $consulta = \App\Persona::join('Inscripcion', 'Persona.idPersona', '=', 'Inscripcion.idPersona')
            ->join('Actividad', 'Inscripcion.idActividad', '=', 'Actividad.idActividad')
            ->leftJoin('atl_provincias as personaProvincia', 'Persona.idProvincia', '=', 'personaProvincia.id')
            ->leftJoin('atl_oficinas as oficina', 'personaProvincia.idOficina', '=', 'oficina.id')
            ->select(DB::raw('Persona.idPersona as id, nombres, apellidoPaterno, count(*) as inscripciones, sum(if(presente=1,1,0)) as presentes, oficina.nombre as oficina'))
            ->where('Actividad.idPais', auth()->user()->idPaisPermitido)
            ->groupBy(['Persona.idPersona', 'nombres', 'apellidoPaterno']) 
            ->orderByRaw(\App\Search\SortSanitizer::sanitize($sort ?? null, 'id desc'));

        if($fecha_desde && $fecha_hasta)
            $consulta->whereBetween('Inscripcion.created_at', [$fecha_desde, $fecha_hasta]);
        if($edad_hasta)
            $consulta->whereRaw("TIMESTAMPDIFF(YEAR, Persona.fechaNacimiento, CURDATE()) BETWEEN ? AND ?", [(int) $edad_desde, (int) $edad_hasta]);
        if($oficina) $consulta->where('Actividad.idOficina', $oficina);
        
        $estadisticas = $consulta->paginate($per_page);

        return $estadisticas;
    }

    // ── Helpers & nuevos endpoints para el dashboard general de evaluaciones ──

    private function getActividadIdsFromRequest(Request $request)
    {
        $año     = $request->filled('año')     ? $request->año     : Carbon::now()->format('Y');
        $pais    = $request->filled('pais')    ? $request->pais    : null;
        $oficina = $request->filled('oficina') ? $request->oficina : null;

        $q = \App\Actividad::whereYear('fechaCreacion', $año);
        if ($pais)    $q->where('idPais', $pais);
        if ($oficina) $q->where('idOficina', $oficina);

        return $q->pluck('idActividad');
    }

    public function evaluaciones_general_stats(Request $request)
    {
        $actividadIds = $this->getActividadIdsFromRequest($request);

        $row = Inscripcion::whereIn('idActividad', $actividadIds)
            ->selectRaw('COUNT(*) as inscriptos, SUM(IF(presente=1,1,0)) as presentes')
            ->first();

        $inscriptos = $row->inscriptos ?? 0;
        $presentes  = $row->presentes  ?? 0;

        $horasData = \App\Actividad::whereIn('idActividad', $actividadIds)
            ->whereNotNull('fechaFin')
            ->whereNotNull('fechaInicio')
            ->leftJoin('Inscripcion as ins', function ($join) {
                $join->on('ins.idActividad', '=', 'Actividad.idActividad')
                     ->where('ins.presente', '=', 1);
            })
            ->select('Actividad.idActividad', 'Actividad.fechaInicio', 'Actividad.fechaFin')
            ->selectRaw('COUNT(ins.idInscripcion) as presentes_act')
            ->groupBy('Actividad.idActividad', 'Actividad.fechaInicio', 'Actividad.fechaFin')
            ->get();

        $horasTotal = 0;
        foreach ($horasData as $a) {
            $dur = max(0, Carbon::parse($a->fechaInicio)->diffInHours(Carbon::parse($a->fechaFin)));
            $horasTotal += $dur * $a->presentes_act;
        }

        return response()->json([
            'inscriptos'           => $inscriptos,
            'presentes'            => $presentes,
            'ausentes'             => $inscriptos - $presentes,
            'horas_voluntariado'   => $horasTotal,
            'horas_por_voluntario' => $presentes > 0 ? round($horasTotal / $presentes, 1) : 0,
        ]);
    }

    public function evaluaciones_actividad_stats(Request $request)
    {
        $actividadIds = $this->getActividadIdsFromRequest($request);

        $stats = EvaluacionActividad::whereIn('idActividad', $actividadIds)
            ->selectRaw('ROUND(AVG(puntaje), 2) as promedio, COUNT(*) as total, SUM(IF(puntaje >= 9, 1, 0)) as excelentes')
            ->first();

        $total     = $stats->total ?? 0;
        $presentes = Inscripcion::whereIn('idActividad', $actividadIds)->where('presente', 1)->count();

        return response()->json([
            'promedio'             => $total > 0 ? round($stats->promedio, 1) : 0,
            'porcentaje_excelente' => $total > 0 ? round($stats->excelentes * 100 / $total) : 0,
            'evaluaron'            => $total,
            'presentes'            => $presentes,
        ]);
    }

    public function evaluaciones_histograma(Request $request)
    {
        $actividadIds = $this->getActividadIdsFromRequest($request);

        $chartData = EvaluacionActividad::whereIn('idActividad', $actividadIds)
            ->whereNotNull('puntaje')
            ->groupBy('puntaje')
            ->selectRaw('puntaje, count(*) as cantidad')
            ->get();

        $puntajes = array_fill_keys([1,2,3,4,5,6,7,8,9,10], 0);
        foreach ($chartData as $item) {
            if (isset($puntajes[$item->puntaje])) {
                $puntajes[$item->puntaje] = $item->cantidad;
            }
        }

        return response()->json(['cantidades' => array_values($puntajes)]);
    }

    public function evaluaciones_comentarios_general(Request $request)
    {
        $actividadIds = $this->getActividadIdsFromRequest($request);

        $comentarios = EvaluacionActividad::whereIn('idActividad', $actividadIds)
            ->whereNotNull('comentario')
            ->where('comentario', '!=', '')
            ->latest()
            ->limit(5)
            ->get(['comentario', 'tags_positivos', 'tags_negativos']);

        return response()->json($comentarios);
    }

    public function evaluaciones_tags_general(Request $request)
    {
        $actividadIds = $this->getActividadIdsFromRequest($request);

        $evaluaciones = EvaluacionActividad::whereIn('idActividad', $actividadIds)
            ->get(['tags_positivos', 'tags_negativos']);

        $positivos = [];
        $negativos = [];
        foreach ($evaluaciones as $ev) {
            foreach (($ev->tags_positivos ?? []) as $tag) {
                $positivos[$tag] = ($positivos[$tag] ?? 0) + 1;
            }
            foreach (($ev->tags_negativos ?? []) as $tag) {
                $negativos[$tag] = ($negativos[$tag] ?? 0) + 1;
            }
        }

        arsort($positivos);
        arsort($negativos);

        $topPositivos = array_slice($positivos, 0, 5, true);
        $topNegativos = array_slice($negativos, 0, 5, true);
        $maxP = !empty($topPositivos) ? max($topPositivos) : 1;
        $maxN = !empty($topNegativos) ? max($topNegativos) : 1;

        $itemsP = [];
        foreach ($topPositivos as $key => $count) {
            $itemsP[] = [
                'key'        => $key,
                'label'      => __('evaluacion.atributos_actividad.' . $key),
                'cantidad'   => $count,
                'porcentaje' => round($count * 100 / $maxP),
            ];
        }
        $itemsN = [];
        foreach ($topNegativos as $key => $count) {
            $itemsN[] = [
                'key'        => $key,
                'label'      => __('evaluacion.mejoras_actividad.' . $key),
                'cantidad'   => $count,
                'porcentaje' => round($count * 100 / $maxN),
            ];
        }

        return response()->json(['positivos' => $itemsP, 'negativos' => $itemsN]);
    }

    public function evaluaciones_competencias_general(Request $request)
    {
        $actividadIds  = $this->getActividadIdsFromRequest($request);
        $evaluacionIds = EvaluacionPersona::whereIn('idActividad', $actividadIds)
            ->pluck('idEvaluacionPersona');

        $datos = EvaluacionPersonaRespuesta::whereIn('idEvaluacionPersona', $evaluacionIds)
            ->whereNotNull('score')
            ->groupBy('question_key')
            ->selectRaw('question_key, ROUND(AVG(score), 2) as promedio')
            ->get();

        $questionKeys = ['conexion_equipo', 'compromiso_colaboracion', 'actitud_propositiva', 'potencia_otras'];
        $promedios = [];
        foreach ($questionKeys as $key) {
            $item = $datos->firstWhere('question_key', $key);
            $promedios[$key] = $item ? (float) $item->promedio : null;
        }

        $validos  = array_filter($promedios, function ($v) { return $v !== null; });
        $analisis = null;
        if (!empty($validos)) {
            $maxKey = array_search(max($validos), $validos);
            $minKey = array_search(min($validos), $validos);
            $analisis = [
                'mas_alto'   => $maxKey,
                'mas_bajo'   => $minKey,
                'valor_alto' => round(max($validos), 1),
                'valor_bajo' => round(min($validos), 1),
            ];
        }

        $promedioGlobal = !empty($validos) ? round(array_sum($validos) / count($validos), 1) : null;

        return response()->json([
            'promedios'       => $promedios,
            'promedio_global' => $promedioGlobal,
            'analisis'        => $analisis,
        ]);
    }

    public function evaluaciones_impacto_general(Request $request)
    {
        $actividadIds = $this->getActividadIdsFromRequest($request);
        $registros    = EvaluacionImpactoActividad::whereIn('idActividad', $actividadIds)->get();
        $total        = $registros->count();

        if ($total === 0) {
            return response()->json(['total' => 0, 'habilidades' => 0, 'percepcion' => 0, 'recomendaria' => 0, 'promotores' => 0, 'detractores' => 0]);
        }

        return response()->json([
            'total'        => $total,
            'habilidades'  => round($registros->where('impacto_habilidades_capacidades',  '>=', 8)->count() * 100 / $total),
            'percepcion'   => round($registros->where('impacto_percepcion_realidad',       '>=', 8)->count() * 100 / $total),
            'recomendaria' => round($registros->where('impacto_recomendaria_experiencia',  '>=', 8)->count() * 100 / $total),
            'promotores' => round($registros->where('impacto_recomendaria_experiencia',  '>=', 9)->count() * 100 / $total),
            'detractores' => round($registros->where('impacto_recomendaria_experiencia',  '<=', 6)->count() * 100 / $total),
        ]);
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
            ->orderByRaw(\App\Search\SortSanitizer::sanitize($sort ?? null, 'id desc'));

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
            ->orderByRaw(\App\Search\SortSanitizer::sanitize($sort ?? null, 'id desc'));

        if($pais) $consulta->where('Actividad.idPais', $pais);
        if($oficina) $consulta->where('Actividad.idOficina', $oficina);
        
        $estadisticas = $consulta->paginate($per_page);

        return $estadisticas;
    }

}
