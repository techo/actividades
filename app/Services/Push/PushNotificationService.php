<?php

namespace App\Services\Push;

use App\Jobs\EnviarNotificacionPush;
use App\Persona;
use App\Services\OneSignal\OneSignalService;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    protected $oneSignal;

    public function __construct(OneSignalService $oneSignal)
    {
        $this->oneSignal = $oneSignal;
    }

    /**
     * Despacha una notificación push al usuario si tiene dispositivos activos
     * y no desactivó las notificaciones push.
     *
     * Nunca lanza excepciones hacia afuera — cualquier fallo se loguea
     * para no interrumpir el flujo de negocio del llamador.
     *
     * Ejemplo de uso:
     *   $this->pushService->enviar(
     *       $persona,
     *       '¡Inscripción confirmada!',
     *       'Ya estás anotado en "Actividad X". ¡Nos vemos!',
     *       ['tipo' => 'inscripcion', 'estado' => 'CONFIRMADO', 'idActividad' => 42]
     *   );
     */
    public function enviar(Persona $persona, string $titulo, string $mensaje, array $datos = []): void
    {
        try {
            if (!$persona->recibir_push) {
                return;
            }

            $playerIds = $persona->dispositivos()
                ->where('activo', true)
                ->pluck('player_id')
                ->toArray();

            if (empty($playerIds)) {
                return;
            }

            EnviarNotificacionPush::dispatch(
                $playerIds,
                $titulo,
                $mensaje,
                $datos,
                ['idPersona' => $persona->idPersona]
            );

        } catch (\Exception $e) {
            Log::warning('PushNotificationService: error al encolar notificación', [
                'idPersona' => $persona->idPersona ?? null,
                'titulo'    => $titulo,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}
