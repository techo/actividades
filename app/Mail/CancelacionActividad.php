<?php

namespace App\Mail;

use App\Mail\Concerns\HasMailLocale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CancelacionActividad extends Mailable implements ShouldQueue
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
