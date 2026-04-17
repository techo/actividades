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

        $suscripcion = Suscribe::create($data);

        // Guardar respuestas a preguntas dinámicas si las hay
        if ($request->filled('respuestas') && is_array($request->respuestas)) {
            foreach ($request->respuestas as $respuesta) {
                if (!empty($respuesta['pregunta_id'])) {
                    SuscribeRespuesta::create([
                        'suscripcion_id' => $suscripcion->id,
                        'pregunta_id'    => $respuesta['pregunta_id'],
                        'respuesta'      => $respuesta['respuesta'] ?? null,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => __('suscribe.success'),
        ]);
    }
}
