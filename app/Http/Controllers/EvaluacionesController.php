<?php

namespace App\Http\Controllers;

use App\Actividad;
use App\EvaluacionActividad;
use App\Inscripcion;
use App\Persona;
use Illuminate\Http\Request;

class EvaluacionesController extends Controller
{
    public function index($id)
    {
        $actividad = Actividad::findOrFail($id);
        $user = auth()->user();
        $inscriptos = Persona::join('Inscripcion', 'Persona.idPersona', '=', 'Inscripcion.idPersona')
            ->where('Inscripcion.presente', '=', 1)
            ->where('idActividad', '=', $actividad->idActividad)
            ->get();

        $respuestaActividad = EvaluacionActividad::where('idPersona', '=', $user->idPersona)
            ->where('idActividad', '=', $actividad->idActividad)
            ->first();
        return view('evaluaciones.index', compact('actividad', 'respuestaActividad', 'inscriptos'));
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
}
