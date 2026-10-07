<?php

namespace App\Console\Commands;

use App\IssueReport;
use App\Services\Github\GithubIssueService;
use Illuminate\Console\Command;

/**
 * Crea issues de GitHub para los reportes abiertos que todavía no tienen uno.
 *
 * Es el ángulo de "levantar los tickets automáticamente": corriéndolo (a mano o
 * programado en Kernel) todos los reportes nuevos/triage llegan a la cola de trabajo
 * de GitHub con su contexto de reproducción. Idempotente: saltea los que ya tienen
 * issue. Fail-closed si GitHub no está configurado.
 */
class SyncReportesGithub extends Command
{
    protected $signature = 'reportes:sync-github {--limit=25 : Máximo de reportes a procesar en esta corrida}';

    protected $description = 'Crea issues de GitHub para los reportes abiertos que aún no tienen uno';

    public function handle(GithubIssueService $github)
    {
        if (! $github->enabled()) {
            $this->error('GitHub no está configurado (falta GITHUB_TOKEN). Abortando.');
            return 1;
        }

        $limit = (int) $this->option('limit');

        $reportes = IssueReport::whereIn('status', [IssueReport::STATUS_NUEVO, IssueReport::STATUS_TRIAGE])
            ->whereNull('github_issue_url')
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        if ($reportes->isEmpty()) {
            $this->info('No hay reportes pendientes de crear en GitHub.');
            return 0;
        }

        $ok = 0;
        foreach ($reportes as $reporte) {
            try {
                $url = $github->crearDesdeReporte($reporte);
                $reporte->github_issue_url = $url;
                if ($reporte->status === IssueReport::STATUS_NUEVO) {
                    $reporte->status = IssueReport::STATUS_EN_PROGRESO;
                }
                $reporte->save();
                $this->line("Reporte #{$reporte->id} → {$url}");
                $ok++;
            } catch (\RuntimeException $e) {
                $this->error("Reporte #{$reporte->id}: {$e->getMessage()}");
            }
        }

        $this->info("Listo: {$ok}/{$reportes->count()} issues creados.");
        return 0;
    }
}
