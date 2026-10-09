<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Fusiona los grupos raíz duplicados de cada actividad y alinea el nombre de la raíz con el
 * de la actividad (reporte #22; origen en el triage de grupos de oct-2026).
 *
 * Por qué hay raíces duplicadas: hasta el fix de oct-2026 la raíz se resolvía por NOMBRE
 * (firstOrCreate nombre=nombreActividad), así que clonar o renombrar una actividad creaba una
 * 2ª raíz. Hoy la raíz es la más antigua con idPadre=0 (Actividad::obtenerGrupoRaiz()) y
 * renombrar la actividad renombra la raíz, pero los datos viejos quedaron partidos: parte de
 * la gente cuelga de una raíz y parte de otra, con nombres distintos en la columna "Grupo".
 *
 * Qué hace, por actividad (en una transacción):
 *   - Raíz principal = la más antigua (menor idGrupo) con idPadre=0.
 *   - Las membresías (Grupo_Persona) de las raíces secundarias pasan a la principal; si la
 *     persona ya tenía fila en la principal, la fila secundaria se borra (no se duplica).
 *   - Los subgrupos de las secundarias se re-cuelgan de la principal (no se pierde ninguno).
 *   - Si la principal no tiene linkEvaluacion y una secundaria sí, se conserva ese link.
 *   - Se borran las raíces secundarias (quedan vacías).
 *   - La principal toma el nombre actual de la actividad.
 * Solo toca Grupo y Grupo_Persona (son las únicas tablas que referencian idGrupo).
 *
 * DRY-RUN por defecto. Con --commit escribe. Idempotente.
 */
class FusionarRaicesGrupos extends Command
{
    protected $signature = 'grupos:fusionar-raices
        {--desde=2026-01-01 : solo actividades con fechaInicio desde esta fecha}
        {--actividad= : limitar a una actividad (idActividad)}
        {--commit : escribir de verdad (por defecto es dry-run)}';

    protected $description = 'Fusiona grupos raíz duplicados y alinea el nombre de la raíz con la actividad. Dry-run por defecto.';

    public function handle()
    {
        $commit = (bool) $this->option('commit');
        $prefijo = $commit ? '' : '[DRY-RUN] ';

        $raices = DB::table('Grupo as g')
            ->join('Actividad as a', 'a.idActividad', '=', 'g.idActividad')
            ->where('g.idPadre', 0)
            ->whereNull('a.deleted_at')
            ->when($this->option('actividad'), function ($q, $id) {
                $q->where('g.idActividad', (int) $id);
            }, function ($q) {
                $q->where('a.fechaInicio', '>=', $this->option('desde'));
            })
            ->orderBy('g.idGrupo')
            ->get(['g.idGrupo', 'g.idActividad', 'g.nombre', 'g.linkEvaluacion', 'a.nombreActividad'])
            ->groupBy('idActividad');

        $totales = ['actividades' => 0, 'fusionadas' => 0, 'raices_borradas' => 0,
            'personas_movidas' => 0, 'filas_duplicadas' => 0, 'subgrupos' => 0, 'renombradas' => 0];

        foreach ($raices as $idActividad => $grupos) {
            $principal = $grupos->first();
            $secundarias = $grupos->slice(1);
            // Grupo.nombre es VARCHAR(191) y nombreActividad VARCHAR(300): se compara truncado.
            $nombreRaiz = mb_substr($principal->nombreActividad, 0, 191);
            $renombrar = $principal->nombre !== $nombreRaiz;

            if ($secundarias->isEmpty() && !$renombrar) {
                continue;
            }
            $totales['actividades']++;

            $idsSec = $secundarias->pluck('idGrupo')->all();
            $filasSec = $idsSec
                ? DB::table('Grupo_Persona')->where('idActividad', $idActividad)->whereIn('idGrupo', $idsSec)->get(['id', 'idPersona'])
                : collect();
            $yaEnPrincipal = $filasSec->isEmpty() ? [] : DB::table('Grupo_Persona')
                ->where('idActividad', $idActividad)
                ->where('idGrupo', $principal->idGrupo)
                ->whereIn('idPersona', $filasSec->pluck('idPersona')->unique()->all())
                ->pluck('idPersona')->all();
            $duplicadas = $filasSec->whereIn('idPersona', $yaEnPrincipal);
            $aMover = $filasSec->whereNotIn('idPersona', $yaEnPrincipal);
            $subgrupos = $idsSec ? DB::table('Grupo')->whereIn('idPadre', $idsSec)->count() : 0;

            if ($idsSec) {
                $totales['fusionadas']++;
            }
            $totales['raices_borradas'] += count($idsSec);
            $totales['personas_movidas'] += $aMover->count();
            $totales['filas_duplicadas'] += $duplicadas->count();
            $totales['subgrupos'] += $subgrupos;
            $totales['renombradas'] += $renombrar ? 1 : 0;

            if ($this->getOutput()->isVerbose()) {
                $this->line(sprintf(' - actividad %d: %d raíces secundarias, %d personas a mover, %d filas duplicadas, %d subgrupos%s',
                    $idActividad, count($idsSec), $aMover->count(), $duplicadas->count(), $subgrupos,
                    $renombrar ? " · renombrar \"{$principal->nombre}\" → \"{$nombreRaiz}\"" : ''));
            }

            if (!$commit) {
                continue;
            }

            DB::transaction(function () use ($principal, $secundarias, $idsSec, $aMover, $duplicadas, $renombrar, $nombreRaiz, $idActividad) {
                if ($idsSec) {
                    if ($aMover->isNotEmpty()) {
                        DB::table('Grupo_Persona')->whereIn('id', $aMover->pluck('id')->all())
                            ->update(['idGrupo' => $principal->idGrupo]);
                    }
                    if ($duplicadas->isNotEmpty()) {
                        DB::table('Grupo_Persona')->whereIn('id', $duplicadas->pluck('id')->all())->delete();
                    }
                    DB::table('Grupo')->whereIn('idPadre', $idsSec)->update(['idPadre' => $principal->idGrupo]);

                    $link = $secundarias->pluck('linkEvaluacion')->filter()->first();
                    if (empty($principal->linkEvaluacion) && $link) {
                        DB::table('Grupo')->where('idGrupo', $principal->idGrupo)->update(['linkEvaluacion' => $link]);
                    }

                    DB::table('Grupo')->whereIn('idGrupo', $idsSec)->where('idActividad', $idActividad)->delete();
                }
                if ($renombrar) {
                    DB::table('Grupo')->where('idGrupo', $principal->idGrupo)->update(['nombre' => $nombreRaiz]);
                }
            });
        }

        $this->info($prefijo . "Actividades a corregir: {$totales['actividades']} "
            . "(con raíces duplicadas: {$totales['fusionadas']}, raíz con nombre desactualizado: {$totales['renombradas']}).");
        $this->info($prefijo . "Raíces secundarias a borrar: {$totales['raices_borradas']} · personas a mover: {$totales['personas_movidas']} "
            . "· filas duplicadas a borrar: {$totales['filas_duplicadas']} · subgrupos a re-colgar: {$totales['subgrupos']}.");

        return 0;
    }
}
