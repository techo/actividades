<?php

namespace App\Http\Controllers\backoffice\ajax;

use App\Campaign;
use App\CampaignPregunta;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CampaignPreguntasController extends Controller
{
    public function index(Campaign $campana)
    {
        return response()->json($campana->preguntas);
    }

    public function store(Request $request, Campaign $campana)
    {
        $request->validate([
            'pregunta'    => 'required|string|max:500',
            'descripcion' => 'nullable|string|max:1000',
            'tipo'        => 'required|in:abierta,desplegable',
            'opciones'    => 'nullable|array',
            'requerida'   => 'boolean',
            'orden'       => 'integer',
        ]);

        $pregunta = CampaignPregunta::create([
            'campaign_id' => $campana->id,
            'pregunta'    => $request->pregunta,
            'descripcion' => $request->descripcion,
            'tipo'        => $request->tipo,
            'opciones'    => $request->tipo === 'desplegable' ? $request->opciones : null,
            'requerida'   => $request->requerida ?? false,
            'orden'       => $request->input('orden', 0),
        ]);

        return response()->json($pregunta, 201);
    }

    public function update(Request $request, Campaign $campana, $preguntaId)
    {
        $pregunta = CampaignPregunta::where('campaign_id', $campana->id)->findOrFail($preguntaId);

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
            'requerida'   => $request->requerida ?? $pregunta->requerida,
            'orden'       => $request->input('orden', $pregunta->orden),
        ]);

        return response()->json($pregunta);
    }

    public function destroy(Campaign $campana, $preguntaId)
    {
        $pregunta = CampaignPregunta::where('campaign_id', $campana->id)->findOrFail($preguntaId);
        $pregunta->delete();

        return response()->json(null, 204);
    }
}
