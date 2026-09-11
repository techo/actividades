<?php

namespace App\Jobs;

use App\Services\MailerSes;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Envía un Mailable BULK por SES (mailer dedicado MAIL_SES_*). Se despacha con
 * ->delay() para respetar el escalonado (MailThrottle). Lo usan las invitaciones
 * de actividad/campaña y las invitaciones a evaluación, que se reparten en el
 * tiempo. El recordatorio no lo necesita (ya es un job propio y llama a MailerSes
 * directo).
 */
class EnviarMailBulkSes implements ShouldQueue
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
        // Respeta la pausa también al procesar (por si el job se encoló antes de pausar).
        if (config('mailing.bulk_pausado')) {
            return;
        }

        MailerSes::enviar($this->mailable, $this->to);
    }
}
