<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

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
        $this->locale = optional($inscripcion->persona->pais)->locale ?? config('app.locale');
        Log::info('[MailInscripcionFaltaPago] locale seteado: ' . $this->locale . ' | pais: ' . optional($inscripcion->persona->pais)->nombre);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        Log::info('[MailInscripcionFaltaPago] build() ejecutado | $this->locale: ' . $this->locale . ' | App::getLocale(): ' . \App::getLocale() . ' | traduccion: ' . __('email.missing_payment_1'));

        return $this
            ->subject(__('email.missing_payment_title') . ' ' . $this->inscripcion->actividad->nombreActividad)
            ->from('noreplyactividades@techo.org')
            ->view('emails.InscripcionFaltaPago');
    }
}
