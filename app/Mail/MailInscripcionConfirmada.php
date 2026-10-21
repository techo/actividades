<?php

namespace App\Mail;

use App\Mail\Concerns\HasMailLocale;
use App\Services\MailerSes;
use Illuminate\Contracts\Mail\Mailer as MailerContract;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

/**
 * Sale por SES, no por el mailer default (Gmail), aunque se encole con Mail::queue():
 * el admin confirma/marca pagos de a muchos y Gmail corta la ráfaga (prod 2026-10-19:
 * 141 confirmaciones fallaron en un minuto). Se resuelve acá, en send(), para cubrir
 * todos los puntos de envío (backoffice, Stripe, PayU, pagos) sin tocar cada uno.
 * Por eso el from es la identidad verificada en SES (mailing.from_bulk).
 */
class MailInscripcionConfirmada extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    use HasMailLocale { send as protected sendConLocale; }

    public $mailLocale;
    public $inscripcion;
    public $persona;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($inscripcion)
    {
        $this->inscripcion = $inscripcion;
        $this->persona = $inscripcion->persona;
        $this->mailLocale = optional($inscripcion->persona->pais)->locale ?? config('app.locale');
    }

    public function send(MailerContract $mailer)
    {
        return $this->sendConLocale(MailerSes::configurado() ? MailerSes::mailer() : $mailer);
    }

    public function build()
    {
        $url = config('app.url')
            . '/admin/actividades/' . $this->inscripcion->idActividad
            . '/inscripcion/' . $this->inscripcion->idInscripcion
            . '/persona/' . $this->inscripcion->persona->idPersona;

        // PNG crudo del QR. Se embebe como adjunto inline (CID) en la vista con
        // $message->embedData(), porque Gmail (y muchos clientes) NO renderizan
        // imágenes data:URI en los mails (se veían rotas).
        $qrCode = (string) QrCode::format('png')->size(200)->generate($url);

        return $this
            ->subject(__('email.inscription_confirmed_title') . ' ' . $this->inscripcion->actividad->nombreActividad)
            ->from(config('mailing.from_bulk'), __('email.remitente'))
            ->view('emails.inscripcionConfirmada')
            ->with([
                'inscripcion' => $this->inscripcion,
                'persona'     => $this->persona,
                'qrCode'      => $qrCode,
            ]);
    }
}
