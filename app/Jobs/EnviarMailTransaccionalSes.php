<?php

namespace App\Jobs;

use App\Services\MailerSes;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Envía un Mailable TRANSACCIONAL por SES (mailer dedicado MAIL_SES_*).
 *
 * Igual que App\Jobs\EnviarMailBulkSes usa el canal SES (App\Services\MailerSes),
 * PERO sin el guard de `bulk_pausado`: este es tránsito 1:1 (ej. aviso de respuesta a
 * un reporte), no un envío masivo, así que la pausa de emergencia del bulk no lo frena.
 *
 * Lo encolamos para no sumar la latencia del SMTP a la request del admin. Al ir por
 * MailerSes se mantienen los listeners de mail (redirección en sandbox + mailstats).
 * El Mailable DEBE fijar su from a una identidad verificada en SES (config('mailing.from_bulk')).
 */
class EnviarMailTransaccionalSes implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    /** @var \Illuminate\Contracts\Mail\Mailable */
    public $mailable;

    /** @var string */
    public $to;

    public function __construct(Mailable $mailable, string $to)
    {
        $this->mailable = $mailable;
        $this->to = $to;
    }

    public function handle()
    {
        MailerSes::enviar($this->mailable, $this->to);
    }
}
