<?php

namespace App\Http\Controllers;

use App\Pais;
use App\Persona;
use App\Suscribe;
use App\SuscribeRespuesta;
use Illuminate\Http\Request;

class SuscribeController extends Controller
{
    public function get($abreviacion)
    {
        $pais = Pais::where('abreviacion', $abreviacion)->firstOrFail();
        app()->setLocale($pais->locale);
        return view('perfil.suscribe', compact('pais'));
    }

    /**
     * REQ 2 — Verifica si un email ya pertenece a un usuario registrado.
     */
    public function checkEmail(Request $request)
    {
        $email = trim($request->email ?? '');
        if (!$email) {
            return response()->json(['exists' => false]);
        }
        $exists = Persona::where('mail', $email)->exists();
        return response()->json(['exists' => $exists]);
    }

    public function create(Request $request)
    {
        $data = $request->except('respuestas');

        // REQ 4 — Prevenir duplicados por campaña
        if (!empty($data['campaign_id'])) {
            $mailCheck = auth()->check() ? auth()->user()->mail : ($data['mail'] ?? null);
            if ($mailCheck) {
                $yaExiste = Suscribe::where('campaign_id', $data['campaign_id'])
                    ->where('mail', $mailCheck)
                    ->exists();
                if ($yaExiste) {
                    return response()->json([
                        'already_registered' => true,
                        'message'            => __('suscribe.already_registered'),
                    ], 422);
                }
            }
        }

        // REQ 3 — Si hay usuario logueado, sobreescribir con sus datos reales
        if (auth()->check()) {
            $user            = auth()->user();
            $data['nombre']  = $user->nombres;
            $data['apellido'] = $user->apellidoPaterno;
            $data['mail']    = $user->mail;
            $data['telefono'] = $user->telefonoMovil ?? ($data['telefono'] ?? '');
            $data['idPersona'] = $user->idPersona;
        }

        // Resolver respuestas y visibilidad ANTES de crear la suscripción, para
        // poder rechazar si falta una pregunta obligatoria visible.
        $respuestasIn = ($request->filled('respuestas') && is_array($request->respuestas))
            ? $request->respuestas
            : [];

        $map = [];
        foreach ($respuestasIn as $r) {
            if (!empty($r['pregunta_id'])) {
                $map[$r['pregunta_id']] = isset($r['respuesta']) ? $r['respuesta'] : null;
            }
        }

        $preguntas = !empty($data['campaign_id'])
            ? \App\CampaignPregunta::where('campaign_id', $data['campaign_id'])
                ->with('condiciones')->get()
            : collect();

        $visibles = array_flip(
            \App\Services\Preguntas\ConditionEvaluator::visibleIds($preguntas, $map)
        );

        // Validar obligatorias SOLO entre las visibles. Las ocultas nunca son
        // obligatorias y sus respuestas se descartan.
        foreach ($preguntas as $pregunta) {
            if (!isset($visibles[$pregunta->id])) continue;
            if (!$pregunta->requerida) continue;
            $valor = isset($map[$pregunta->id]) ? trim((string) $map[$pregunta->id]) : '';
            if ($valor === '') {
                return response()->json([
                    'success' => false,
                    'message' => __('suscribe.missing_required'),
                ], 422);
            }
        }

        $suscripcion = Suscribe::create($data);

        // Guardar solo las respuestas de preguntas visibles.
        foreach ($respuestasIn as $respuesta) {
            if (empty($respuesta['pregunta_id'])) continue;
            if (!isset($visibles[$respuesta['pregunta_id']])) continue;

            SuscribeRespuesta::create([
                'suscripcion_id' => $suscripcion->id,
                'pregunta_id'    => $respuesta['pregunta_id'],
                'respuesta'      => $respuesta['respuesta'] ?? null,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => __('suscribe.success'),
        ]);
    }
}
