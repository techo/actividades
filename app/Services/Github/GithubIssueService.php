<?php

namespace App\Services\Github;

use App\IssueReport;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

/**
 * Crea issues en GitHub a partir de reportes de la bandeja (Fase 3).
 *
 * El objetivo es que el ticket llegue a la cola de trabajo (GitHub Issues) con el
 * contexto de reproducción ya armado, para que un humano —o un agente— pueda tomarlo
 * sin volver a esta pantalla. NO cierra el ciclo automáticamente ni deploya nada.
 *
 * Fail-closed: si no hay `GITHUB_TOKEN` configurado, `enabled()` devuelve false y el
 * caller no debe intentar crear el issue.
 */
class GithubIssueService
{
    /** @var Client */
    private $client;

    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client();
    }

    public function enabled(): bool
    {
        return ! empty(config('services.github.token')) && ! empty(config('services.github.repo'));
    }

    /**
     * Crea el issue y devuelve su URL (html_url). Lanza \RuntimeException si falla.
     */
    public function crearDesdeReporte(IssueReport $report): string
    {
        if (! $this->enabled()) {
            throw new \RuntimeException('La integración con GitHub no está configurada (falta GITHUB_TOKEN).');
        }

        $repo = config('services.github.repo');
        $url  = "https://api.github.com/repos/{$repo}/issues";

        $labels = array_values(array_filter(array_map('trim', explode(',', (string) config('services.github.labels')))));
        // Etiqueta según el tipo para poder filtrar bugs vs sugerencias en GitHub.
        $labels[] = $report->type === IssueReport::TYPE_SUGGESTION ? 'sugerencia' : 'bug';

        try {
            $response = $this->client->post($url, [
                'headers' => [
                    'Authorization' => 'token ' . config('services.github.token'),
                    'Accept'        => 'application/vnd.github+json',
                    'User-Agent'    => 'techo-actividades',
                ],
                'json' => [
                    'title'  => $this->titulo($report),
                    'body'   => $this->cuerpo($report),
                    'labels' => array_values(array_unique($labels)),
                ],
                'timeout' => (int) config('services.github.timeout', 8),
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if (empty($data['html_url'])) {
                throw new \RuntimeException('GitHub no devolvió la URL del issue.');
            }

            return $data['html_url'];
        } catch (RequestException $e) {
            $detalle = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : $e->getMessage();
            Log::error('GithubIssueService: fallo creando issue', ['report' => $report->id, 'error' => $detalle]);
            throw new \RuntimeException('GitHub rechazó la creación del issue.');
        }
    }

    private function titulo(IssueReport $report): string
    {
        $prefijo = $report->type === IssueReport::TYPE_SUGGESTION ? '[Sugerencia]' : '[Bug]';
        $resumen = str_replace(["\r", "\n"], ' ', trim((string) $report->description));
        if (mb_strlen($resumen) > 80) {
            $resumen = mb_substr($resumen, 0, 77) . '…';
        }

        return "{$prefijo} {$resumen}";
    }

    /**
     * Cuerpo en Markdown con el contexto de reproducción. Legible para humanos y para
     * un agente: incluye URL exacta, entorno, quién reportó y los errores JS capturados.
     */
    private function cuerpo(IssueReport $report): string
    {
        $lines = [];
        $lines[] = $report->description;
        $lines[] = '';
        $lines[] = '---';
        $lines[] = '';
        $lines[] = '### Contexto';
        $lines[] = '| Campo | Valor |';
        $lines[] = '| --- | --- |';
        $lines[] = '| Reporte | #' . $report->id . ' |';
        $lines[] = '| Tipo | ' . $report->type . ' |';
        if ($report->severity) { $lines[] = '| Gravedad | ' . $report->severity . ' |'; }
        if ($report->area)     { $lines[] = '| Área | ' . $report->area . ' |'; }
        $lines[] = '| Reportó | ' . $report->reporter_name . ' (' . $report->reporter_email . ') · ' . $report->reporter_role . ' |';
        if ($report->idPais)   { $lines[] = '| País | ' . $report->idPais . ' |'; }
        $lines[] = '| URL | ' . $report->url . ' |';
        if ($report->route_name) { $lines[] = '| Ruta | ' . $report->route_name . ' |'; }
        $lines[] = '| Navegador | ' . $report->browser . ' / ' . $report->os . ' |';
        $lines[] = '| Pantalla | ' . $report->screen_resolution . ' (viewport ' . $report->viewport . ') |';
        if ($report->locale)  { $lines[] = '| Locale | ' . $report->locale . ' |'; }
        if ($report->release) { $lines[] = '| Release | `' . $report->release . '` |'; }

        if (is_array($report->console_errors) && count($report->console_errors)) {
            $lines[] = '';
            $lines[] = '### Errores JS capturados';
            $lines[] = '```json';
            $lines[] = json_encode($report->console_errors, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $lines[] = '```';
        }

        if ($report->screenshot_path) {
            $lines[] = '';
            $lines[] = '_La captura de pantalla está adjunta en la bandeja del backoffice (storage privado)._';
        }

        return implode("\n", $lines);
    }
}
