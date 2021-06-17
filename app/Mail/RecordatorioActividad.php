<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RecordatorioActividad extends Mailable
{
    use Queueable, SerializesModels;

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
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('TECHO: ' . $this->inscripcion->actividad->nombreActividad . ' está por comenzar')
            ->from('noreply.actividades@techo.org')
            ->view('emails.recordatorioActividad');
    }
}
