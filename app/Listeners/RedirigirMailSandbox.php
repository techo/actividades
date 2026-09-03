<?php

namespace App\Listeners;

use Illuminate\Mail\Events\MessageSending;

/**
 * Red de seguridad para SANDBOX: si `mailing.redirect_to` (env MAIL_REDIRECT_TO) está
 * seteada, redirige TODO el mail saliente a esa casilla en vez de al destinatario real.
 *
 * Motivo: la base de sandbox es copia de prod y tiene emails reales de voluntarios, así
 * que un envío masivo de prueba le escribiría a gente real. Con esto, cualquier mail
 * (masivo o transaccional) cae solo en la casilla de prueba; el destinatario real queda
 * marcado en el asunto para poder verificar a quién habría ido.
 *
 * En PRODUCCIÓN la variable debe estar VACÍA → el listener no toca nada.
 */
class RedirigirMailSandbox
{
    public function handle(MessageSending $event): void
    {
        $redirect = config('mailing.redirect_to');

        if (empty($redirect)) {
            return; // Producción (o sin override): no se toca el mail.
        }

        $message = $event->message;

        // Destinatarios reales (to + cc + bcc), para dejarlos visibles en el asunto.
        $reales = array_merge(
            array_keys((array) $message->getTo()),
            array_keys((array) $message->getCc()),
            array_keys((array) $message->getBcc())
        );

        // Redirige todo a la casilla de prueba y limpia cc/bcc para no filtrar nada.
        $message->setTo([$redirect]);
        $message->setCc([]);
        $message->setBcc([]);

        $marca = '[SANDBOX→' . implode(',', $reales) . '] ';
        $message->setSubject($marca . (string) $message->getSubject());
    }
}
