<?php

namespace App\Http\Controllers;

use App\Actividad;
use App\EvaluacionActividad;
use App\Grupo;
use App\Http\Resources\PersonaEvaluadaResource;
use App\Persona;
use Illuminate\Http\Request;

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

        $gruposSubordinados = Grupo::where('idPadre', '=', $miGrupo->idGrupo)->pluck('idGrupo');

        $respuestaActividad = EvaluacionActividad::where('idPersona', '=', $user->idPersona)
            ->where('idActividad', '=', $actividad->idActividad)
            ->first();

        return view(
            'evaluaciones.index',
            compact(
                'actividad',
                'respuestaActividad',
                'listadoInscriptos',
                'miGrupo',
                'gruposSubordinados'
            )
        );
    }

    public function evaluarActividad(Request $request)
    {
        $user = auth()->user();
        $request->request->add(['idPersona' => $user->idPersona]);
        if (EvaluacionActividad::create($request->all())) {
            return response('ok');
        }

        return response('Error al guardar la evaluación', 500);
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

        $inscriptos = [];
        foreach ($inscriptosCollection as $item) {
            $inscriptos[] = new PersonaEvaluadaResource($item);
        }

        return $inscriptos;
    }
}
