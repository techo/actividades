<?php

namespace App\Console\Commands;

use App\Actividad;
use App\Services\Push\PushNotificationService;
use Illuminate\Console\Command;

class NotificarAperturaEvaluacion extends Command
{
    protected $signature = 'push:apertura-evaluacion';

    protected $description = 'Notifica a los inscriptos de actividades cuyo período de evaluación abre hoy';

    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        parent::__construct();
        $this->pushService = $pushService;
    }

    public function handle()
    {
        Actividad::whereDate('fechaInicioEvaluaciones', today())
            ->with(['inscripciones' => function ($q) {
                $q->where('presente', 1)->with(['persona.pais', 'persona.dispositivos']);
            }])
            ->chunk(50, function ($actividades) {
                foreach ($actividades as $actividad) {
                    foreach ($actividad->inscripciones as $inscripcion) {
                        $this->pushService->enviarLocalizado(
                            $inscripcion->persona,
                            'push.apertura_evaluacion_titulo',
                            'push.apertura_evaluacion_cuerpo',
                            ['actividad' => $actividad->nombreActividad],
                            ['tipo' => 'evaluacion', 'estado' => 'ABIERTA', 'idActividad' => $actividad->idActividad]
                        );
                    }
                }
            });
    }
}
