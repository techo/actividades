<?php

namespace App\Jobs;

use App\Services\OneSignal\OneSignalService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class EnviarNotificacionPush implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Intentos antes de mover a failed_jobs.
     * OneSignal puede tener indisponibilidades transitorias.
     */
    public $tries = 3;

    /**
     * Tiempo máximo de ejecución en segundos.
     */
    public $timeout = 60;

    protected $playerIds;
    protected $titulo;
    protected $mensaje;
    protected $datos;

    /**
     * @param  array   $playerIds  Lista de OneSignal player_ids destinatarios
     * @param  string  $titulo     Título de la notificación push
     * @param  string  $mensaje    Cuerpo de la notificación push
     * @param  array   $datos      Payload adicional para la app (tipo, id, etc.)
     *
     * Ejemplo de uso:
     *   EnviarNotificacionPush::dispatch(
     *       ['player-id-1', 'player-id-2'],
     *       'Tu inscripción fue confirmada',
     *       'Ya podés ver los detalles de tu actividad.',
     *       ['tipo' => 'inscripcion', 'idInscripcion' => 123]
     *   );
     */
    public function __construct(array $playerIds, string $titulo, string $mensaje, array $datos = [])
    {
        $this->playerIds = $playerIds;
        $this->titulo    = $titulo;
        $this->mensaje   = $mensaje;
        $this->datos     = $datos;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (empty($this->playerIds)) {
            Log::info('EnviarNotificacionPush: sin destinatarios, se omite el envío');
            return;
        }

        $service   = new OneSignalService();
        $resultado = $service->enviarAPlayerIds(
            $this->playerIds,
            $this->titulo,
            $this->mensaje,
            $this->datos
        );

        if (!$resultado['success']) {
            Log::error('EnviarNotificacionPush: fallo el envío', [
                'error'      => $resultado['error'] ?? 'desconocido',
                'titulo'     => $this->titulo,
                'player_ids' => $this->playerIds,
                'intento'    => $this->attempts(),
            ]);

            // Lanzar excepción para que el sistema de reintentos actúe.
            // Después de $this->tries intentos fallidos, el job pasa a failed_jobs.
            throw new \Exception(
                'Error al enviar notificación push: ' . ($resultado['error'] ?? 'desconocido')
            );
        }

        Log::info('EnviarNotificacionPush: enviado correctamente', [
            'titulo'      => $this->titulo,
            'recipients'  => $resultado['recipients'] ?? 0,
            'onesignal_id' => $resultado['id'] ?? null,
        ]);
    }

    /**
     * Handle a job failure.
     * Se ejecuta cuando se agotaron todos los reintentos.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        Log::critical('EnviarNotificacionPush: falló definitivamente tras ' . $this->tries . ' intentos', [
            'titulo'     => $this->titulo,
            'player_ids' => $this->playerIds,
            'error'      => $exception->getMessage(),
        ]);
    }
}
