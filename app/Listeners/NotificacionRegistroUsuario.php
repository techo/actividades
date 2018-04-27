<?php

namespace App\Listeners;

use App\Events\RegistroUsuario;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mail;
use App\Mail\MailRegistroUsuario;

class NotificacionRegistroUsuario
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  RegistroUsuario  $event
     * @return void
     */
    public function handle(RegistroUsuario $event)
    {
      $persona = $event->usuario;
      Mail::to($persona->mail)->send(new MailRegistroUsuario($persona));

    }
}
