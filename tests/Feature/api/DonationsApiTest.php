<?php

namespace Tests\Feature\api;

use App\Donation;
use App\Services\StripePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Mockery;
use Tests\TestCase;

/**
 * Tarea #18 — Tests de la API mobile: donaciones y webhooks Stripe.
 *
 * StripePaymentService se inyecta por constructor en DonationController y
 * DonationWebhookController, así que se mockea por el container: ningún test
 * hace llamadas reales a Stripe.
 */
class DonationsApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function create_payment_intent_valida_el_input()
    {
        Passport::actingAs(factory('App\Persona')->create());

        // amount/currency/source faltantes o inválidos → 422 (antes de tocar Stripe).
        $this->postJson('/api/donations/stripe/payment-intent', [
            'amount'   => 1,        // < AMOUNT_MIN
            'currency' => 'xxx',    // no permitida
            'source'   => 'invalido',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['amount', 'currency', 'source']);
    }

    /** @test */
    public function create_payment_intent_sin_autenticacion_devuelve_401()
    {
        $this->postJson('/api/donations/stripe/payment-intent', [])
            ->assertStatus(401);
    }

    /** @test */
    public function webhook_con_firma_invalida_devuelve_400()
    {
        $mock = Mockery::mock(StripePaymentService::class);
        $mock->shouldReceive('constructWebhookEvent')
            ->andThrow(new \Stripe\Exception\SignatureVerificationException('firma inválida'));
        $this->app->instance(StripePaymentService::class, $mock);

        $this->postJson('/api/donations/stripe/webhook', ['cualquier' => 'cosa'])
            ->assertStatus(400);
    }

    /** @test */
    public function webhook_payment_intent_succeeded_confirma_la_donacion()
    {
        $persona = factory('App\Persona')->create();

        $donation = Donation::create([
            'person_id'                => $persona->idPersona,
            'amount'                   => 5000,
            'currency'                 => 'ars',
            'mode'                     => 'one_time',
            'source'                   => 'home_pill',
            'status'                   => Donation::STATUS_PENDING,
            'stripe_payment_intent_id' => 'pi_test_123',
            'idempotency_key'          => 'idem-test-1',
        ]);

        // Evento Stripe falso (sin red): el controller navega
        // $event->data->object->id, $event->id, $event->type, $event->created.
        $event = \Stripe\Event::constructFrom([
            'id'      => 'evt_test_1',
            'type'    => 'payment_intent.succeeded',
            'created' => time(),
            'data'    => ['object' => ['id' => 'pi_test_123', 'object' => 'payment_intent']],
        ]);

        $mock = Mockery::mock(StripePaymentService::class);
        $mock->shouldReceive('constructWebhookEvent')->andReturn($event);
        $this->app->instance(StripePaymentService::class, $mock);

        $this->postJson('/api/donations/stripe/webhook', [])
            ->assertStatus(200);

        $this->assertDatabaseHas('donations', [
            'id'              => $donation->id,
            'status'          => Donation::STATUS_SUCCEEDED,
            'stripe_event_id' => 'evt_test_1',
        ]);
    }
}
