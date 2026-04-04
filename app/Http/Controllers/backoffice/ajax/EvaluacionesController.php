<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Actividad;
use App\EvaluacionActividad;
use App\EvaluacionImpactoActividad;
use App\EvaluacionPersona;
use App\EvaluacionPersonaRespuesta;
use App\Http\Controllers\BaseController;
use App\Inscripcion;
use App\Mail\InvitacionEvaluacion;
use App\Persona;
use Illuminate\Http\Request;

class EvaluacionesController extends BaseController
{
    public function enviar($id, Request $request)
    {
        $actividad = Actividad::findOrFail($id);
        if ($actividad->idPais !== auth()->user()->idPaisPermitido){
            return "No tiene permisos";
        }
        $inscripciones = Inscripcion::where('Inscripcion.idActividad', '=', $id)
            ->where('Inscripcion.presente', '=', '1')
            ->with('Persona')
            ->get();

        foreach ($inscripciones as $i) {
            $this->intentaEnviar(new InvitacionEvaluacion($i->persona, $actividad), $i->persona);
        }
        return $inscripciones->count();
    }

    public function getActividadStats($id)
    {
        $actividad = Actividad::find($id);
        $evaluaron = $actividad->evaluaciones->count();
        $promedio = round($actividad->evaluaciones()->whereNotNull('puntaje')->avg('puntaje'), 2);
        $presentes = $actividad->cantidadPresentes;

        $totalConPuntaje = $actividad->evaluaciones()->whereNotNull('puntaje')->count();
        $excelentes = $actividad->evaluaciones()->where('puntaje', '>=', 9)->count();
        $porcentajeExcelente = $totalConPuntaje > 0 ? round($excelentes * 100 / $totalConPuntaje) : 0;

        return response()->json([
            'evaluaron'           => $evaluaron,
            'promedio'            => $promedio,
            'presentes'           => $presentes,
            'porcentaje_excelente' => $porcentajeExcelente,
        ]);
    }

    public function getActividadChartData($id)
    {
        $query = (new EvaluacionActividad)->newQuery();
        $chartData = $query->where('idActividad', $id)
            ->groupBy('puntaje')
            ->selectRaw('puntaje, count(*) as cantidad')
            ->get();

        $chartData = $chartData->toArray();
        $puntajes = [1,2,3,4,5,6,7,8,9,10];
        $puntajes = array_fill_keys($puntajes, 0);

        foreach ($chartData as $item){
            $puntajes[$item['puntaje']] = $item['cantidad'];
        }

        return response()->json(
            [
                'cantidades' => array_values($puntajes)
            ],
            200
        );
    }

    public function getVoluntariosStats($id)
    {
        $actividad = Actividad::find($id);
        $evaluaron = $actividad->evaluacionesVoluntarios()->distinct('idEvaluador')->count('idEvaluador');
        $promedioSocial = round($actividad->evaluacionesVoluntarios()->whereNotNull('puntajeSocial')->avg('puntajeSocial'),2);
        $promedioTecnico = round($actividad->evaluacionesVoluntarios()->whereNotNull('puntajeTecnico')->avg('puntajeTecnico'),2);
        $promedioGenero = round($actividad->evaluacionesVoluntarios()->whereNotNull('puntajeGenero')->avg('puntajeGenero'),2);


        return response()->json(
            [
                'evaluaron' => $evaluaron,
                'presentes' => $actividad->cantidadPresentes,
                'promedioSocial' => $promedioSocial,
                'promedioTecnico' => $promedioTecnico,
                'promedioGenero' => $promedioGenero
            ]
        );
    }

    public function getVoluntariosChartData($id)
    {
        $query = (new EvaluacionPersona())->newQuery();
        $dataSocial = $query->where('idActividad', $id)
            ->groupBy('puntajeSocial')
            ->selectRaw('puntajeSocial, count(*) as cantidad')
            ->get();

        $query = (new EvaluacionPersona())->newQuery();
        $dataTecnico = $query->where('idActividad', $id)
            ->groupBy('puntajeTecnico')
            ->selectRaw('puntajeTecnico, count(*) as cantidad')
            ->get();

        $query = (new EvaluacionPersona())->newQuery();
        $dataGenero = $query->where('idActividad', $id)
            ->groupBy('puntajeGenero')
            ->selectRaw('puntajeGenero, count(*) as cantidad')
            ->get();

        $dataSocial = $dataSocial->toArray();
        $dataTecnico = $dataTecnico->toArray();
        $dataGenero = $dataGenero->toArray();
        $puntajesKeys = [1,2,3,4,5,6,7,8,9,10];
        $puntajesSocial = array_fill_keys($puntajesKeys, 0);
        $puntajesTecnico = array_fill_keys($puntajesKeys, 0);
        $puntajesGenero = array_fill_keys($puntajesKeys, 0);

        foreach ($dataSocial as $item){
            $puntajesSocial[$item['puntajeSocial']] = $item['cantidad'];
        }

        foreach ($dataTecnico as $item){
            $puntajesTecnico[$item['puntajeTecnico']] = $item['cantidad'];
        }

        foreach ($dataGenero as $item){
            $puntajesGenero[$item['puntajeGenero']] = $item['cantidad'];
        }

        return response()->json(
            [
                'cantidadesSocial' => array_values($puntajesSocial),
                'cantidadesTecnico' => array_values($puntajesTecnico),
                'cantidadesGenero' => array_values($puntajesGenero)
            ],
            200
        );
    }

