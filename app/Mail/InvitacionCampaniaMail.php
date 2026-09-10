<?php

namespace App\Mail;

use App\Mail\Concerns\HasMailLocale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Email de una comunicación cuyo objetivo es una campaña. El destinatario puede ser una
 * Persona (voluntario) o un suscripto/lead de campaña; por eso recibe el `nombre` como
 * string (no un modelo). El CTA apunta a la URL pública de la campaña
 * (/{abreviacion}/campania/{id}), que se arma con el país recibido.
 */
class InvitacionCampaniaMail extends Mailable
{
    use Queueable, SerializesModels, HasMailLocale;

    public $mailLocale;
    public $nombre;
    public $campaign;
    public $pais;
    public $titulo;
    public $mensaje;

    /** Persona destinataria si es un voluntario (habilita el link de baja del footer);
     *  null cuando el destinatario es un suscripto/lead de campaña sin cuenta. */
    public $persona;

    /** id de la Comunicacion, para instrumentar enviados_ok/error en el historial. */
    public $comunicacionId;

    public function __construct($nombre, $campaign, $pais, string $titulo, string $mensaje, $persona = null, $comunicacionId = null)
    {
        $this->nombre         = $nombre;
        $this->campaign       = $campaign;
        $this->pais           = $pais;
        $this->titulo         = $titulo;
        $this->mensaje        = $mensaje;
        $this->persona        = $persona;
        $this->comunicacionId = $comunicacionId;
        $this->mailLocale     = optional($pais)->locale ?? config('app.locale');
    }

    public function build()
    {
        return $this
            ->subject($this->titulo)
            ->from(config('mail.from.address'), __('email.remitente'))
            ->withSwiftMessage(function ($message) {
                if ($this->comunicacionId) {
                    $message->getHeaders()->addTextHeader('X-Comunicacion-Id', (string) $this->comunicacionId);
                }
            })
            ->view('emails.invitacionCampania');
    }
}
