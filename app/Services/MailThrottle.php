<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Reparte en el tiempo los envíos MASIVOS de mail (hub de comunicaciones) para no
 * superar los límites del proveedor (Gmail hoy) ni ahogar la cola de transaccionales.
 *
 * Cómo funciona: mantiene un cursor compartido en cache (persistente — driver database)
 * con el próximo "slot" de envío disponible. Cada mail masivo reserva un slot y avanza
 * el cursor un `espaciado` fijo. El espaciado uniforme (86400 / por_dia segundos)
 * garantiza <= por_dia en CUALQUIER ventana de 24h (rolling, como el límite de Gmail)
 * y reparte el envío parejo a lo largo del día — sin saturar a mitad de jornada.
 *
 * Los jobs lo usan con Mail::to(...)->later($slot, $mailable) en vez de ->queue().
 *
 * Alcance: solo email masivo. El transaccional (confirmaciones, recordatorios) NO pasa
 * por acá — debe salir rápido — y el push tampoco (no consume cupo del proveedor).
 *
 * Nota: el cursor es compartido entre las dos rutas masivas (invitación a actividad y
 * comunicación de campaña), así dos envíos concurrentes no se pisan el presupuesto.
 * Con 1 worker (config actual) la reserva es serial y no hay carrera; si algún día se
 * corren varios workers en paralelo, envolver el read-modify-write en Cache::lock.
 */
class MailThrottle
{
    /** Clave del cursor compartido (timestamp unix del próximo slot libre). */
    const CACHE_KEY = 'mail_hub_next_slot';

    /**
     * Reserva y devuelve el próximo horario de envío para un mail masivo, avanzando
     * el cursor para el siguiente. Si el cursor quedó en el pasado (o no existe), el
     * envío sale ya (ahora).
     */
    public static function siguienteSlot(): Carbon
    {
        $porDia    = max(1, (int) config('mailing.hub_por_dia'));
        $porMinuto = max(1, (int) config('mailing.hub_por_minuto'));

        // Espaciado (segundos) que respeta AMBOS topes a la vez: el diario y el por-minuto.
        $espaciado = (int) ceil(max(86400 / $porDia, 60 / $porMinuto));

        $ahora   = now()->timestamp;
        $cursor  = (int) Cache::get(self::CACHE_KEY, 0);
        $slotTs  = max($cursor, $ahora);

        // Avanza el cursor para el próximo mail. Retención amplia (60 días) para que no
        // expire en medio de un envío que se escalona a varios días.
        Cache::put(self::CACHE_KEY, $slotTs + $espaciado, now()->addDays(60));

        return Carbon::createFromTimestamp($slotTs);
    }

    /**
     * Días estimados para completar un envío de N destinatarios al ritmo diario actual.
     * Lo usa el preflight del controlador para avisar al admin antes de disparar.
     */
    public static function diasEstimados(int $destinatarios): int
    {
        $porDia = max(1, (int) config('mailing.hub_por_dia'));

        return (int) max(1, ceil($destinatarios / $porDia));
    }
}
