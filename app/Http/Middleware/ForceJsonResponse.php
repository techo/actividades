<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Fuerza que las rutas de la API (grupo `api`, routes/api.php) se traten como
 * "espera JSON". Sin esto, un error de validación/auth/404 en un request sin el
 * header `Accept: application/json` lo renderiza Laravel como REDIRECT 302 (HTML)
 * en vez de un 422/401/404 JSON con {message, errors}, y la app móvil no ve el
 * mensaje (p.ej. "DNI inválido" o "Debés tener al menos 13 años"). Con el Accept
 * forzado acá, Laravel siempre responde JSON estructurado, sin depender de que el
 * cliente mande el header correcto.
 */
class ForceJsonResponse
{
    public function handle($request, Closure $next)
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
