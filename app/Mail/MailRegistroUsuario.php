<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MailRegistroUsuario extends Mailable
{
    use Queueable, SerializesModels;

    public $persona;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($persona)
    {
        $this->persona = $persona;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject(__('email.account_registration_title'))
            ->from('noreply.actividades@techo.org')
            ->view('emails.notificacionRegistroUsuario');
    }
}
