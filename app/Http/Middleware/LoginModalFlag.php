<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Comparte con las vistas el flag `requiere_auth` (0/1) para que el layout decida
 * si mostrar el modal de login. NO autentica ni bloquea: SIEMPRE llama a $next().
 *
 * La protección real de una ruta la da el middleware `auth`; este solo alimenta la
 * vista. Antes se llamaba `requiere.auth`, un nombre que hacía creer que exigía
 * login (finding A-8 de la auditoría 2026): las rutas que dependían solo de él
 * quedaban abiertas a invitados (p.ej. un GET a /estado tiraba 500 al usar
 * Auth::user() null). Renombrado a `login-modal-flag` para que no vuelva a pasar.
 */
class LoginModalFlag
{
    public function handle($request, Closure $next)
    {
        view()->share('requiere_auth', Auth::check() ? 0 : 1);

        return $next($request);
    }
}
