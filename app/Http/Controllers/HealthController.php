<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

/**
 * Health-check para uptime monitoring y deploys.
 *
 * La ruta se registra FUERA de los grupos `web`/`api` (ver RouteServiceProvider::mapHealthRoutes)
 * para que sea liviana: sin sesión, sin CSRF, sin SeleccionarPais (que pega a BD) y sin locale.
 * No requiere autenticación. Verifica la app y la conexión a BD.
 *
 * El endpoint nunca debe tirar 500: eso sería una mala señal para el monitor. Toda la
 * verificación va dentro de try/catch y responde 503 ante un problema de infraestructura.
 */
class HealthController extends Controller
{
    public function check()
    {
        $database = 'ok';
        $healthy = true;

        try {
            // getPdo() abre/valida la conexión; el select 1 confirma que responde consultas.
            DB::connection()->getPdo();
            DB::select('select 1');
        } catch (\Throwable $e) {
            $database = 'error';
            $healthy = false;
        }

        $payload = [
            'status'   => $healthy ? 'ok' : 'error',
            'app'      => config('app.name'),
            'database' => $database,
        ];

        return response()
            ->json($payload, $healthy ? 200 : 503)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
