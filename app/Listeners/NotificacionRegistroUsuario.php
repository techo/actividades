<?php

namespace App\Listeners;

use App\Events\RegistroUsuario;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
      $persona->notify(new \App\Notifications\RegistroUsuario($persona));

    }
}
