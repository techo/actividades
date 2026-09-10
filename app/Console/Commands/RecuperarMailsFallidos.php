<?php

namespace App\Console\Commands;

use App\Notifications\VerifyEmail;
use App\Services\EstadoInscripcion;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Re-envía mails transaccionales que quedaron en `failed_jobs` durante la caída
 * de mailing (Gmail relay) de la semana, ANTES de la migración a SES.
 *
 * Principios (ver plan de recuperación):
 *   - Se maneja SOLO desde failed_jobs → dedup automático: si un mail salió bien,
 *     no está acá, así que no se re-envía.
 *   - Chequea el ESTADO ACTUAL antes de re-enviar, para no molestar:
 *       · verificacion → saltea si la cuenta YA está verificada (admin la validó).
 *       · confirmacion → saltea si la inscripción no está confirmada o la actividad ya pasó.
 *       · esperar      → saltea si ya fue confirmada o la actividad ya pasó.
 *       · pago         → saltea si ya pagó o la actividad ya pasó.
 *   - DRY-RUN por defecto: reporta qué haría. Con --commit envía.
 *   - Escalonado: --rate mails/seg (throttle para no pasar el rate de SES).
 *   - Idempotente: al re-enviar OK, borra ese failed_job (no se re-manda en otra corrida).
 *
 * Deliberadamente NO cubre las invitaciones masivas ni recordatorios (stale).
 */
class RecuperarMailsFallidos extends Command
{
    protected $signature = 'mail:recuperar-fallidos
        {--tipo=verificacion : verificacion|confirmacion|esperar|pago}
        {--desde=2026-09-01 00:00:00 : failed_at desde}
        {--hasta= : failed_at hasta (default: ahora)}
        {--rate=5 : mails por segundo al enviar}
        {--commit : enviar de verdad (por defecto es dry-run, no manda nada)}';

    protected $description = 'Re-envía mails transaccionales fallidos (failed_jobs) chequeando el estado actual. Dry-run por defecto.';

    /** Firmas en el payload para filtrar failed_jobs por tipo. */
    private $firmas = [
        'verificacion' => ['VerifyEmail'],
        'confirmacion' => ['MailInscripcionConfirmada'],
        'esperar'      => ['MailInscripcionEsperarConfirmacion'],
        'pago'         => ['MailInscripcionFaltaPago', 'MailInscripcionPagoFueraDeFecha'],
    ];

    public function handle()
    {
        $tipo = $this->option('tipo');
        if (!isset($this->firmas[$tipo])) {
            $this->error("tipo inválido: {$tipo} (usá verificacion|confirmacion|esperar|pago)");
            return 1;
        }

        $desde  = $this->option('desde');
        $hasta  = $this->option('hasta') ?: now()->toDateTimeString();
        $commit = (bool) $this->option('commit');
        $rate   = max(1, (int) $this->option('rate'));
        $usleep = (int) (1000000 / $rate);

        $like = collect($this->firmas[$tipo])
            ->map(function ($c) { return "payload LIKE '%{$c}%'"; })
            ->implode(' OR ');

        $rows = DB::table('failed_jobs')
            ->whereBetween('failed_at', [$desde, $hasta])
            ->whereRaw("({$like})")
            ->orderBy('id')
            ->get();

        $this->info("Tipo={$tipo}  ventana=[{$desde} .. {$hasta}]  candidatos={$rows->count()}  modo=" . ($commit ? 'COMMIT (envía)' : 'DRY-RUN (no envía)'));
        $this->line('');

        $enviados = 0;
        $salteados = 0;
        $motivos = [];

        foreach ($rows as $row) {
            try {
                $payload = json_decode($row->payload, true);
                // unserialize dispara __wakeup de SerializesModels → re-fetch de modelos
                // (lanza ModelNotFoundException si el registro fue borrado).
                $command = @unserialize($payload['data']['command']);
                if ($command === false) {
                    $salteados++; $motivos['payload_corrupto'] = ($motivos['payload_corrupto'] ?? 0) + 1;
                    continue;
                }

                list($accion, $motivo) = $this->evaluar($tipo, $command, $commit);

                if ($accion === 'enviar') {
                    $enviados++;
                    if ($commit) {
                        DB::table('failed_jobs')->where('id', $row->id)->delete();
                        usleep($usleep);
                    }
                } else {
                    $salteados++; $motivos[$motivo] = ($motivos[$motivo] ?? 0) + 1;
                }
            } catch (\Throwable $e) {
                $salteados++;
                $motivos['registro_inexistente_o_error'] = ($motivos['registro_inexistente_o_error'] ?? 0) + 1;
                Log::warning('recuperar-fallidos: job salteado', ['id' => $row->id, 'error' => $e->getMessage()]);
            }
        }

        $this->line('');
        $this->info(($commit ? 'Enviados' : 'Se enviarían') . ": {$enviados}");
        $this->info("Salteados: {$salteados}");
        foreach ($motivos as $m => $n) {
            $this->line("   · {$m}: {$n}");
        }
        if (!$commit) {
            $this->line('');
            $this->comment('Dry-run: no se envió nada. Repetí con --commit para enviar.');
        }
        return 0;
    }

