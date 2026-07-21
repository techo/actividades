<?php

namespace Tests\Feature;

use App\ActividadFactory;
use App\Donation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Tarea #12 — Tests web: webhook de Stripe (StripeController@webhook).
 * Endpoint crítico: procesa confirmaciones de pago. Un fallo silencioso deja
 * inscripciones sin confirmar.
 *
 * La firma se valida con \Stripe\Webhook::constructEvent (estática), así que se
 * genera una firma válida con HMAC-SHA256 en el test: ninguna llamada real a Stripe.
 */
class StripeWebhookWebTest extends TestCase
{
    use RefreshDatabase;

    const WEBHOOK_SECRET = 'whsec_test_secret';

    private function paisConStripe()
    {
        return factory('App\Pais')->create([
            'config_pago' => json_encode([
                'stripe_secret'         => 'sk_test_x',
                'stripe_webhook_secret' => self::WEBHOOK_SECRET,
            ]),
        ]);
    }

    /** Firma el payload como lo hace Stripe (header t=...,v1=HMAC). */
    private function postWebhook($paisId, array $event, string $secret = self::WEBHOOK_SECRET)
    {
        $payload   = json_encode($event);
        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);
        $header    = "t={$timestamp},v1={$signature}";

        return $this->call(
            'POST',
            "/stripe/webhook/{$paisId}",
            [], [], [],
            ['HTTP_STRIPE_SIGNATURE' => $header, 'CONTENT_TYPE' => 'application/json'],
            $payload
        );
    }

    /** @test */
    public function checkout_session_completed_marca_la_inscripcion_como_pagada()
    {
        Mail::fake();
        $this->seed('PermisosSeeder');

        $pais      = $this->paisConStripe();
        $persona   = factory('App\Persona')->create();
        $actividad = app(ActividadFactory::class)->conPais($pais->id)->agregarPuntoConInscriptos(0)->create();
        $inscripcion = factory('App\Inscripcion')->create([
            'idActividad'      => $actividad->idActividad,
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idPersona'        => $persona->idPersona,
            'pago'             => 0,
        ]);

        $event = [
            'id'   => 'evt_1',
            'type' => 'checkout.session.completed',
            'data' => ['object' => [
                'id'             => 'cs_test_1',
                'metadata'       => ['inscripcion_id' => $inscripcion->idInscripcion],
                'payment_status' => 'paid',
                'amount_total'   => 10000,
                'currency'       => 'ars',
                'payment_intent' => 'pi_test_1',
            ]],
        ];

        $this->postWebhook($pais->id, $event)->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', [
            'idInscripcion' => $inscripcion->idInscripcion,
            'pago'          => 1,
            'metodo_pago'   => 'stripe',
        ]);
    }

    /** @test */
    public function firma_invalida_devuelve_400()
    {
        $pais = $this->paisConStripe();

        $event = ['id' => 'evt_1', 'type' => 'checkout.session.completed', 'data' => ['object' => []]];

        // Firmado con un secret distinto → la verificación falla.
        $this->postWebhook($pais->id, $event, 'whsec_secret_equivocado')
            ->assertStatus(400);
    }

    /** @test */
    public function evento_desconocido_responde_200_sin_efectos()
    {
        $pais      = $this->paisConStripe();
        $persona   = factory('App\Persona')->create();
        $actividad = app(ActividadFactory::class)->conPais($pais->id)->agregarPuntoConInscriptos(0)->create();
        $inscripcion = factory('App\Inscripcion')->create([
            'idActividad'      => $actividad->idActividad,
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idPersona'        => $persona->idPersona,
            'pago'             => 0,
        ]);

        $event = ['id' => 'evt_x', 'type' => 'customer.created', 'data' => ['object' => ['id' => 'cus_1']]];

        $this->postWebhook($pais->id, $event)->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', [
            'idInscripcion' => $inscripcion->idInscripcion,
            'pago'          => 0,
        ]);
    }

    /** @test */
    public function pais_inexistente_devuelve_404()
    {
        $event = ['id' => 'evt_1', 'type' => 'checkout.session.completed', 'data' => ['object' => []]];

        $this->postWebhook(999999, $event)->assertStatus(404);
    }

    // =========================================================================
    // Tarea #30 — Confirmación del flujo mobile: payment_intent.succeeded/failed.
    // El pago mobile crea un PI (InscripcionStripeController) y se confirma por
    // este webhook, no por checkout.session.completed.
    // =========================================================================

    /** Arma una inscripción impaga con su Donation pendiente vinculada al PI. */
    private function inscripcionConPiPendiente($pais, $piId, array $actividadExtra = [])
    {
        $persona   = factory('App\Persona')->create();
        $actividad = app(ActividadFactory::class)->conPais($pais->id)->agregarPuntoConInscriptos(0)->create($actividadExtra);
        $inscripcion = factory('App\Inscripcion')->create([
            'idActividad'              => $actividad->idActividad,
            'idPuntoEncuentro'         => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idPersona'                => $persona->idPersona,
            'pago'                     => 0,
            'stripe_payment_intent_id' => $piId,
        ]);
        $donation = Donation::create([
            'person_id'                => $persona->idPersona,
            'inscripcion_id'           => $inscripcion->idInscripcion,
            'stripe_payment_intent_id' => $piId,
            'amount'                   => 10000,
            'currency'                 => 'ars',
            'mode'                     => 'one_time',
            'status'                   => Donation::STATUS_PENDING,
            'source'                   => 'inscripcion',
            'idempotency_key'          => 'idem-' . $piId,
        ]);

        return [$inscripcion, $donation];
    }

    private function piEvent($type, $piId, array $extra = [])
    {
        return [
            'id'   => 'evt_' . $piId,
            'type' => $type,
            'data' => ['object' => array_merge([
                'id'              => $piId,
                'object'          => 'payment_intent',
                'amount_received' => 10000,
                'currency'        => 'ars',
                'metadata'        => [], // lo completa cada test
            ], $extra)],
        ];
    }

    /** @test */
    public function payment_intent_succeeded_marca_la_inscripcion_pagada_y_confirma_la_donation()
    {
        Mail::fake();
        $pais = $this->paisConStripe();
        [$inscripcion, $donation] = $this->inscripcionConPiPendiente($pais, 'pi_ok_1');

        $event = $this->piEvent('payment_intent.succeeded', 'pi_ok_1', [
            'metadata' => ['inscripcion_id' => $inscripcion->idInscripcion],
        ]);

        $this->postWebhook($pais->id, $event)->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', [
            'idInscripcion' => $inscripcion->idInscripcion,
            'pago'          => 1,
            'metodo_pago'   => 'stripe_api',
        ]);
        $this->assertDatabaseHas('donations', [
            'id'     => $donation->id,
            'status' => Donation::STATUS_SUCCEEDED,
        ]);
    }

    /** @test */
    public function payment_intent_succeeded_es_idempotente()
    {
        Mail::fake();
        $pais = $this->paisConStripe();
        [$inscripcion] = $this->inscripcionConPiPendiente($pais, 'pi_ok_2');
        // Ya procesada por el mismo método.
        $inscripcion->update(['pago' => 1, 'metodo_pago' => 'stripe_api']);

        $event = $this->piEvent('payment_intent.succeeded', 'pi_ok_2', [
            'metadata' => ['inscripcion_id' => $inscripcion->idInscripcion],
        ]);

        $this->postWebhook($pais->id, $event)->assertStatus(200);

        // No se reenvía el mail de confirmación en la segunda pasada.
        Mail::assertNothingQueued();
    }

    /** @test */
    public function payment_intent_payment_failed_marca_la_donation_como_failed()
    {
        $pais = $this->paisConStripe();
        [$inscripcion, $donation] = $this->inscripcionConPiPendiente($pais, 'pi_fail_1');

        $event = $this->piEvent('payment_intent.payment_failed', 'pi_fail_1', [
            'metadata' => ['inscripcion_id' => $inscripcion->idInscripcion],
        ]);

        $this->postWebhook($pais->id, $event)->assertStatus(200);

        $this->assertDatabaseHas('donations', [
            'id'     => $donation->id,
            'status' => Donation::STATUS_FAILED,
        ]);
        // El pago rechazado NO marca la inscripción como pagada.
        $this->assertDatabaseHas('Inscripcion', [
            'idInscripcion' => $inscripcion->idInscripcion,
            'pago'          => 0,
        ]);
    }
}
