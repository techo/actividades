<?php

namespace App\Console\Commands;

use App\Inscripcion;
use App\Services\Push\PushNotificationService;
use Illuminate\Console\Command;

class NotificarRecordatorioPago extends Command
{
    protected $signature = 'push:recordatorio-pago';

    protected $description = 'Notifica a voluntarios con pago pendiente ~48hs después de haber sido confirmados';

    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        parent::__construct();
        $this->pushService = $pushService;
    }

    public function handle()
    {
        Inscripcion::where('confirma', 1)
            ->where('pago', 0)
            ->whereBetween('updated_at', [now()->subHours(49), now()->subHours(47)])
            ->with(['persona.pais', 'persona.dispositivos', 'actividad'])
            ->chunk(100, function ($inscripciones) {
                foreach ($inscripciones as $inscripcion) {
                    $this->pushService->enviarLocalizado(
                        $inscripcion->persona,
                        'push.recordatorio_pago_titulo',
                        'push.recordatorio_pago_cuerpo',
                        ['actividad' => $inscripcion->actividad->nombreActividad],
                        ['tipo' => 'inscripcion', 'estado' => 'RECORDATORIO_PAGO', 'idActividad' => $inscripcion->actividad->idActividad]
                    );
                }
            });
    }
}
