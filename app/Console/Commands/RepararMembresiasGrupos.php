<?php

namespace App\Console\Commands;

use App\Actividad;
use App\GrupoRolPersona;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Repara inscripciones vivas que quedaron SIN fila en Grupo_Persona (task 47, reclamos #7/#11).
 *
 * Causas históricas (ya corregidas en código):
 *   - GruposActividadesController::delete() borraba la membresía de la persona en TODAS sus
 *     actividades al quitarla de un grupo en una sola.
 *   - Altas manuales que fallaban al resolver el grupo.
 * Sin esa fila la persona no aparece en el árbol de grupos, no se la puede mover a un grupo
 * y no aparece para ser evaluada por sus pares.
 *
 * Qué hace: crea la fila en el grupo RAÍZ (el más antiguo con idPadre=0; ver
 * Actividad::obtenerGrupoRaiz()) de cada actividad. No mueve a nadie que ya tenga grupo.
 * Además LISTA (sin tocar) las actividades con más de un grupo raíz: fusionarlas es una
 * decisión caso a caso.
 *
 * DRY-RUN por defecto. Con --commit escribe. Idempotente.
 */
class RepararMembresiasGrupos extends Command
{
    protected $signature = 'grupos:reparar-membresias
        {--desde=2026-01-01 : solo actividades con fechaInicio desde esta fecha}
        {--actividad= : limitar a una actividad (idActividad)}
        {--commit : escribir de verdad (por defecto es dry-run)}';

    protected $description = 'Crea en el grupo raíz las membresías faltantes de inscripciones vivas. Dry-run por defecto.';

    public function handle()
    {
        $commit = (bool) $this->option('commit');

        $huerfanas = DB::table('Inscripcion as i')
            ->join('Actividad as a', 'a.idActividad', '=', 'i.idActividad')
            ->whereNull('i.deleted_at')
            ->whereNull('a.deleted_at')
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('Grupo_Persona as gp')
                    ->whereColumn('gp.idPersona', 'i.idPersona')
                    ->whereColumn('gp.idActividad', 'i.idActividad');
            })
            ->when($this->option('actividad'), function ($q, $id) {
                $q->where('i.idActividad', (int) $id);
            }, function ($q) {
                $q->where('a.fechaInicio', '>=', $this->option('desde'));
            })
            ->select('i.idActividad', 'i.idPersona')
            ->distinct()
            ->get()
            ->groupBy('idActividad');

        $total = $huerfanas->sum(function ($filas) { return $filas->count(); });
        $this->info(($commit ? '' : '[DRY-RUN] ') . "Inscripciones sin grupo: {$total} en {$huerfanas->count()} actividades.");

        $creadas = 0;
        foreach ($huerfanas as $idActividad => $filas) {
            $actividad = Actividad::find($idActividad);
            if (!$actividad) {
                continue;
            }
            $this->line(" - actividad {$idActividad}: {$filas->count()}");

            if (!$commit) {
                continue;
            }

            $raiz = $actividad->obtenerGrupoRaiz();
            foreach ($filas as $fila) {
                GrupoRolPersona::firstOrCreate(
                    ['idPersona' => $fila->idPersona, 'idActividad' => $idActividad],
                    ['idGrupo' => $raiz->idGrupo, 'rol' => '']
                );
                $creadas++;
            }
        }

        if ($commit) {
            $this->info("Membresías creadas: {$creadas}.");
        }

        $this->reportarRaicesDuplicadas();

        return 0;
    }

    private function reportarRaicesDuplicadas()
    {
        $duplicadas = DB::table('Grupo as g')
            ->join('Actividad as a', 'a.idActividad', '=', 'g.idActividad')
            ->where('g.idPadre', 0)
            ->whereNull('a.deleted_at')
            ->when($this->option('actividad'), function ($q, $id) {
                $q->where('g.idActividad', (int) $id);
            }, function ($q) {
                $q->where('a.fechaInicio', '>=', $this->option('desde'));
            })
            ->groupBy('g.idActividad')
            ->havingRaw('COUNT(*) > 1')
            ->select('g.idActividad', DB::raw('COUNT(*) as raices'))
            ->get();

        $this->info("Actividades con más de un grupo raíz (no se modifican): {$duplicadas->count()}.");
        if ($this->getOutput()->isVerbose()) {
            foreach ($duplicadas as $d) {
                $this->line(" - actividad {$d->idActividad}: {$d->raices} raíces");
            }
        }
    }
}
