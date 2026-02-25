<?php

namespace App\Http\Controllers;

use App\Actividad;
use App\EvaluacionActividad;
use App\EvaluacionImpactoActividad;
use App\EvaluacionPersona;
use App\EvaluacionPersonaRespuesta;
use App\Grupo;
use App\Http\Resources\PersonaEvaluadaResource;
use App\Inscripcion;
use App\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EvaluacionesController extends Controller
{
    public function index($id)
    {
        $user = auth()->user();
        $actividad = Actividad::findOrFail($id);
        $listadoInscriptos = $this->getInscriptos($actividad);

        $miGrupo = Grupo::join('Grupo_Persona', 'Grupo_Persona.idGrupo', '=', 'Grupo.idGrupo')
            ->where('Grupo_Persona.idPersona', '=', $user->idPersona)
            ->where('Grupo_Persona.idActividad', '=', $actividad->idActividad)
            ->first();

        // si no estoy en ningun grupo, estoy en la raíz
        if (is_null($miGrupo)) {
            // El grupo raíz no existe en actividades de legacy
            $miGrupo = Grupo::firstOrCreate(
                ['idActividad' => $actividad->idActividad,'nombre' => $actividad->nombreActividad,'idPadre' => 0]
            );
        }

        $gruposSubordinados = Grupo::where('idPadre', '=', $miGrupo->idGrupo)->pluck('idGrupo');

        $respuestaActividad = EvaluacionActividad::where('idPersona', '=', $user->idPersona)
            ->where('idActividad', '=', $actividad->idActividad)
            ->first();

        $evaluados = $this->getEvaluados($actividad, $user);
        $respuestasEvaluacion = EvaluacionPersona::with('respuestas')
            ->where('idActividad', $actividad->idActividad)
            ->where('idEvaluador', $user->idPersona)
            ->get();
        
        $respuestasImpactoActividad = EvaluacionImpactoActividad::where('idPersona', '=', $user->idPersona)
            ->where('idActividad', '=', $actividad->idActividad)
            ->first();

        if (request()->expectsJson() || request()->is('api/*')) {

            $idsGruposPermitidos = collect([
                $miGrupo->idGrupo,
                $miGrupo->idPadre,
            ])->filter(); // por si idPadre es null o 0

            $inscriptosMiGrupo = $listadoInscriptos->filter(function ($persona) use ($idsGruposPermitidos) {
                return $idsGruposPermitidos->contains($persona->idGrupo);
            })->values();

            return response()->json([
                'actividad' => $actividad,
                'miGrupo' => $miGrupo,
                'gruposSubordinados' => $gruposSubordinados,
                'listado_presentes' => $listadoInscriptos,
                'listado_a_evaluar' => $inscriptosMiGrupo,
                'respuesta_actividad' => $respuestaActividad,
                'respuestas_persona' => $respuestasEvaluacion,
                'respuestas_impacto' => $respuestasImpactoActividad,
                'preguntasEvaluacionPersona' => __('evaluacion.personas'),
                'preguntasEvaluacionImpacto' => __('evaluacion.impacto'),
            ]);
        }   

        return view(
            'evaluaciones.index',
            compact(
                'actividad',
                'respuestaActividad',
                'listadoInscriptos',
                'miGrupo',
                'gruposSubordinados',
                'evaluados',
                'respuestasEvaluacion',
                'respuestasImpactoActividad'
            )
        );
    }
    
    public function getTagsActividad(Request $request, $id)
    {
        return response()->json([
            'atributos' => __('evaluacion.atributos_actividad'),
            'mejoras' => __('evaluacion.mejoras_actividad'),
        ]);
    }

    public function evaluarActividad(Request $request)
    {
        $request->validate([
            'idActividad'     => 'required|integer|exists:Actividad,idActividad',
            'puntaje'         => 'required|numeric|min:0|max:10',
            'tagsPositivos'         => 'nullable|array',
            'tagsNegativos'         => 'nullable|array',
            'comentario'      => 'nullable|string|max:2000',
        ]);
        
        $persona = auth()->user();

        $inscripcion = Inscripcion::where('idActividad', '=', $request->idActividad)
            ->where('idPersona', '=', $persona->idPersona)
            ->first();

        if (!$inscripcion) {
            return response('Usuario no inscripto a esta actividad', 400);
        }

        $evaluacion = EvaluacionActividad::where('idActividad', '=', $request->idActividad)
            ->where('idPersona', '=', $persona->idPersona)
            ->first();
        if ($evaluacion) {
            return response('La evaluación ya existe', 400);
        }

        $request->request->add(['idPersona' => $persona->idPersona]);
        if (EvaluacionActividad::create($request->all())) {
            return response('ok');
        }

        return response('Error al guardar la evaluación', 500);
    }

    public  function evaluarImpacto(Request $request, $id)
    {
        $request->validate([
            'idActividad' => 'required|integer|exists:Actividad,idActividad',

            'impacto_habilidades_capacidades' => 'required|integer|min:1|max:10',
            'impacto_percepcion_realidad'     => 'required|integer|min:1|max:10',
            'impacto_recomendaria_experiencia'=> 'required|integer|min:1|max:10',
        ]);

        $persona = auth()->user();

        $inscripcion = Inscripcion::where('idActividad', $request->idActividad)
            ->where('idPersona', $persona->idPersona)
            ->first();

        if (!$inscripcion) {
            return response('Usuario no inscripto a esta actividad', 400);
        }

        EvaluacionImpactoActividad::updateOrCreate(
            [
                'idActividad' => $request->idActividad,
                'idPersona'   => $persona->idPersona,
            ],
            [
                'impacto_habilidades_capacidades' => $request->impacto_habilidades_capacidades,
                'impacto_percepcion_realidad'     => $request->impacto_percepcion_realidad,
                'impacto_recomendaria_experiencia'=> $request->impacto_recomendaria_experiencia,
            ]
        );

        return response('ok');
        
    }

    public function evaluarPersona(Request $request, $id)
    {
        $actividad = Actividad::findOrFail($id);
        $evaluador = auth()->user();

        // ───────────────────────────────────────────────
        // 1) Verificar si ya evaluó a esa persona
        // ───────────────────────────────────────────────
        $yaEvaluado = EvaluacionPersona::where([
            'idActividad' => $id,
            'idEvaluador' => $evaluador->idPersona,
            'idEvaluado'  => $request->evaluado['idPersona'],
        ])->exists();

        if ($yaEvaluado) {
            return response()->json(['error' => 'La evaluación ya existe'], 400);
        }

        // ───────────────────────────────────────────────
        // 2) Normalizar puntajes (null si no aplica)
        // ───────────────────────────────────────────────
        $evaluacionData = [
            'idActividad'   => $actividad->idActividad,
            'idEvaluador'   => $evaluador->idPersona,
            'idEvaluado'    => $request->evaluado['idPersona'],
            'puntajeSocial' => $request->noAplicaSocial ? null : $request->puntajeSocial,
            'puntajeTecnico'=> $request->noAplicaTecnico ? null : $request->puntajeTecnico,
            'puntajeGenero' => $request->noAplicaGenero ? null : $request->puntajeGenero,
            'comentario'    => $request->comentario,
        ];

        // ───────────────────────────────────────────────
        // 3) Guardar evaluación + respuestas en una transacción
        // ───────────────────────────────────────────────
        try {
            DB::beginTransaction();

            $evaluacion = EvaluacionPersona::create($evaluacionData);

            foreach ($request->puntajes as $questionKey => $score) {

                $tags = $request->tagsSeleccionados[$questionKey] ?? [
                    'positivos' => [],
                    'negativos' => []
                ];

                EvaluacionPersonaRespuesta::create([
                    'idEvaluacionPersona' => $evaluacion->idEvaluacionPersona,
                    'question_key'          => $questionKey,
                    'score'                 => (int) $score,
                    'tags_positivos'        => $tags['positivos'],
                    'tags_negativos'        => $tags['negativos'],
                    'comentario'            => null
                ]);
            }

            DB::commit();
            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {

            DB::rollBack();
            return response()->json([
                'error' => 'Ocurrió un error al guardar la evaluación',
                'detail' => $e->getMessage(),
            ], 500);
        }
    }

    private function getInscriptos(Actividad $actividad)
    {
        $inscriptosCollection = Inscripcion::join('Persona', 'Persona.idPersona', '=', 'Inscripcion.idPersona')
            ->join('Grupo_Persona', 'Grupo_Persona.idPersona', '=', 'Inscripcion.idPersona')
            ->select(
                [
                    'Persona.idPersona', 'Persona.nombres', 'Persona.apellidoPaterno', 'Persona.dni',
                    'Inscripcion.rol', 'Grupo_Persona.idGrupo'
                ]
            )
            ->where('Inscripcion.presente', '=', 1)
            ->where('Inscripcion.idActividad', '=', $actividad->idActividad)
            ->where('Grupo_Persona.idActividad', '=', $actividad->idActividad)
            ->get();

        $inscriptos = collect();
        foreach ($inscriptosCollection as $item) {
            $inscriptos->push(new PersonaEvaluadaResource($item));
        }

        return $inscriptos;
    }

    /**
     * @param Actividad $actividad
     * @param Persona $evaluador
     * @return Collection
     */
    private function getEvaluados(Actividad $actividad, Persona $evaluador)
    {
        return Persona::join('EvaluacionPersona', 'Persona.idPersona', '=', 'EvaluacionPersona.idEvaluado')
            ->join('Grupo_Persona', 'Persona.idPersona', '=', 'Grupo_Persona.idPersona')
            ->select([ 'Persona.idPersona', 'Persona.nombres', 'Persona.apellidoPaterno', 'Persona.dni',
                'EvaluacionPersona.puntajeSocial', 'EvaluacionPersona.puntajeTecnico','EvaluacionPersona.puntajeGenero', 'EvaluacionPersona.comentario',
                'Grupo_Persona.rol'])
            ->where('EvaluacionPersona.idActividad', '=', $actividad->idActividad)
            ->where('EvaluacionPersona.idEvaluador', '=', $evaluador->idPersona)
            ->where('Grupo_Persona.idActividad', '=', $actividad->idActividad)
            ->get();
        }
}
