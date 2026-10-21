<?php

namespace App\Console\Commands;

use App\Actividad;
use App\Jobs\EnviarMailTransaccionalSes;
use App\Mail\MailRecordatorioPagoVencimiento;
use App\Services\EstadoInscripcion;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Recordatorio PROACTIVO de pago: avisa ~2 días ANTES de que venza la fecha
 * límite de pago a quien todavía está en estado "confirmar pagando", para que
 * llegue a aportar y no pierda el cupo. Más útil que el viejo aviso de "venció
 * el plazo" (que llegaba cuando ya no se podía hacer nada).
 *
 * Sale por SES (EnviarMailTransaccionalSes), no por Gmail: aunque es transaccional
 * en importancia, una actividad grande son cientos de mails en un minuto y Gmail
 * corta la ráfaga (prod 2026-10-12: 81 de 199 fallaron). Al ser transaccional
 * NO se frena con mailing.bulk_pausado.
 */
class EnviarRecordatorioPagoVencimiento extends Command
{
    protected $signature = 'pago:recordatorio-vencimiento {--dias=2 : Días de anticipación respecto de la fecha límite de pago}';

    protected $description = 'Recuerda por mail a los inscriptos con pago pendiente ~2 días antes de que venza la fecha límite de pago';

    public function handle()
    {
        $dias = max(1, (int) $this->option('dias'));
        // Actividades cuya fecha límite de pago cae exactamente ese día objetivo.
        $objetivo = Carbon::today()->addDays($dias)->toDateString();

        $actividades = Actividad::where('pago', 1)
            ->whereNotNull('fechaLimitePago')
            ->whereDate('fechaLimitePago', $objetivo)
            ->get();

        if ($actividades->isEmpty()) {
            $this->info("Sin actividades con fecha límite de pago el {$objetivo}.");
            return;
        }

        // Escalonado: una actividad grande son cientos de mails; repartimos el envío para no
        // disparar una ráfaga contra SES.
        $porSegundo = max(1, intdiv((int) config('mailing.batch_por_minuto', 120), 60));
        $enviados = 0;
        $i = 0;

        foreach ($actividades as $actividad) {
            $inscripciones = $actividad->inscripciones()
                ->where('pago', 0)
                ->where(function ($q) {
                    $q->where('exento_pago', 0)->orWhereNull('exento_pago');
                })
                ->when($actividad->confirmacion == 1, function ($q) {
                    $q->where('confirma', 1);
                })
                ->with(['persona.pais'])
                ->get();

            foreach ($inscripciones as $inscripcion) {
                // Fuente de verdad: solo a quien realmente está "confirmar pagando".
                if (EstadoInscripcion::resolve($actividad, $inscripcion) !== EstadoInscripcion::CONFIRM_BY_PAYING) {
                    continue;
                }

                $persona = $inscripcion->persona;
                if (!$persona || !$persona->recibirMails || !$persona->tieneMailValido()) {
                    continue;
                }

                $delay = now()->addSeconds(5 + intdiv($i, $porSegundo));
                dispatch((new EnviarMailTransaccionalSes(new MailRecordatorioPagoVencimiento($inscripcion), $persona->mail))->delay($delay));
                $enviados++;
                $i++;
            }
        }

        $this->info("Recordatorio de pago: {$enviados} mail(s) encolado(s) (fecha límite {$objetivo}).");
        \Log::info("pago:recordatorio-vencimiento: {$enviados} mails encolados para fecha límite {$objetivo}.");
    }
}
