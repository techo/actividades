<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Guarda un snapshot de las etapas del ciclo de voluntariado por país.
 * Se corre mensualmente (ver App\Console\Kernel) para construir el histórico
 * que alimenta los embudos y el "Var. vs Año Ant." en reporting.
 */
class SnapshotLifecycle extends Command
{
    protected $signature = 'reporting:snapshot-lifecycle';
    protected $description = 'Guarda un snapshot mensual de las etapas del ciclo de voluntariado (reporting_snapshot_lifecycle).';

    public function handle()
    {
        $hoy = now()->toDateString();

        // Idempotente: re-correr el mismo día reemplaza el snapshot del día.
        DB::table('reporting_snapshot_lifecycle')->where('snapshot_date', $hoy)->delete();

        $filas = DB::table('reporting_fact_lifecycle')
            ->selectRaw('idPais, etapa, COUNT(*) as cantidad')
            ->groupBy('idPais', 'etapa')
            ->get();

        foreach ($filas as $f) {
            DB::table('reporting_snapshot_lifecycle')->insert([
                'snapshot_date' => $hoy,
                'idPais'        => $f->idPais,
                'etapa'         => $f->etapa,
                'cantidad'      => $f->cantidad,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        $this->info('Snapshot lifecycle guardado: ' . count($filas) . ' filas para ' . $hoy);
    }
}
