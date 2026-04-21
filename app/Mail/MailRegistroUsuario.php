<?php

namespace App\Mail;

use App\Mail\Concerns\HasMailLocale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailRegistroUsuario extends Mailable
{
    use Queueable, SerializesModels, HasMailLocale;

    public $mailLocale;
    public $persona;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($persona)
    {
        $this->persona = $persona;
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
            ->subject(__('email.account_registration_title'))
            ->from('noreplyactividades@techo.org')
            ->view('emails.notificacionRegistroUsuario');
    }
}
