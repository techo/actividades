<?php

namespace App\Services\Salesforce;

use App\Persona;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * ¿La persona es socio/donante de TECHO?
 * (dato en Salesforce: Contact.npsp__Sustainer__c, matcheado por DNI__c).
 *
 * Solo aplica en el país configurado (Argentina = 13). Fuera de ese país, con el
 * feature flag apagado, o sin DNI cargado → devuelve false SIN llamar a Salesforce.
 *
 * Política de fallo: FAIL-CLOSED. Ante cualquier error de Salesforce (timeout,
 * 5xx, token que no refresca) se asume NO socio (la persona paga normal). Nunca
 * se regala la exención por un error, y los errores NO se cachean.
 */
class SocioService
{
    /** @var SalesforceClient */
    protected $client;

    /** Memo in-request por idPersona: un mismo request no repite el lookup. */
    protected $memo = [];

    public function __construct(SalesforceClient $client)
    {
        $this->client = $client;
    }

    /**
     * @return bool true solo si Salesforce confirma que la persona es Sustainer.
     */
    public function esSocio(Persona $persona): bool
    {
        if (!config('services.salesforce.enabled')) {
            return false;
        }

        $paisSocio = (int) config('services.salesforce.socio_pais_id', 13);
        if ((int) $persona->idPais !== $paisSocio) {
            return false;
        }

        $dni = $this->normalizarDni($persona->dni);
        if ($dni === '') {
            return false;
        }

        $idPersona = $persona->idPersona;
        if (array_key_exists($idPersona, $this->memo)) {
            return $this->memo[$idPersona];
        }

        $ttl      = (int) config('services.salesforce.cache_ttl', 720);
        $cacheKey = 'socio:ar:' . $dni;

        try {
            // Solo se cachean resultados DETERMINADOS (true/false). Si la consulta
            // falla, consultarSalesforce() lanza excepción antes del put, así que
            // el error no envenena la caché (fail-closed no se persiste).
            $resultado = Cache::remember($cacheKey, $ttl, function () use ($dni) {
                return $this->consultarSalesforce($dni);
            });
        } catch (\Exception $e) {
            Log::warning('SocioService: fallo verificando socio (fail-closed → no socio)', [
                'idPersona' => $idPersona,
                'message'   => $e->getMessage(),
            ]);
            $resultado = false;
        }

        $this->memo[$idPersona] = $resultado;

        return $resultado;
    }

    /**
     * Consulta Salesforce por DNI. Devuelve true si algún Contact es Sustainer.
     *
     * @throws \RuntimeException si Salesforce falla (para no cachear el error)
     */
    private function consultarSalesforce(string $dni): bool
    {
        // El DNI viene normalizado a solo dígitos, así que la interpolación en la
        // SOQL es segura (no puede contener comillas → sin riesgo de inyección).
        $soql = "SELECT npsp__Sustainer__c FROM Contact WHERE DNI__c='" . $dni . "'";

        $body = $this->client->query($soql);

        if (empty($body['records'])) {
            return false; // totalSize 0 / records:[] → DNI no encontrado → no socio.
        }

        foreach ($body['records'] as $record) {
            if (!empty($record['npsp__Sustainer__c'])) {
                return true;
            }
        }

        return false;
    }

    /** Normaliza el DNI para el match con Salesforce: solo dígitos. */
    private function normalizarDni(?string $dni): string
    {
        return preg_replace('/\D+/', '', (string) $dni);
    }
}
