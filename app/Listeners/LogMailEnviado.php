<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Log;

/**
 * Deja una línea en el canal 'mailstats' por cada mail efectivamente enviado.
 *
 * Motivo: Laravel no registra los envíos exitosos, así que no había forma de
 * calcular la TASA de rechazos (failed_jobs / enviados). Con esto, el numerador
 * (fallas) sale de failed_jobs y el denominador (enviados) de este log:
 *
 *   enviados_hoy = grep -c SENT storage/logs/mailstats-YYYY-MM-DD.log
 *   tasa = fallas / (enviados + fallas)
 *
 * El listener corre en el proceso del worker (donde se envían los mails encolados).
 * Es sincrónico y liviano (una línea de log); no implementa ShouldQueue a propósito.
 */
class LogMailEnviado
{
    public function handle(MessageSent $event)
    {
        // El logueo de métricas es best-effort y NUNCA debe romper el envío: el mail
        // YA se envió cuando dispara MessageSent, así que si el archivo de log no se
        // puede escribir (permisos, disco), se traga el error. Si no, el job fallaría
        // y se reintentaría → reenviando el mail (duplicados).
        try {
            $to = array_keys((array) $event->message->getTo());

            Log::channel('mailstats')->info('SENT', [
                'to'      => $to[0] ?? null,
                'subject' => $event->message->getSubject(),
            ]);
        } catch (\Throwable $e) {
            // no-op
        }
    }
}
