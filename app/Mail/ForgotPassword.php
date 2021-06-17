<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ForgotPassword extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $persona;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($token, $persona)
    {
        $this->token = $token;
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
            ->subject(__('email.forgot_password_title'))
            ->from('noreply.actividades@techo.org')
            ->view('emails.forgot-password');
    }
}
