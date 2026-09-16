<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Resumen de mails del día, discriminando TRANSACCIONAL (Gmail) vs BULK (SES).
 *
 *  - Enviados OK: se leen del log diario de mailstats (canal 'mailstats',
 *    storage/logs/mailstats-YYYY-MM-DD.log), que LogMailEnviado escribe por cada
 *    MessageSent con su 'channel'. Las líneas viejas (previas al tag de canal)
 *    quedan como 'sin_clasificar'.
 *  - Fallidos: se leen de failed_jobs y se clasifican por la clase del Mailable/Job.
 *
 * Uso:
 *   php artisan mail:resumen-envios              # hoy
 *   php artisan mail:resumen-envios --fecha=2026-09-12
 */
class ResumenEnviosMail extends Command
{
    protected $signature = 'mail:resumen-envios {--fecha= : Fecha YYYY-MM-DD (default: hoy)}';

    protected $description = 'Resumen de mails del día enviados/fallidos, discriminando transaccional vs bulk';

    /** Clases (Mailable o Job) que corresponden al canal BULK. El resto es transaccional. */
    const BULK = [
        'App\\Mail\\RecordatorioActividad',
        'App\\Mail\\InvitacionActividadMail',
        'App\\Mail\\InvitacionEvaluacion',
        'App\\Mail\\InvitacionCampaniaMail',
        'App\\Jobs\\EnviarMailBulkSes',
        'App\\Jobs\\EnviarMailsRecordatorioActividad',
        'App\\Jobs\\EnviarInvitacionActividad',
    ];

    public function handle()
    {
        $fecha = $this->option('fecha')
            ? Carbon::parse($this->option('fecha'))->toDateString()
            : Carbon::today()->toDateString();

        [$envBulk, $envTrans, $envSin] = $this->contarEnviados($fecha);
        [$falBulk, $falTrans] = $this->contarFallidos($fecha);

        $envTotal = $envBulk + $envTrans + $envSin;
        $falTotal = $falBulk + $falTrans;
        $intentos = $envTotal + $falTotal;
        $tasa = $intentos > 0 ? round($falTotal * 100 / $intentos, 1) : 0;

        $this->line('');
        $this->info("📧 Resumen de mails — {$fecha}");
        $this->line('');
        $this->line('  ENVIADOS OK');
        $this->line('    transaccional : ' . $envTrans);
        $this->line('    bulk          : ' . $envBulk);
        if ($envSin > 0) {
            $this->line('    sin clasificar: ' . $envSin . '  (previos al tag de canal)');
        }
        $this->line('    ── total      : ' . $envTotal);
        $this->line('');
        $this->line('  FALLIDOS (failed_jobs)');
        $this->line('    transaccional : ' . $falTrans);
        $this->line('    bulk          : ' . $falBulk);
        $this->line('    ── total      : ' . $falTotal);
        $this->line('');
        $this->line("  Tasa de fallo: {$tasa}%  ({$falTotal} de {$intentos} intentos)");
        $this->line('');

        return 0;
    }

    /** Lee el log diario de mailstats y cuenta SENT por canal. */
    private function contarEnviados(string $fecha): array
    {
        $path = storage_path("logs/mailstats-{$fecha}.log");
        $bulk = $trans = $sin = 0;

        if (!is_file($path)) {
            return [0, 0, 0];
        }

        $fh = fopen($path, 'r');
        if (!$fh) {
            return [0, 0, 0];
        }

        while (($linea = fgets($fh)) !== false) {
            if (strpos($linea, 'SENT ') === false) {
                continue;
            }
            // Extrae el contexto JSON que sigue a 'SENT '.
            $pos = strpos($linea, 'SENT {');
            if ($pos === false) {
                continue;
            }
            $json = substr($linea, $pos + 5);
            $data = json_decode(trim($json), true);
            $canal = is_array($data) ? ($data['channel'] ?? null) : null;

            if ($canal === 'bulk') {
                $bulk++;
            } elseif ($canal === 'transaccional') {
                $trans++;
            } else {
                $sin++;
            }
        }
        fclose($fh);

        return [$bulk, $trans, $sin];
    }

    /** Cuenta failed_jobs de la fecha, clasificando por la clase del payload. */
    private function contarFallidos(string $fecha): array
    {
        $desde = Carbon::parse($fecha)->startOfDay();
        $hasta = Carbon::parse($fecha)->endOfDay();
        $bulk = $trans = 0;

        DB::table('failed_jobs')
            ->whereBetween('failed_at', [$desde, $hasta])
            ->select('payload')
            ->orderBy('id')
            ->chunk(500, function ($rows) use (&$bulk, &$trans) {
                foreach ($rows as $r) {
                    $d = json_decode($r->payload, true);
                    $name = $d['displayName'] ?? '';
                    if (in_array($name, self::BULK, true)) {
                        $bulk++;
                    } else {
                        $trans++;
                    }
                }
            });

        return [$bulk, $trans];
    }
}
