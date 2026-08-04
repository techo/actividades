<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Agrega cabeceras de seguridad a las respuestas (defensa en profundidad).
 *
 * Nota sobre CSP: la app usa scripts/estilos inline, así que una CSP estricta
 * con nonces requiere una migración aparte (Fase 3). Acá se aplica una CSP
 * mínima NO disruptiva —frame-ancestors/object-src/base-uri— que mitiga
 * clickjacking, plugins e inyección de <base>, sin restringir la carga de JS/CSS.
 */
class SecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Algunas respuestas (streams, descargas de archivos) no aceptan headers via ->header().
        if (!method_exists($response, 'header')) {
            return $response;
        }

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN', false);
        $response->headers->set('X-Content-Type-Options', 'nosniff', false);
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin', false);
        $response->headers->set('X-XSS-Protection', '0', false); // desactivado a propósito (el filtro legacy es inseguro)

        $response->headers->set(
            'Content-Security-Policy',
            "frame-ancestors 'self'; object-src 'none'; base-uri 'self'",
            false
        );

        // HSTS solo bajo HTTPS (producción), para no romper dev local por HTTP.
        if ($request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains', false);
        }

        return $response;
    }
}
