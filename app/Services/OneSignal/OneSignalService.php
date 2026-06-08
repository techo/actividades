<?php

namespace App\Services\OneSignal;

use App\Dispositivo;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class OneSignalService
{
    const API_URL = 'https://onesignal.com/api/v1/notifications';

    // OneSignal soporta hasta 2000 player_ids por request
    const MAX_PLAYER_IDS_POR_REQUEST = 2000;

    protected $appId;
    protected $apiKey;
    protected $client;

    public function __construct()
    {
        $this->appId  = config('services.onesignal.app_id');
        $this->apiKey = config('services.onesignal.api_key');
        $this->client = new Client();
    }

    /**
     * Envía una notificación push a una lista de player_ids.
     *
     * Si la lista supera MAX_PLAYER_IDS_POR_REQUEST, se divide en batches.
     *
     * @param  array   $playerIds  Lista de OneSignal player_ids
     * @param  string  $titulo     Título de la notificación
     * @param  string  $mensaje    Cuerpo de la notificación
     * @param  array   $datos      Datos adicionales (payload para la app)
     * @return array   ['success' => bool, 'id' => string|null, 'recipients' => int, 'error' => string|null]
     */
    public function enviarAPlayerIds(array $playerIds, string $titulo, string $mensaje, array $datos = []): array
    {
        if (empty($playerIds)) {
            return ['success' => false, 'id' => null, 'recipients' => 0, 'error' => 'No hay player_ids destinatarios'];
        }

        // Guard: si app_id no está configurado, fallar rápido con un error claro
        // en lugar de mandar un payload malformado a OneSignal.
        // Causa más común: ONESIGNAL_APP_ID (o ONESIGNAL_APP_ID_DEV) no definido en .env.
        if (empty($this->appId)) {
            Log::error('OneSignalService: app_id no configurado — notificación no enviada', [
                'accion'  => 'Definir ONESIGNAL_APP_ID_DEV (local/staging) u ONESIGNAL_APP_ID (producción) en .env',
                'titulo'  => $titulo,
            ]);
            return ['success' => false, 'id' => null, 'recipients' => 0, 'error' => 'ONESIGNAL_APP_ID no configurado en este entorno'];
        }

        // Si hay más de MAX_PLAYER_IDS_POR_REQUEST, dividir en batches
        $chunks = array_chunk($playerIds, self::MAX_PLAYER_IDS_POR_REQUEST);

        $totalRecipients = 0;
        $lastId          = null;

        foreach ($chunks as $chunk) {
            $resultado = $this->enviarChunk($chunk, $titulo, $mensaje, $datos);

            if (!$resultado['success']) {
                return $resultado;
            }

            $totalRecipients += $resultado['recipients'];
            $lastId           = $resultado['id'];
        }

        return [
            'success'    => true,
            'id'         => $lastId,
            'recipients' => $totalRecipients,
            'error'      => null,
        ];
    }

    /**
     * Envía un único batch de hasta MAX_PLAYER_IDS_POR_REQUEST player_ids.
     */
    protected function enviarChunk(array $playerIds, string $titulo, string $mensaje, array $datos): array
    {
        $payload = [
            'app_id'             => $this->appId,
            'include_player_ids' => $playerIds,
            'headings'           => ['en' => $titulo, 'es' => $titulo],
            'contents'           => ['en' => $mensaje, 'es' => $mensaje],
            'data'               => $datos,
        ];

        try {
            $response = $this->client->post(self::API_URL, [
                'headers' => [
                    'Authorization' => 'Basic ' . $this->apiKey,
                    'Content-Type'  => 'application/json',
                ],
                'json' => $payload,
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            // OneSignal devuelve errors[] con player_ids inválidos.
            // Los marcamos como inactivos para no volver a usarlos.
            if (!empty($body['errors'])) {
                $invalidIds = is_array($body['errors']) ? $body['errors'] : [];
                Log::warning('OneSignalService: player_ids inválidos — se desactivarán', [
                    'errors' => $invalidIds,
                ]);
                if (!empty($invalidIds)) {
                    Dispositivo::whereIn('player_id', $invalidIds)
                        ->update(['activo' => false]);
                }
            }

            return [
                'success'    => true,
                'id'         => $body['id'] ?? null,
                'recipients' => $body['recipients'] ?? 0,
                'error'      => null,
            ];

        } catch (RequestException $e) {
            $responseBody = $e->hasResponse()
                ? $e->getResponse()->getBody()->getContents()
                : null;

            Log::error('OneSignalService: error HTTP al enviar notificación', [
                'message'       => $e->getMessage(),
                'response_body' => $responseBody,
                'player_ids'    => $playerIds,
            ]);

            return [
                'success'    => false,
                'id'         => null,
                'recipients' => 0,
                'error'      => $e->getMessage(),
            ];

        } catch (\Exception $e) {
            Log::error('OneSignalService: error inesperado', [
                'message'    => $e->getMessage(),
                'player_ids' => $playerIds,
            ]);

            return [
                'success'    => false,
                'id'         => null,
                'recipients' => 0,
                'error'      => $e->getMessage(),
            ];
        }
    }
}
