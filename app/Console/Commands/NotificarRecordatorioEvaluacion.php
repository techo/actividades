<?php

namespace App\Console\Commands;

use App\Actividad;
use App\Services\Push\PushNotificationService;
use Illuminate\Console\Command;

class NotificarRecordatorioEvaluacion extends Command
{
    protected $signature = 'push:recordatorio-evaluacion';

    protected $description = 'Notifica a los inscriptos de actividades cuyo período de evaluación termina mañana';

    protected $pushService;

    public function __construct(PushNotificationService $pushService)
    {
        parent::__construct();
        $this->pushService = $pushService;
    }

    public function handle()
    {
        Actividad::whereDate('fechaFinEvaluaciones', today()->addDay())
            ->with(['inscripciones' => function ($q) {
                $q->where('presente', 1)->with(['persona.pais', 'persona.dispositivos']);
            }])
            ->chunk(50, function ($actividades) {
                foreach ($actividades as $actividad) {
                    foreach ($actividad->inscripciones as $inscripcion) {
                        $this->pushService->enviarLocalizado(
                            $inscripcion->persona,
                            'push.recordatorio_evaluacion_titulo',
                            'push.recordatorio_evaluacion_cuerpo',
                            ['actividad' => $actividad->nombreActividad],
                            ['tipo' => 'evaluacion', 'estado' => 'POR_CERRAR', 'idActividad' => $actividad->idActividad]
                        );
                    }
                }
            });
    }
}
