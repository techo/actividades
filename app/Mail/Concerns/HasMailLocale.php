<?php

namespace App\Mail\Concerns;

/**
 * Trait HasMailLocale
 *
 * Fixes locale loss during queue serialization. Laravel's SerializesModels
 * only serializes properties declared in the concrete class, not inherited
 * ones (like $locale from Mailable). This trait overrides send() to transfer
 * $mailLocale (declared in each concrete class) back to $this->locale before
 * Mailable::send() runs withLocale().
 *
 * Each class using this trait MUST declare: public $mailLocale;
 */
trait HasMailLocale
{
    public function send(\Illuminate\Contracts\Mail\Mailer $mailer)
    {
        if (!empty($this->mailLocale)) {
            $this->locale($this->mailLocale);
        }

        return parent::send($mailer);
    }
}
