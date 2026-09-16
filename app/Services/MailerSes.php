<?php

namespace App\Services;

use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Mail\Mailer;
use Swift_Mailer;
use Swift_SmtpTransport;

/**
 * Envía Mailables por Amazon SES usando credenciales DEDICADAS (MAIL_SES_*),
 * separadas del mailer default de Laravel (que en prod es Gmail, para el
 * transaccional).
 *
 * Por qué existe: el transaccional (confirmaciones, verificaciones) va por Gmail
 * —volumen bajo, entra en su límite—; el BULK (recordatorios, invitaciones,
 * evaluaciones, campañas) va por SES —cuota alta, no satura al proveedor del
 * transaccional—. Este servicio es el camino del bulk hacia SES.
 *
 * Remitente: los Mailables bulk usan config('mailing.from_bulk') =
 * noreply@actividades.techo.org (identidad verificada en SES). El alwaysFrom de
 * acá es solo fallback por si un Mailable no fija su from.
 *
 * Reutiliza app('events') para que sigan corriendo los listeners de mail
 * (RedirigirMailSandbox → redirect en sandbox, LogMailEnviado → mailstats,
 * ContarMailFallido → enviados_ok del hub).
 */
class MailerSes
{
    /**
     * Envío SINCRÓNICO por SES. Se llama desde jobs ya encolados (recordatorio)
     * o desde EnviarMailBulkSes (invitaciones/evaluaciones, con delay), así el
     * escalonado lo maneja la cola y acá solo mandamos uno.
     */
    public static function enviar(Mailable $mailable, string $to): void
    {
        $mailable->to($to);
        $mailable->send(self::mailer());
    }

    private static function mailer(): Mailer
    {
        $transport = new Swift_SmtpTransport(
            env('MAIL_SES_HOST', 'email-smtp.us-west-2.amazonaws.com'),
            (int) env('MAIL_SES_PORT', 587),
            env('MAIL_SES_ENCRYPTION', 'tls')
        );
        $transport->setUsername(env('MAIL_SES_USERNAME'));
        $transport->setPassword(env('MAIL_SES_PASSWORD'));

        $mailer = new Mailer(app('view'), new Swift_Mailer($transport), app('events'));
        $mailer->alwaysFrom(
            env('MAIL_SES_FROM', 'noreply@actividades.techo.org'),
            env('MAIL_FROM_NAME', 'TECHO')
        );

        return $mailer;
    }
}
