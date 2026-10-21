<?php

namespace App\Console\Commands;

use App\Inscripcion;
use App\Services\EstadoInscripcion;
use App\Services\Salesforce\SocioExencionService;
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
        $exencion = app(SocioExencionService::class);

        // get() y no chunk(): reevaluar() puede guardar (toca updated_at, que es el
        // filtro de la ventana) y con chunk por offset se saltearían filas. La
        // ventana es de 2 horas, el volumen es chico.
        $inscripciones = Inscripcion::where('confirma', 1)
            ->where('pago', 0)
            ->where(function ($q) {
                $q->where('exento_pago', 0)->orWhereNull('exento_pago');
            })
            ->whereHas('actividad', function ($q) {
                $q->where('pago', 1);
            })
            ->whereBetween('updated_at', [now()->subHours(49), now()->subHours(47)])
            ->with(['persona.pais', 'persona.dispositivos', 'actividad'])
            ->get();

        foreach ($inscripciones as $inscripcion) {
            // Fuente de verdad: solo a quien realmente está "confirmar pagando".
            if (EstadoInscripcion::resolve($inscripcion->actividad, $inscripcion) !== EstadoInscripcion::CONFIRM_BY_PAYING) {
                continue;
            }

            // Si hoy es socio, no se le pide pagar (queda exento).
            if ($exencion->reevaluar($inscripcion)) {
                continue;
            }

            $this->pushService->enviarLocalizado(
                $inscripcion->persona,
                'push.recordatorio_pago_titulo',
                'push.recordatorio_pago_cuerpo',
                ['actividad' => $inscripcion->actividad->nombreActividad],
                ['tipo' => 'inscripcion', 'estado' => 'RECORDATORIO_PAGO', 'idActividad' => $inscripcion->actividad->idActividad]
            );
        }
    }
}
