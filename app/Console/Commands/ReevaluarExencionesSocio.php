<?php

namespace App\Console\Commands;

use App\Inscripcion;
use App\Services\Salesforce\SocioExencionService;
use App\Services\Salesforce\SocioService;
use Illuminate\Console\Command;

/**
 * Re-evalúa la exención por socio (Salesforce) de inscripciones con pago pendiente.
 *
 * Por qué existe: la exención se materializaba solo al crear la inscripción. Quien
 * se hizo socio después, o se inscribió mientras Salesforce fallaba (fail-closed →
 * se cobra), quedó con el pago pendiente aunque hoy sea socio (reclamos oct-2026,
 * act. 41565). Desde este fix también se re-evalúa en cada punto de pago; este
 * comando repara lo que ya quedó atrás.
 *
 * Alcance: actividades pagas del país de socios (Argentina) con fechaInicio desde
 * --desde; inscripciones vivas, no pagadas y no exentas. NO toca las ya pagadas:
 * con --pagadas solo LISTA las pagadas cuya persona hoy es socio (posible
 * devolución, se decide caso a caso).
 *
 * DRY-RUN por defecto (consulta Salesforce pero no escribe). Con --commit escribe.
 * Idempotente. No envía mails ni push.
 */
class ReevaluarExencionesSocio extends Command
{
    protected $signature = 'socios:reevaluar-exenciones
        {--desde= : solo actividades con fechaInicio desde esta fecha (default: hoy)}
        {--actividad= : limitar a una actividad (idActividad)}
        {--pagadas : listar además las inscripciones YA pagadas cuya persona hoy es socio (no las modifica)}
        {--commit : escribir de verdad (por defecto es dry-run)}';

    protected $description = 'Marca exentas las inscripciones con pago pendiente de personas que hoy son socio. Dry-run por defecto.';

    public function handle(SocioService $socios, SocioExencionService $exencion)
    {
        if (!config('services.salesforce.enabled')) {
            $this->error('SALESFORCE_ENABLED está apagado: no hay nada que evaluar.');
            return 1;
        }

        $commit    = (bool) $this->option('commit');
        $paisSocio = (int) config('services.salesforce.socio_pais_id', 13);
        $desde     = $this->option('desde') ?: now()->toDateString();

        $inscripciones = Inscripcion::whereHas('actividad', function ($q) use ($paisSocio, $desde) {
                $q->where('pago', 1)->where('idPais', $paisSocio);
                if ($this->option('actividad')) {
                    $q->where('idActividad', (int) $this->option('actividad'));
                } else {
                    $q->where('fechaInicio', '>=', $desde);
                }
            })
            ->where(function ($q) {
                $q->where('exento_pago', 0)->orWhereNull('exento_pago');
            })
            ->when(!$this->option('pagadas'), function ($q) {
                $q->where(function ($q) {
                    $q->where('pago', 0)->orWhereNull('pago');
                });
            })
            ->with(['actividad', 'persona'])
            ->orderBy('idActividad')
            ->get();

        $alcance = $this->option('actividad') ? "actividad {$this->option('actividad')}" : "actividades desde {$desde}";
        $this->info(($commit ? '' : '[DRY-RUN] ') . "Evaluando {$inscripciones->count()} inscripciones ({$alcance}).");

        $exentas  = [];
        $pagadas  = [];
        $sinDatos = 0;

        foreach ($inscripciones as $inscripcion) {
            if (!$inscripcion->persona || !$inscripcion->actividad) {
                $sinDatos++;
                continue;
            }

            // esSocio() es fail-closed: un error de Salesforce cuenta como "no socio"
            // (y queda logueado). Re-correr el comando más tarde lo recupera.
            if (!$socios->esSocio($inscripcion->persona)) {
                continue;
            }

            $fila = [
                $inscripcion->idInscripcion,
                $inscripcion->idActividad,
                mb_substr($inscripcion->actividad->nombreActividad, 0, 40),
                $inscripcion->idPersona,
                $inscripcion->confirma ? 'sí' : 'no',
            ];

            if ((int) $inscripcion->pago === 1) {
                $pagadas[] = $fila;
                continue;
            }

            $exentas[] = $fila;
            if ($commit) {
                $exencion->reevaluar($inscripcion);
            }
        }

        $headers = ['idInscripcion', 'idActividad', 'actividad', 'idPersona', 'confirmada'];

        $this->line('');
        $this->info(($commit ? 'Marcadas exentas' : 'Quedarían exentas') . ': ' . count($exentas));
        if ($exentas) {
            $this->table($headers, $exentas);
        }

        if ($this->option('pagadas')) {
            $this->line('');
            $this->warn('Ya pagadas y hoy son socio (no se modifican; ¿devolución?): ' . count($pagadas));
            if ($pagadas) {
                $this->table($headers, $pagadas);
            }
        }

        if ($sinDatos) {
            $this->warn("Sin persona/actividad (salteadas): {$sinDatos}");
        }

        if (!$commit && $exentas) {
            $this->line('');
            $this->comment('Dry-run: no se escribió nada. Re-correr con --commit para aplicar.');
        }

        return 0;
    }
}
