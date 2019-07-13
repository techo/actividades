<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CancelacionActividad extends Mailable implements ShouldQueue
{
    use Queueable;

    public $inscripcion;
    public $persona;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($persona, $actividad, $pais)
    {
        $this->persona = $persona;
        $this->actividad = $actividad;
        $this->pais = $pais;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('TECHO: ' . $this->actividad->nombreActividad . ' fue cancelada')
            ->from('no-reply@techo.org')
            ->view('emails.cancelacionActividad',['persona' => $this->persona, 'actividad' => $this->actividad, 'pais' => $this->pais]);
    }
}