    public function getGeneralStats($id)
    {
        $actividad = Actividad::find($id);
        $presentes = $actividad->inscripciones()->presente()->count();
        $inscriptos = $actividad->inscripciones()->count();
        $ausentes = $inscriptos - $presentes;

        $horasPorVoluntario = 0;
        $horasTotal = 0;
        if ($actividad->fechaInicio && $actividad->fechaFin) {
            $horasPorVoluntario = round($actividad->fechaInicio->diffInHours($actividad->fechaFin));
            $horasTotal = $horasPorVoluntario * $presentes;
        }

        return response()->json([
            'presentes'           => $presentes,
            'inscriptos'          => $inscriptos,
            'ausentes'            => $ausentes,
            'horas_voluntariado'  => $horasTotal,
            'horas_por_voluntario' => $horasPorVoluntario,
        ]);
    }

    public function getStatsPorUsuario($id)
    {
        $persona = Persona::findOrFail($id);
        $inscripciones = $persona->inscripciones()->count();
        $presentes = $persona->inscripciones()->presente()->count();
        $ausentes = $persona->inscripciones()->ausente()->count();

        return response()->json(
            [
                'inscripciones' => $inscripciones,
                'presentes' => $presentes,
                'ausentes' => $ausentes,
            ]
        );
    }

    public function getComentarios($id)
    {
        $comentarios = EvaluacionActividad::where('idActividad', $id)
            ->whereNotNull('comentario')
            ->where('comentario', '!=', '')
            ->latest()
            ->limit(5)
            ->get(['comentario', 'tags_positivos', 'tags_negativos']);

        return response()->json($comentarios);
    }

    public function getTagsResumen($id)
    {
        $evaluaciones = EvaluacionActividad::where('idActividad', $id)
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

        $maxPositivo = !empty($topPositivos) ? max($topPositivos) : 1;
        $maxNegativo = !empty($topNegativos) ? max($topNegativos) : 1;

        $resultado = [
            'positivos' => array_map(fn($key, $count) => [
                'key'        => $key,
                'cantidad'   => $count,
                'porcentaje' => round($count * 100 / $maxPositivo),
            ], array_keys($topPositivos), $topPositivos),
            'negativos' => array_map(fn($key, $count) => [
                'key'        => $key,
                'cantidad'   => $count,
                'porcentaje' => round($count * 100 / $maxNegativo),
            ], array_keys($topNegativos), $topNegativos),
        ];

        return response()->json($resultado);
    }

    public function getCompetenciasStats($id)
    {
        $datos = EvaluacionPersonaRespuesta::join(
                'EvaluacionPersona',
                'evaluacion_persona_respuestas.idEvaluacionPersona',
                '=',
                'EvaluacionPersona.idEvaluacionPersona'
            )
            ->where('EvaluacionPersona.idActividad', $id)
            ->whereNotNull('evaluacion_persona_respuestas.score')
            ->groupBy('evaluacion_persona_respuestas.question_key')
            ->selectRaw('evaluacion_persona_respuestas.question_key, ROUND(AVG(evaluacion_persona_respuestas.score), 2) as promedio, COUNT(*) as cantidad')
            ->get();

        $promedioGlobal = $datos->avg('promedio');

        $questionKeys = ['conexion_equipo', 'compromiso_colaboracion', 'actitud_propositiva', 'potencia_otras'];
        $promedios = [];
        foreach ($questionKeys as $key) {
            $item = $datos->firstWhere('question_key', $key);
            $promedios[$key] = $item ? (float) $item->promedio : null;
        }

        // Texto de análisis: dimensión más alta y más baja
        $validos = array_filter($promedios, fn($v) => $v !== null);
        $analisis = null;
        if (!empty($validos)) {
            $maxKey = array_search(max($validos), $validos);
            $minKey = array_search(min($validos), $validos);
            $analisis = [
                'mas_alto'  => $maxKey,
                'mas_bajo'  => $minKey,
                'valor_alto' => round(max($validos), 1),
                'valor_bajo' => round(min($validos), 1),
            ];
        }

        return response()->json([
            'promedios'      => $promedios,
            'promedio_global' => round($promedioGlobal, 1),
            'analisis'       => $analisis,
        ]);
    }

    public function getImpactoStats($id)
    {
        $registros = EvaluacionImpactoActividad::where('idActividad', $id)->get();
        $total = $registros->count();

        if ($total === 0) {
            return response()->json([
                'total'          => 0,
                'habilidades'    => 0,
                'percepcion'     => 0,
                'recomendaria'   => 0,
            ]);
        }

        $habilidades  = $registros->where('impacto_habilidades_capacidades', '>=', 8)->count();
        $percepcion   = $registros->where('impacto_percepcion_realidad', '>=', 8)->count();
        $recomendaria = $registros->where('impacto_recomendaria_experiencia', '>=', 8)->count();

        return response()->json([
            'total'        => $total,
            'habilidades'  => round($habilidades * 100 / $total),
            'percepcion'   => round($percepcion * 100 / $total),
            'recomendaria' => round($recomendaria * 100 / $total),
        ]);
    }
}
