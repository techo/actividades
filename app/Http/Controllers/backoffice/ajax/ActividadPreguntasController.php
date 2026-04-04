<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\ActividadPregunta;
use App\Actividad;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ActividadPreguntasController extends Controller
{
    public function index(Actividad $actividad)
    {
        return response()->json($actividad->preguntas);
    }

    public function store(Request $request, Actividad $actividad)
    {
        $request->validate([
            'pregunta'    => 'required|string|max:500',
            'descripcion' => 'nullable|string|max:1000',
            'tipo'        => 'required|in:abierta,desplegable',
            'opciones'    => 'nullable|array',
            'requerida'   => 'boolean',
            'orden'       => 'integer',
        ]);

        $pregunta = ActividadPregunta::create([
            'actividad_id' => $actividad->idActividad,
            'pregunta'     => $request->pregunta,
            'descripcion'  => $request->descripcion,
            'tipo'         => $request->tipo,
            'opciones'     => $request->tipo === 'desplegable' ? $request->opciones : null,
            'requerida'    => $request->requerida,
            'orden'        => $request->input('orden', 0),
        ]);

        return response()->json($pregunta, 201);
    }

    public function update(Request $request, Actividad $actividad, $preguntaId)
    {
        $pregunta = ActividadPregunta::where('actividad_id', $actividad->idActividad)->findOrFail($preguntaId);

        $request->validate([
            'pregunta'    => 'required|string|max:500',
            'descripcion' => 'nullable|string|max:1000',
            'tipo'        => 'required|in:abierta,desplegable',
            'opciones'    => 'nullable|array',
            'requerida'   => 'boolean',
            'orden'       => 'integer',
        ]);

        $pregunta->update([
            'pregunta'    => $request->pregunta,
            'descripcion' => $request->descripcion,
            'tipo'        => $request->tipo,
            'opciones'    => $request->tipo === 'desplegable' ? $request->opciones : null,
            'requerida'   => $request->requerida,
            'orden'       => $request->input('orden', $pregunta->orden),
        ]);

        return response()->json($pregunta);
    }

    public function destroy(Actividad $actividad, $preguntaId)
    {
        $pregunta = ActividadPregunta::where('actividad_id', $actividad->idActividad)->findOrFail($preguntaId);
        $pregunta->delete();

        return response()->json(null, 204);
    }
}
