<?php

namespace Tests\Concerns;

use Stripe\ApiRequestor;
use Tests\Support\FakeStripeHttpClient;

/**
 * Instala/limpia el HTTP client falso de Stripe en los tests que ejercitan
 * las llamadas estáticas del SDK (Checkout Session / PaymentIntent).
 *
 * Usar resetStripeHttpClient() en tearDown para no filtrar el client entre tests.
 */
trait FakesStripe
{
    /** @var FakeStripeHttpClient|null */
    protected $fakeStripe;

    /**
     * @param array<int, array{0: array, 1: int}> $responses Cola FIFO de [body, statusCode].
     */
    protected function fakeStripeHttp(array $responses): FakeStripeHttpClient
    {
        $this->fakeStripe = new FakeStripeHttpClient($responses);
        ApiRequestor::setHttpClient($this->fakeStripe);

        return $this->fakeStripe;
    }

    /** Restaura el client por defecto del SDK (se re-instancia lazy en la próxima llamada). */
    protected function resetStripeHttpClient(): void
    {
        ApiRequestor::setHttpClient(null);
        $this->fakeStripe = null;
    }
}
