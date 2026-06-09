<?php

namespace App\Console\Commands;

use App\Dispositivo;
use App\Persona;
use App\Services\OneSignal\OneSignalService;
use App\Services\Push\PushNotificationService;
use Illuminate\Console\Command;

class TestPushNotificacion extends Command
{
    protected $signature = 'push:test {idPersona} {--notificacion=inscripcion_confirmada}';

    protected $description = 'Prueba el envío de una notificación push a un usuario específico. Solo para desarrollo.';

    protected $pushService;
    protected $oneSignal;

    public function __construct(PushNotificationService $pushService, OneSignalService $oneSignal)
    {
        parent::__construct();
        $this->pushService = $pushService;
        $this->oneSignal   = $oneSignal;
    }

    public function handle()
    {
        $idPersona    = $this->argument('idPersona');
        $notificacion = $this->option('notificacion');

        $this->line('');
        $this->info("=== PUSH TEST — persona #{$idPersona} ===");
        $this->line('');

        // ── 1. Persona ──────────────────────────────────────────────────
        $persona = Persona::with('pais')->find($idPersona);

        if (!$persona) {
            $this->error("✗ Persona #{$idPersona} no existe.");
            return 1;
        }

        $this->info("✓ Persona encontrada: {$persona->nombres} {$persona->apellidoPaterno} ({$persona->mail})");

        // ── 2. recibir_push ─────────────────────────────────────────────
        if (!$persona->recibir_push) {
            $this->error("✗ recibir_push = false  →  el usuario tiene las notificaciones desactivadas.");
            $this->line("  Solucion: UPDATE Persona SET recibir_push = 1 WHERE idPersona = {$idPersona};");
            return 1;
        }

        $this->info("✓ recibir_push = true");

        // ── 3. Dispositivos ─────────────────────────────────────────────
        $dispositivos = $persona->dispositivos()->where('activo', true)->get();

        if ($dispositivos->isEmpty()) {
            $this->error("✗ No hay dispositivos activos para esta persona.");
            $this->line("  Dispositivos inactivos: " . $persona->dispositivos()->where('activo', false)->count());
            $this->line("  Solucion: la app debe llamar a POST /api/dispositivos al loguearse.");
            return 1;
        }

        $this->info("✓ Dispositivos activos: {$dispositivos->count()}");
        foreach ($dispositivos as $d) {
            $this->line("   [{$d->plataforma}] {$d->player_id}  (updated: {$d->updated_at})");
        }

        // ── 4. Credenciales OneSignal ───────────────────────────────────
        $appId  = config('services.onesignal.app_id');
        $apiKey = config('services.onesignal.api_key');
        $env    = config('app.env');

        $this->line('');
        $this->info("✓ Entorno: {$env}");

        if (empty($appId)) {
            $this->error("✗ ONESIGNAL_APP_ID" . ($env !== 'production' ? "_DEV" : "") . " no está definido en .env");
            return 1;
        }

        $this->info("✓ app_id:  {$appId}");
        $this->info("✓ api_key: " . ($apiKey ? substr($apiKey, 0, 8) . '...' : 'NO DEFINIDA'));

        if (empty($apiKey)) {
            $this->error("✗ ONESIGNAL_REST_API_KEY no está definido en .env");
            return 1;
        }

        // ── 5. Locale ───────────────────────────────────────────────────
        $locale = optional($persona->pais)->locale ?? config('app.locale');
        $this->line('');
        $this->info("✓ Locale: {$locale}  (pais: " . optional($persona->pais)->nombre . ")");

        // ── 6. Claves de traducción ─────────────────────────────────────
        $notificaciones = [
            'inscripcion_confirmada'  => ['push.inscripcion_confirmada_titulo',  'push.inscripcion_confirmada_cuerpo'],
            'pago_pendiente'          => ['push.pago_pendiente_titulo',           'push.pago_pendiente_cuerpo'],
            'recordatorio_pago'       => ['push.recordatorio_pago_titulo',        'push.recordatorio_pago_cuerpo'],
            'pago_exitoso'            => ['push.pago_exitoso_titulo',             'push.pago_exitoso_cuerpo'],
            'apertura_evaluacion'     => ['push.apertura_evaluacion_titulo',      'push.apertura_evaluacion_cuerpo'],
            'recordatorio_evaluacion' => ['push.recordatorio_evaluacion_titulo',  'push.recordatorio_evaluacion_cuerpo'],
            'reactivacion'            => ['push.reactivacion_titulo',             'push.reactivacion_cuerpo'],
            'recordatorio_asistencia' => ['push.recordatorio_asistencia_titulo',  'push.recordatorio_asistencia_cuerpo'],
            'cambio_actividad'        => ['push.cambio_actividad_titulo',         'push.cambio_actividad_cuerpo'],
        ];

        if (!isset($notificaciones[$notificacion])) {
            $this->error("✗ Notificación '{$notificacion}' no existe. Opciones: " . implode(', ', array_keys($notificaciones)));
            return 1;
        }

        [$tituloKey, $cuerpoKey] = $notificaciones[$notificacion];

        \App::setLocale($locale);
        $titulo  = __($tituloKey,  ['actividad' => 'Actividad de prueba', 'hora' => '09:00']);
        $cuerpo  = __($cuerpoKey,  ['actividad' => 'Actividad de prueba', 'hora' => '09:00']);
        \App::setLocale(config('app.locale'));

        $this->line('');
        $this->info("✓ Título:  {$titulo}");
        $this->info("✓ Cuerpo:  {$cuerpo}");

        // ── 7. Envío directo a OneSignal ────────────────────────────────
        $this->line('');
        $this->line("Enviando a OneSignal...");

        $playerIds = $dispositivos->pluck('player_id')->toArray();
        $resultado = $this->oneSignal->enviarAPlayerIds(
            $playerIds,
            $titulo,
            $cuerpo,
            ['tipo' => 'test', 'notificacion' => $notificacion]
        );

        $this->line('');
        if ($resultado['success']) {
            $this->info("✓ OneSignal respondió OK");
            $this->info("  notification_id: " . ($resultado['id'] ?? 'n/a'));
            $this->info("  recipients:      " . ($resultado['recipients'] ?? 0));

            if (($resultado['recipients'] ?? 0) === 0) {
                $this->warn("⚠ recipients = 0: el player_id existe pero el dispositivo no tiene la app instalada o el token expiró.");
            }
        } else {
            $this->error("✗ OneSignal devolvió error: " . ($resultado['error'] ?? 'desconocido'));
        }

        $this->line('');
        return 0;
    }
}
