<?php

namespace App\Mail;

use App\Actividad;
use App\Persona;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InvitacionEvaluacion extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;
    public $persona;
    public $actividad;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Persona $persona, Actividad $actividad)
    {
        $this->persona = $persona;
        $this->actividad = $actividad;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject( __('email.evaluation_title') . ' ' . $this->actividad->nombreActividad)
            ->from('no-reply@techo.org')
            ->view('emails.invitacionEvaluacion');
    }
}
