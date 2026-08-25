<?php

namespace App\Console\Commands;

use App\Services\Salesforce\SalesforceClient;
use Illuminate\Console\Command;

/**
 * Chequeo end-to-end de la integración con Salesforce (verificación de socio).
 *
 * Objetivo: poder afirmar, con un solo comando, que el lado servidor de Salesforce
 * funciona (credenciales + obtención de token + query de socio). Es la herramienta
 * para validar la conexión en producción ANTES de activar SALESFORCE_ENABLED.
 *
 * Read-only y seguro de correr en producción: no escribe nada en Salesforce ni
 * imprime el access_token completo.
 *
 * Ejemplos:
 *   php artisan salesforce:selfcheck
 *   php artisan salesforce:selfcheck 24134990
 *   php artisan salesforce:selfcheck 24134990 --json
 */
class SalesforceSelfCheck extends Command
{
    protected $signature = 'salesforce:selfcheck
        {dni? : DNI a consultar (opcional). Si se pasa, corre la SOQL de socio contra Salesforce}
        {--json : Salida en JSON en vez de tabla legible}';

    protected $description = 'Chequeo de Salesforce: credenciales, obtención de token y (opcional) consulta de socio por DNI.';

    public function handle(SalesforceClient $client)
    {
        $rows = [];
        $ok   = true;

        // ── 1. Configuración ──────────────────────────────────────────────────
        $clientId = config('services.salesforce.client_id');
        $secret   = config('services.salesforce.client_secret');
        $tokenUrl = config('services.salesforce.token_url');
        $enabled  = config('services.salesforce.enabled');

        $rows[] = [
            'check'  => 'feature flag (SALESFORCE_ENABLED)',
            'status' => $enabled ? 'on' : 'off',
            'detail' => $enabled ? 'exención activa' : 'no-op (solo diagnóstico, nadie queda exento)',
        ];

        $credsOk = !empty($clientId) && !empty($secret) && !empty($tokenUrl);
        $rows[] = [
            'check'  => 'credenciales',
            'status' => $credsOk ? 'ok' : 'FALTA',
            'detail' => $credsOk
                ? 'client_id/secret/token_url presentes (token_url=' . $tokenUrl . ')'
                : 'faltan client_id/secret/token_url en .env',
        ];

        if (!$credsOk) {
            $this->render($rows, false);
            return 1;
        }

        // ── 2. Access token ───────────────────────────────────────────────────
        try {
            $token = $client->getAccessToken(true);
            $rows[] = [
                'check'  => 'access_token (client_credentials)',
                'status' => 'ok',
                'detail' => 'instance_url=' . $token['instance_url'],
            ];
        } catch (\Exception $e) {
            $rows[] = ['check' => 'access_token (client_credentials)', 'status' => 'FALLA', 'detail' => $e->getMessage()];
            $this->render($rows, false);
            return 1;
        }

        // ── 3. Query de socio por DNI (opcional) ──────────────────────────────
        $dni = $this->argument('dni');
        if ($dni) {
            $dniNorm = preg_replace('/\D+/', '', $dni);
            $soql    = "SELECT npsp__Sustainer__c FROM Contact WHERE DNI__c='" . $dniNorm . "'";
            try {
                $body    = $client->query($soql);
                $total   = $body['totalSize'] ?? 0;
                $esSocio = false;
                foreach (($body['records'] ?? []) as $r) {
                    if (!empty($r['npsp__Sustainer__c'])) { $esSocio = true; break; }
                }
                $verdict = $total == 0
                    ? 'DNI no encontrado → NO socio'
                    : ($esSocio ? 'ES SOCIO (exento de pago)' : 'encontrado, NO socio');
                $rows[] = [
                    'check'  => 'query socio DNI ' . $dniNorm,
                    'status' => 'ok',
                    'detail' => 'totalSize=' . $total . ' → ' . $verdict,
                ];
            } catch (\Exception $e) {
                $rows[] = ['check' => 'query socio DNI ' . $dniNorm, 'status' => 'FALLA', 'detail' => $e->getMessage()];
                $ok = false;
            }
        }

        $this->render($rows, $ok);
        return $ok ? 0 : 1;
    }

    private function render(array $rows, bool $ok)
    {
        if ($this->option('json')) {
            $this->line(json_encode(['ok' => $ok, 'rows' => $rows], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            return;
        }

        $this->line('');
        $this->line('══ Salesforce self-check ══  APP_ENV=' . config('app.env') . '  ' . now()->toDateTimeString());
        foreach ($rows as $row) {
            if (in_array($row['status'], ['ok', 'on'], true)) {
                $icon = '✓';
            } elseif ($row['status'] === 'off') {
                $icon = '·';
            } else {
                $icon = '✗';
            }
            $this->line(sprintf('  %s  %-38s %-8s %s', $icon, $row['check'], $row['status'], $row['detail']));
        }
        $this->line('');
        $this->line($ok ? 'RESULTADO: OK' : 'RESULTADO: HAY FALLAS');
        $this->line('');
    }
}
