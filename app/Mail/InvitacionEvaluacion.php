<?php

namespace App\Mail;

use App\Actividad;
use App\Mail\Concerns\HasMailLocale;
use App\Persona;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InvitacionEvaluacion extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, HasMailLocale;

    public $mailLocale;
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
        $this->mailLocale = optional($persona->pais)->locale ?? config('app.locale');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject(__('email.evaluation_title') . ' ' . $this->actividad->nombreActividad)
            ->from('noreplyactividades@techo.org')
            ->view('emails.invitacionEvaluacion');
    }
}
