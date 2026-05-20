<?php

namespace App\Http\Controllers\api;

use App\Campaign;
use App\Suscribe;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SuscribeController;
use Illuminate\Http\Request;

class CampanasController extends Controller
{
    /**
     * GET /api/campanas
     *
     * Lista campañas activas. Filtrables por:
     *   ?pais_id=1          — país (int)
     *   ?tipo=colecta        — tipo (colecta | captacion)
     *   ?per_page=20         — items por página (max 50)
     *
     * Respuesta paginada estándar (misma estructura que el resto de la API).
     */
    public function index(Request $request)
    {
        $perPage = min((int) $request->get('per_page', 20), 50);

        $query = Campaign::where('activa', true)
            ->with('preguntas')
            ->orderBy('fecha_inicio', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('pais_id')) {
            $query->where('pais_id', $request->pais_id);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        return response()->json($query->paginate($perPage));
    }

    /**
     * GET /api/campanas/{id}
     *
     * Detalle de una campaña activa. Incluye preguntas dinámicas y
     * confirmation_message (HTML) para que la app lo muestre post-suscripción.
     */
    public function show($id)
    {
        $campana = Campaign::where('activa', true)
            ->with('preguntas')
            ->findOrFail($id);

        return response()->json($campana);
    }

    /**
     * POST /api/campanas/{id}/suscribir   [auth:api]
     *
     * Suscribe al usuario autenticado a la campaña.
     * Delega completamente en SuscribeController::create(), que ya maneja:
     *   — sobreescritura con datos del usuario logueado (auth()->check())
     *   — duplicados (422 + already_registered)
     *   — respuestas a preguntas dinámicas
     *
     * Body JSON:
     * {
     *   "respuestas": [                       // opcional
     *     { "pregunta_id": 1, "respuesta": "Sí" }
     *   ]
     * }
     *
     * Respuesta éxito (200):
     * {
     *   "success": true,
     *   "message": "...",
     *   "whatsapp_link": "https://...",        // null si no tiene
     *   "confirmation_message": "<p>...</p>"   // null si no tiene
     * }
     */
    public function suscribir(Request $request, $id)
    {
        $campana = Campaign::where('activa', true)->findOrFail($id);

        // Inyectar campaign_id para que SuscribeController lo procese
        $request->merge(['campaign_id' => $campana->id]);

        // Delegar — SuscribeController detecta auth()->check() y usa los datos
        // del usuario Passport autenticado, sin necesidad de que la app los envíe
        $response = app(SuscribeController::class)->create($request);

        // Si hubo error (duplicado, validación) devolver tal cual
        if ($response->status() !== 200) {
            return $response;
        }

        // En éxito, enriquecer con los datos que la app necesita para
        // mostrar la pantalla de confirmación (whatsapp + mensaje custom)
        $payload = json_decode($response->getContent(), true);
        $payload['whatsapp_link']        = $campana->whatsapp_link ?: null;
        $payload['confirmation_message'] = $campana->confirmation_message ?: null;

        return response()->json($payload);
    }

    /**
     * GET /api/campanas/{id}/suscripcion   [auth:api]
     *
     * Indica si el usuario autenticado ya está inscripto en esta campaña.
     *
     * Respuesta:
     * { "inscripto": true|false }
     */
    public function suscripcion($id)
    {
        Campaign::where('activa', true)->findOrFail($id);

        $inscripto = Suscribe::where('campaign_id', $id)
            ->where('mail', auth()->user()->mail)
            ->exists();

        return response()->json(['inscripto' => $inscripto]);
    }
}
