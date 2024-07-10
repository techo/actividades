<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MailInscripcionFaltaPago extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $inscripcion;
    public $persona;
    public $actividad;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($inscripcion)
    {
        $this->inscripcion = $inscripcion;
        $this->persona = $inscripcion->persona;
        $this->actividad = $inscripcion->actividad;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject(__('email.missing_payment_title') . ' ' . $this->inscripcion->actividad->nombreActividad)
            ->from('noreply.actividades@techo.org')
            ->view('emails.InscripcionFaltaPago');
    }
}