    /**
     * @return array [accion('enviar'|'saltear'), motivo]
     */
    private function evaluar(string $tipo, $command, bool $commit): array
    {
        if ($tipo === 'verificacion') {
            $notifiables = $command->notifiables ?? null;
            $persona = $notifiables instanceof Collection ? $notifiables->first() : $notifiables;
            if (!$persona) {
                return ['saltear', 'sin_persona'];
            }
            if (!is_null($persona->email_verified_at)) {
                return ['saltear', 'ya_verificado'];   // un admin (o el propio user) ya validó
            }
            if ($commit) {
                $locale = optional($persona->pais)->locale ?? config('app.locale');
                $persona->notifyNow((new VerifyEmail)->locale($locale));
            }
            return ['enviar', 'reenviar_verificacion'];
        }

        // Tipos Mailable (SendQueuedMailable)
        $mailable = $command->mailable ?? null;
        if (!$mailable) {
            return ['saltear', 'sin_mailable'];
        }
        $insc = $mailable->inscripcion ?? null;
        if (!$insc || !$insc->actividad) {
            return ['saltear', 'inscripcion_inexistente'];
        }

        // Estado canónico (fuente de verdad): ramifica por el requisito real de
        // la actividad; una actividad que no requiere confirmación queda CONFIRMED
        // aunque confirma=0. Por eso NO alcanza con mirar el flag suelto.
        $estado          = EstadoInscripcion::resolve($insc->actividad, $insc);
        $actividadPasada = optional($insc->actividad->fechaInicio)->isPast();

        if ($tipo === 'confirmacion') {
            if ($estado !== EstadoInscripcion::CONFIRMED)            return ['saltear', 'no_confirmada'];
            if ($actividadPasada)                                   return ['saltear', 'actividad_pasada'];
        } elseif ($tipo === 'esperar') {
            if ($estado !== EstadoInscripcion::WAITING_CONFIRMATION) return ['saltear', 'no_esperando_confirmacion'];
            if ($actividadPasada)                                   return ['saltear', 'actividad_pasada'];
        } elseif ($tipo === 'pago') {
            // Solo si todavía puede pagar (plazo abierto); si ya pagó/está confirmada
            // o venció el plazo, no corresponde.
            if ($estado !== EstadoInscripcion::CONFIRM_BY_PAYING)   return ['saltear', 'no_requiere_pago'];
            if ($actividadPasada)                                   return ['saltear', 'actividad_pasada'];
        }

        if ($commit) {
            // Envío sincrónico (aunque el Mailable sea ShouldQueue) para respetar el throttle.
            $mailable->send(app('mailer'));
        }
        return ['enviar', 'reenviar_' . $tipo];
    }
}
