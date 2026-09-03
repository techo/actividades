<?php

namespace App\Mail;

use App\Mail\Concerns\HasMailLocale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

// OJO: NO implementar ShouldQueue acá. Este mailable se envía SIEMPRE con ->send()
// desde dentro de EnviarMailsCancelacionActividad (que ya es un job encolado), y ese
// job construye persona/actividad/pais con ::make() → instancias sin persistir (id nulo).
// Si el mailable fuera ShouldQueue, Mail::send() lo re-encolaría y SerializesModels
// re-buscaría esos modelos con firstOrFail(id=null) → ModelNotFoundException, haciendo
// fallar TODOS los mails de cancelación. Enviándolo sincrónico se usan los modelos en
// memoria y no hay re-serialización.
class CancelacionActividad extends Mailable
{
    use Queueable, SerializesModels, HasMailLocale;

    public $mailLocale;
    public $persona;
    public $actividad;
    public $pais;

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
        $this->mailLocale = optional($pais)->locale ?? config('app.locale');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject(__('email.activity_cancel_title') . ' ' . $this->actividad->nombreActividad)
            ->from('noreplyactividades@techo.org')
            ->view('emails.cancelacionActividad', [
                'persona'  => $this->persona,
                'actividad' => $this->actividad,
                'pais'     => $this->pais,
            ]);
    }
}
