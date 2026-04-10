<?php

namespace App\Http\Controllers;

use App\Pais;
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

    public function create(Request $request)
    {
        $data = $request->except('respuestas');

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
            'message' => __('suscribe.success')
        ]);
    }
}
