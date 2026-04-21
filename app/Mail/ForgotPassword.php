<?php

namespace App\Mail;

use App\Mail\Concerns\HasMailLocale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ForgotPassword extends Mailable
{
    use Queueable, SerializesModels, HasMailLocale;

    public $mailLocale;
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
            ->subject(__('email.forgot_password_title'))
            ->from('noreplyactividades@techo.org')
            ->view('emails.forgot-password');
    }
}
