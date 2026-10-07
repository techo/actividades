<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;

/**
 * 403 cuyo mensaje es seguro y útil para mostrarle al usuario final (ya traducido).
 *
 * La pantalla errors/403 muestra el mensaje SOLO para esta excepción: el resto de los 403
 * siguen con el texto genérico (sus mensajes pueden ser internos o estar en inglés).
 */
class AccesoExplicadoException extends AuthorizationException
{
}
