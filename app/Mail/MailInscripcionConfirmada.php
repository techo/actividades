<?php

namespace App\Mail;

use App\Mail\Concerns\HasMailLocale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MailInscripcionConfirmada extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, HasMailLocale;

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
            ->from(config('mail.from.address'), __('email.remitente'))
            ->view('emails.inscripcionConfirmada')
            ->with([
                'inscripcion' => $this->inscripcion,
                'persona'     => $this->persona,
                'qrCode'      => $qrCode,
            ]);
    }
}
