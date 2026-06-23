<?php

namespace App\Services;

use App\Actividad;
use App\Inscripcion;
use Carbon\Carbon;

/**
 * Fuente única de verdad del estado de una inscripción.
 *
 * Antes la lógica estaba duplicada en Actividad::estadoInscripcion() (vocabulario
 * inglés, consumido por API mobile y frontend web) y Persona::estadoInscripcion()
 * (vocabulario español, consumido por el backoffice), con reglas que podían
 * discrepar en casos borde: la versión de Actividad comparaba por igualdad
 * (confirmacion == confirma) y marcaba "esperar confirmación" cuando un flag
 * estaba seteado sin que la actividad lo requiriera.
 *
 * Acá vive la regla robusta: ramifica por el REQUISITO de la actividad; si no
 * requiere confirmación/pago, ignora ese flag. Los modelos quedan como
 * adaptadores que traducen el estado canónico al vocabulario de cada consumidor.
 */
class EstadoInscripcion
{
    const CONFIRMED            = 'confirmed';
    const CONFIRM_BY_PAYING    = 'confirm_by_paying';
    const PAYMENT_DATE_CLOSED  = 'payment_date_closed';
    const WAITING_CONFIRMATION = 'waiting_confirmation';

    /**
     * Estado canónico de una inscripción dada su actividad.
     * Devuelve null si no hay inscripción.
     */
    public static function resolve(Actividad $actividad, ?Inscripcion $inscripcion)
    {
        if (!$inscripcion) {
            return null;
        }

        $requiereConfirmacion = (int) $actividad->confirmacion === 1;
        $requierePago         = (int) $actividad->pago === 1;
        $confirmo             = (bool) $inscripcion->confirma;
        $pago                 = (bool) $inscripcion->pago;

        if ($requiereConfirmacion && $requierePago) {
            if ($confirmo && $pago) {
                return self::CONFIRMED;
            }
            if (!$confirmo) {
                return self::WAITING_CONFIRMATION;
            }
            return self::resolvePagoPendiente($actividad);
        }

        if ($requiereConfirmacion) {
            return $confirmo ? self::CONFIRMED : self::WAITING_CONFIRMATION;
        }

        if ($requierePago) {
            return $pago ? self::CONFIRMED : self::resolvePagoPendiente($actividad);
        }

        // No requiere ni confirmación ni pago: confirmada por defecto.
        return self::CONFIRMED;
    }

    private static function resolvePagoPendiente(Actividad $actividad)
    {
        $limite = $actividad->fechaLimitePago;

        if (!$limite || $limite->greaterThan(Carbon::now())) {
            return self::CONFIRM_BY_PAYING;
        }

        return self::PAYMENT_DATE_CLOSED;
    }

    /** Vocabulario consumido por API mobile y frontend web (ActividadResource, tarjeta.vue). */
    public static function toEnglish($estado)
    {
        $map = [
            self::CONFIRMED            => 'confirmed',
            self::CONFIRM_BY_PAYING    => 'confirm_by_paying',
            self::PAYMENT_DATE_CLOSED  => 'confirmation_date_is_closed',
            self::WAITING_CONFIRMATION => 'waiting_for_confirmation',
        ];

        return $estado ? $map[$estado] : false;
    }

    /** Vocabulario consumido por el backoffice. */
    public static function toSpanish($estado)
    {
        $map = [
            self::CONFIRMED            => 'CONFIRMADO',
            self::CONFIRM_BY_PAYING    => 'CONFIRMAR PARTICIPACIÓN',
            self::PAYMENT_DATE_CLOSED  => 'FECHA DE CONFIRMACIÓN VENCIDA',
            self::WAITING_CONFIRMATION => 'ESPERAR CONFIRMACIÓN',
        ];

        return $estado ? $map[$estado] : false;
    }
}
