<?php

namespace Tests\Support;

use Stripe\HttpClient\ClientInterface;

/**
 * HTTP client falso para el SDK de Stripe (v8). Se inyecta con
 * \Stripe\ApiRequestor::setHttpClient() y devuelve respuestas predefinidas,
 * de modo que las llamadas estáticas del SDK
 * (\Stripe\Checkout\Session::create, \Stripe\PaymentIntent::create/retrieve)
 * funcionen en los tests sin tocar la red.
 *
 * Cola FIFO: cada elemento es [bodyArray, statusCode]. Si el statusCode es
 * >= 400 y el body trae una clave "error", el SDK lanza la excepción
 * correspondiente (útil para simular un pago rechazado / error de API).
 */
class FakeStripeHttpClient implements ClientInterface
{
    /** @var array<int, array{0: array, 1: int}> */
    private $responses;

    /** @var array<int, array{method: string, url: string, params: array}> */
    public $requests = [];

    public function __construct(array $responses)
    {
        $this->responses = $responses;
    }

    public function request($method, $absUrl, $headers, $params, $hasFile)
    {
        $this->requests[] = [
            'method' => $method,
            'url'    => $absUrl,
            'params' => $params,
        ];

        if (empty($this->responses)) {
            throw new \RuntimeException(
                "FakeStripeHttpClient: llamada inesperada a Stripe {$method} {$absUrl} (no quedan respuestas en la cola)."
            );
        }

        [$body, $code] = array_shift($this->responses);

        return [json_encode($body), $code, []];
    }
}
