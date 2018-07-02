<?php

namespace App\Http\Controllers;

use App\Actividad;
use App\EvaluacionActividad;
use App\EvaluacionPersona;
use App\Grupo;
use App\Http\Resources\PersonaEvaluadaResource;
use App\Persona;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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

        if (is_null($miGrupo)) {
            $miGrupo = Grupo::where('idActividad', '=', $actividad->idActividad)
                ->where('idPadre', '=', 0)
                ->first();
        }

        $gruposSubordinados = Grupo::where('idPadre', '=', $miGrupo->idGrupo)->pluck('idGrupo');

        $respuestaActividad = EvaluacionActividad::where('idPersona', '=', $user->idPersona)
            ->where('idActividad', '=', $actividad->idActividad)
            ->first();

        $evaluados = $this->getEvaluados($actividad, $user);
        return view(
            'evaluaciones.index',
            compact(
                'actividad',
                'respuestaActividad',
                'listadoInscriptos',
                'miGrupo',
                'gruposSubordinados',
                'evaluados'
            )
        );
    }

    public function evaluarActividad(Request $request)
    {
        $persona = auth()->user();
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

    public function evaluarPersona(Request $request, $id, $idPersona)
    {
        $actividad = Actividad::findOrFail($id);
        $evaluador = auth()->user();
        $yaEvaluado = EvaluacionPersona::where('idActividad', '=', $id)
            ->where('idEvaluador', '=', $evaluador->idPersona)
            ->where('idEvaluado', '=', $request->evaluado['idPersona'])
            ->first();

        if ($yaEvaluado) {
            return response('la evaluación ya existe', 400);
        }

        $puntajeSocial = ($request->noAplicaSocial) ?  null : $request->puntajeSocial;
        $puntajeTecnico = ($request->noAplicaTecnico) ? null : $request->puntajeTecnico;
        $evaluacion = [
            'idActividad'   => $actividad->idActividad,
            'idEvaluador' => $evaluador->idPersona,
            'idEvaluado' => $request->evaluado['idPersona'],
            'puntajeSocial' => $puntajeSocial,
            'puntajeTecnico' => $puntajeTecnico,
            'comentario'    => $request->comentario
        ];

        if (EvaluacionPersona::create($evaluacion)) {
            return response('ok', 200);
        }

        return response('Ocurrió un error al guardar evaluación de persona', 500);
    }

    private function getInscriptos(Actividad $actividad)
    {
        $inscriptosCollection = Persona::join('Inscripcion', 'Persona.idPersona', '=', 'Inscripcion.idPersona')
            ->join('Grupo_Persona', 'Grupo_Persona.idPersona', '=', 'Inscripcion.idPersona')
            ->select(
                [
                    'Persona.idPersona', 'Persona.nombres', 'Persona.apellidoPaterno', 'Persona.dni',
                    'Grupo_Persona.rol', 'Grupo_Persona.idGrupo'
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
                'EvaluacionPersona.puntajeSocial', 'EvaluacionPersona.puntajeTecnico', 'EvaluacionPersona.comentario',
                'Grupo_Persona.rol'])
            ->where('EvaluacionPersona.idActividad', '=', $actividad->idActividad)
            ->where('EvaluacionPersona.idEvaluador', '=', $evaluador->idPersona)
            ->where('Grupo_Persona.idActividad', '=', $actividad->idActividad)
            ->get();
        }
}
