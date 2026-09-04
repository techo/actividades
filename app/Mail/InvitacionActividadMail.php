<?php

namespace App\Mail;

use App\Mail\Concerns\HasMailLocale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Invitación por email a una actividad, con asunto y cuerpo de texto libre compuestos
 * por un admin desde el hub de comunicaciones. Análogo por email de la invitación push.
 *
 * El opt-in (`recibirMails`) y la existencia de `mail` ya vienen garantizados por la
 * segmentación (EnviarInvitacionActividad::segmento con canal 'email'); este Mailable
 * no reimplementa esa guarda.
 */
class InvitacionActividadMail extends Mailable
{
    use Queueable, SerializesModels, HasMailLocale;

    public $mailLocale;
    public $persona;
    public $actividad;
    public $titulo;
    public $mensaje;

    public function __construct($persona, $actividad, string $titulo, string $mensaje)
    {
        $this->persona    = $persona;
        $this->actividad  = $actividad;
        $this->titulo     = $titulo;
        $this->mensaje    = $mensaje;
        $this->mailLocale = optional($persona->pais)->locale ?? config('app.locale');
    }

    public function build()
    {
        return $this
            ->subject($this->titulo)
            ->from('noreply@techo.org', 'TECHO')
            ->view('emails.invitacionActividad');
    }
}
