<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\RegistroUsuario' => [
            'App\Listeners\NotificacionRegistroUsuario',
        ],
        'App\Events\NuevaInscripcion' => [
            'App\Listeners\NotificacionNuevaInscripcion',
        ],
        // Cuenta los mails enviados (denominador de la tasa de rechazos). Ver LogMailEnviado.
        'Illuminate\Mail\Events\MessageSent' => [
            'App\Listeners\LogMailEnviado',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
