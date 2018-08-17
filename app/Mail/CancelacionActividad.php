<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CancelacionActividad extends Mailable
{
    use Queueable, SerializesModels;

    public $inscripcion;
    public $persona;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($inscripcion, $persona)
    {
        $this->inscripcion = $inscripcion;
        $this->persona = $inscripcion->persona;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('TECHO: ' . $this->inscripcion->actividad->nombreActividad . ' fue cancelada')
            ->from('no-reply@techo.org')
            ->view('emails.cancelacionActividad');
    }
}
