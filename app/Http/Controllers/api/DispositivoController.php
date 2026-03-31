<?php

namespace App\Http\Controllers\api;

use App\Dispositivo;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DispositivoController extends Controller
{
    /**
     * Registra o actualiza el dispositivo del usuario autenticado.
     *
     * Upsert por player_id:
     *   - Si el player_id no existe → lo crea asociado a este usuario.
     *   - Si el player_id ya existe (mismo u otro usuario) → lo reasigna
     *     a este usuario y lo reactiva. Cubre el caso de cambio de teléfono.
     *
     * La app mobile debe llamar este endpoint:
     *   - Al hacer login
     *   - Cuando el SDK de OneSignal entrega un nuevo player_id
     *
     * POST /api/dispositivos
     * Authorization: Bearer {token}
     * Body: { "player_id": "...", "plataforma": "android|ios" }
     */
    public function registrar(Request $request)
    {
        $request->validate([
            'player_id'  => 'required|string|max:255',
            'plataforma' => 'nullable|string|in:ios,android',
        ]);

        $persona = auth('api')->user();

        $dispositivo = Dispositivo::updateOrCreate(
            [
                'player_id' => $request->player_id,
            ],
            [
                'idPersona'  => $persona->idPersona,
                'plataforma' => $request->input('plataforma'),
                'activo'     => true,
            ]
        );

        return response()->json([
            'success'     => true,
            'dispositivo' => [
                'id'         => $dispositivo->idDispositivo,
                'player_id'  => $dispositivo->player_id,
                'plataforma' => $dispositivo->plataforma,
            ],
        ], 200);
    }

    /**
     * Desactiva el dispositivo al hacer logout en la app.
     *
     * Solo desactiva — no borra — para conservar historial.
     * Solo puede desactivar dispositivos propios.
     *
     * DELETE /api/dispositivos/{player_id}
     * Authorization: Bearer {token}
     */
    public function desactivar(Request $request, $playerId)
    {
        $persona = auth('api')->user();

        $dispositivo = Dispositivo::where('player_id', $playerId)
            ->where('idPersona', $persona->idPersona)
            ->first();

        if (!$dispositivo) {
            return response()->json([
                'success' => false,
                'message' => 'Dispositivo no encontrado',
            ], 404);
        }

        $dispositivo->update(['activo' => false]);

        return response()->json(['success' => true], 200);
    }
}
