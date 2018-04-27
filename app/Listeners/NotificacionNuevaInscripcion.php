<?php

namespace App\Listeners;

use App\Events\NuevaInscripcion;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotificacionNuevaInscripcion
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
     * @param  NuevaInscripcion  $event
     * @return void
     */
    public function handle(NuevaInscripcion $event)
    {
      $inscripcion = $event->inscripcion;
      Mail::to($persona->mail)->send(new MailConfimacionInscripcion($inscripcion));

    }
}
