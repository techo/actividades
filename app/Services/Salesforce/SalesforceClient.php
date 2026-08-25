<?php

namespace App\Services\Salesforce;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Transporte hacia Salesforce (OAuth 2.0 Client Credentials + SOQL).
 *
 * Responsabilidad única: obtener/cachear el access_token y correr queries. NO
 * decide nada de negocio (eso vive en SocioService). Ante cualquier error de
 * red/HTTP lanza RuntimeException para que la capa de negocio aplique su
 * política (fail-closed). Espeja el patrón de OneSignalService (Guzzle + config).
 *
 * Flujo (según la guía de conexión de TECHO):
 *   1. POST client_credentials al token endpoint → { access_token, instance_url }.
 *   2. GET {instance_url}/services/data/{version}/query?q=SOQL con Bearer token.
 *      En 401 el token expiró → se refresca una vez y se reintenta.
 */
class SalesforceClient
{
    /** Cache key del token + instance_url. */
    const CACHE_KEY_TOKEN = 'salesforce:oauth_token';

    /**
     * TTL del token cacheado (minutos). El manejo de 401 lo refresca antes si
     * la sesión de Salesforce expira, así que un valor conservador alcanza.
     */
    const TOKEN_TTL_MIN = 90;

    /** @var Client */
    protected $client;

    public function __construct(Client $client = null)
    {
        $this->client = $client ?: new Client([
            'timeout' => (int) config('services.salesforce.timeout', 5),
        ]);
    }

    /**
     * Obtiene el access_token + instance_url (cacheado).
     *
     * @param  bool $forceRefresh  Ignora la caché y re-pide el token.
     * @return array{access_token:string, instance_url:string}
     * @throws \RuntimeException si no se puede obtener el token
     */
    public function getAccessToken(bool $forceRefresh = false): array
    {
        if (!$forceRefresh) {
            $cached = Cache::get(self::CACHE_KEY_TOKEN);
            if (is_array($cached) && !empty($cached['access_token'])) {
                return $cached;
            }
        }

        $clientId     = config('services.salesforce.client_id');
        $clientSecret = config('services.salesforce.client_secret');
        $tokenUrl     = config('services.salesforce.token_url');

        if (empty($clientId) || empty($clientSecret) || empty($tokenUrl)) {
            throw new \RuntimeException('Salesforce: credenciales/token_url no configuradas.');
        }

        try {
            $response = $this->client->post($tokenUrl, [
                'headers'     => ['Content-Type' => 'application/x-www-form-urlencoded'],
                'form_params' => [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $clientId,
                    'client_secret' => $clientSecret,
                ],
            ]);
        } catch (RequestException $e) {
            Log::warning('Salesforce: fallo al obtener access_token', ['message' => $e->getMessage()]);
            throw new \RuntimeException('Salesforce: no se pudo obtener el access_token', 0, $e);
        }

        $body = json_decode($response->getBody()->getContents(), true);

        if (empty($body['access_token']) || empty($body['instance_url'])) {
            throw new \RuntimeException('Salesforce: respuesta de token inválida (sin access_token/instance_url).');
        }

        $token = [
            // Se usa el instance_url devuelto por Salesforce para las queries;
            // puede diferir del dominio usado para pedir el token (lo pide la doc).
            'access_token' => $body['access_token'],
            'instance_url' => rtrim($body['instance_url'], '/'),
        ];

        Cache::put(self::CACHE_KEY_TOKEN, $token, self::TOKEN_TTL_MIN);

        return $token;
    }

    /**
     * Corre una query SOQL y devuelve la respuesta decodificada.
     * En 401 (token expirado) refresca el token una vez y reintenta.
     *
     * @return array respuesta SOQL decodificada (totalSize, done, records[])
     * @throws \RuntimeException ante cualquier error irrecuperable
     */
    public function query(string $soql): array
    {
        return $this->runQuery($soql, false);
    }

    private function runQuery(string $soql, bool $isRetry): array
    {
        $token   = $this->getAccessToken($isRetry);
        $version = config('services.salesforce.api_version', 'v61.0');
        $url     = $token['instance_url'] . '/services/data/' . $version . '/query';

        try {
            $response = $this->client->get($url, [
                'headers' => ['Authorization' => 'Bearer ' . $token['access_token']],
                'query'   => ['q' => $soql],
            ]);
        } catch (ClientException $e) {
            // 401 → token expirado: invalidar caché, refrescar y reintentar UNA vez.
            if (!$isRetry && $e->getResponse() && $e->getResponse()->getStatusCode() === 401) {
                Cache::forget(self::CACHE_KEY_TOKEN);
                return $this->runQuery($soql, true);
            }
            Log::warning('Salesforce: error HTTP en query', ['message' => $e->getMessage()]);
            throw new \RuntimeException('Salesforce: query falló', 0, $e);
        } catch (RequestException $e) {
            Log::warning('Salesforce: error de red en query', ['message' => $e->getMessage()]);
            throw new \RuntimeException('Salesforce: query falló', 0, $e);
        }

        $body = json_decode($response->getBody()->getContents(), true);

        if (!is_array($body)) {
            throw new \RuntimeException('Salesforce: respuesta de query inválida.');
        }

        return $body;
    }
}
