<?php

namespace App\Listeners;

use App\Comunicacion;
use Illuminate\Queue\Events\JobFailed;

/**
 * Suma a `comunicaciones.enviados_error` cuando falla el envío de un mail del hub
 * (invitación a actividad o comunicación de campaña). Es la contraparte de
 * LogMailEnviado (que cuenta los ok vía MessageSent).
 *
 * El id de la comunicación viaja como propiedad `comunicacionId` del mailable, que
 * queda serializado dentro del job encolado. Se extrae con regex del payload SIN
 * des-serializar, para no recargar modelos (que podrían no existir ya) al procesar
 * una falla.
 */
class ContarMailFallido
{
    public function handle(JobFailed $event): void
    {
        try {
            $payload = $event->job->payload();
            $cmd = $payload['data']['command'] ?? '';

            $esMailHub = strpos($cmd, 'InvitacionActividadMail') !== false
                || strpos($cmd, 'InvitacionCampaniaMail') !== false;

            if ($esMailHub && preg_match('/comunicacionId";i:(\d+);/', $cmd, $m)) {
                Comunicacion::where('id', (int) $m[1])->increment('enviados_error');
            }
        } catch (\Throwable $e) {
            // best-effort: nunca romper el manejo de fallas de la cola.
        }
    }
}
