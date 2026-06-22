<?php

namespace App\Services\Push;

use App\Jobs\EnviarNotificacionPush;
use App\Persona;
use App\Services\OneSignal\OneSignalService;
use Illuminate\Support\Facades\App;
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
    /**
     * Envía una notificación push resolviendo los textos en el idioma del usuario.
     * Los parámetros $tituloKey y $mensajeKey son claves de traducción del archivo push.php.
     */
    public function enviarLocalizado(Persona $persona, string $tituloKey, string $mensajeKey, array $params = [], array $datos = []): void
    {
        // Guardamos el locale real previo. No usamos config('app.locale') para
        // restaurar porque App::setLocale() también sobreescribe esa config, y
        // leerla después devolvería el locale ya cambiado (fuga al resto del request).
        $localeAnterior = App::getLocale();
        $locale = optional($persona->pais)->locale ?? config('app.locale');
        App::setLocale($locale);
        $titulo  = __($tituloKey, $params);
        $mensaje = __($mensajeKey, $params);
        App::setLocale($localeAnterior);
        $this->enviar($persona, $titulo, $mensaje, $datos);
    }

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
