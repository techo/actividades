<?php

namespace App\Console\Commands;

use App\Persona;
use App\Services\Push\PushNotificationService;
use Illuminate\Console\Command;

class NotificarReactivacionVoluntarios extends Command
{
    protected $signature = 'push:reactivacion-voluntarios';

    protected $description = 'Notifica a voluntarios que llevan más de 30 días sin inscribirse a ninguna actividad';

    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        parent::__construct();
        $this->pushService = $pushService;
    }

    public function handle()
    {
        Persona::where('recibir_push', true)
            ->whereHas('dispositivos', fn($q) => $q->where('activo', true))
            ->whereDoesntHave('inscripciones', fn($q) => $q->where('fechaInscripcion', '>=', now()->subDays(30)))
            ->with('pais')
            ->chunk(100, function ($personas) {
                foreach ($personas as $persona) {
                    $this->pushService->enviarLocalizado(
                        $persona,
                        'push.reactivacion_titulo',
                        'push.reactivacion_cuerpo',
                        [],
                        ['tipo' => 'reactivacion']
                    );
                }
            });
    }
}
