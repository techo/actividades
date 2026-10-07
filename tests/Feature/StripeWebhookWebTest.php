<?php

namespace Tests\Feature;

use App\ActividadFactory;
use App\Donation;
use App\Services\StripeReembolsos;
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

    protected function setUp()
    {
        parent::setUp();
        // Por defecto ningún PI está reembolsado: los tests no llaman a Stripe.
        $this->simularReembolso(false);
    }

    private function simularReembolso(bool $reembolsado)
    {
        $this->app->instance(StripeReembolsos::class, new class($reembolsado) extends StripeReembolsos {
            private $reembolsado;
            public function __construct($reembolsado) { $this->reembolsado = $reembolsado; }
            public function piReembolsado(?string $paymentIntentId): bool { return $this->reembolsado; }
        });
    }

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

    /** Inscripción impaga lista para recibir un evento de Checkout. */
    private function inscripcionImpaga($pais)
    {
        $persona   = factory('App\Persona')->create();
        $actividad = app(ActividadFactory::class)->conPais($pais->id)->agregarPuntoConInscriptos(0)->create();

        return factory('App\Inscripcion')->create([
            'idActividad'      => $actividad->idActividad,
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idPersona'        => $persona->idPersona,
            'pago'             => 0,
        ]);
    }

    private function eventoCheckout($type, $inscripcion, $paymentStatus)
    {
        return [
            'id'   => 'evt_' . $type,
            'type' => $type,
            'data' => ['object' => [
                'id'             => 'cs_test_pix',
                'metadata'       => ['inscripcion_id' => $inscripcion->idInscripcion],
                'payment_status' => $paymentStatus,
                'amount_total'   => 4500,
                'currency'       => 'brl',
                'payment_intent' => 'pi_test_pix',
            ]],
        ];
    }

    /** @test */
    public function pix_checkout_completed_unpaid_no_marca_pago_y_async_succeeded_si()
    {
        Mail::fake();
        $this->seed('PermisosSeeder');

        $pais        = $this->paisConStripe();
        $inscripcion = $this->inscripcionImpaga($pais);

        // Al generar el QR: completed con unpaid → todavía no está pagada.
        $this->postWebhook($pais->id, $this->eventoCheckout('checkout.session.completed', $inscripcion, 'unpaid'))
            ->assertStatus(200);
        $this->assertDatabaseHas('Inscripcion', ['idInscripcion' => $inscripcion->idInscripcion, 'pago' => 0]);

        // Cuando paga el PIX: async_payment_succeeded con paid → pagada.
        $this->postWebhook($pais->id, $this->eventoCheckout('checkout.session.async_payment_succeeded', $inscripcion, 'paid'))
            ->assertStatus(200);
        $this->assertDatabaseHas('Inscripcion', [
            'idInscripcion'            => $inscripcion->idInscripcion,
            'pago'                     => 1,
            'metodo_pago'              => 'stripe',
            'moneda'                   => 'BRL',
            'stripe_payment_intent_id' => 'pi_test_pix',
        ]);
    }

    /** @test */
    public function pix_async_payment_failed_no_marca_pago()
    {
        $pais        = $this->paisConStripe();
        $inscripcion = $this->inscripcionImpaga($pais);

        $this->postWebhook($pais->id, $this->eventoCheckout('checkout.session.async_payment_failed', $inscripcion, 'unpaid'))
            ->assertStatus(200);
        $this->assertDatabaseHas('Inscripcion', ['idInscripcion' => $inscripcion->idInscripcion, 'pago' => 0]);
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

    // =========================================================================
    // Reembolsos: un reintento del webhook no debe marcar pagado un PI ya
    // reembolsado, y charge.refunded devuelve la inscripción a impaga.
    // =========================================================================

    /** @test */
    public function checkout_completed_de_un_pi_reembolsado_no_marca_pago()
    {
        Mail::fake();
        $this->simularReembolso(true);
        $pais        = $this->paisConStripe();
        $inscripcion = $this->inscripcionImpaga($pais);

        $this->postWebhook($pais->id, $this->eventoCheckout('checkout.session.completed', $inscripcion, 'paid'))
            ->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', ['idInscripcion' => $inscripcion->idInscripcion, 'pago' => 0]);
        Mail::assertNothingQueued();
    }

    /** @test */
    public function payment_intent_succeeded_de_un_pi_reembolsado_no_marca_pago()
    {
        Mail::fake();
        $this->simularReembolso(true);
        $pais = $this->paisConStripe();
        list($inscripcion) = $this->inscripcionConPiPendiente($pais, 'pi_reemb_app');

        $event = $this->piEvent('payment_intent.succeeded', 'pi_reemb_app', [
            'metadata' => ['inscripcion_id' => $inscripcion->idInscripcion],
        ]);

        $this->postWebhook($pais->id, $event)->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', ['idInscripcion' => $inscripcion->idInscripcion, 'pago' => 0]);
    }

    private function inscripcionPagada($pais, $piId, $metodo)
    {
        $inscripcion = $this->inscripcionImpaga($pais);
        $inscripcion->update([
            'pago'                     => 1,
            'montoPago'                => 45,
            'fechaPago'                => now(),
            'metodo_pago'              => $metodo,
            'stripe_payment_intent_id' => $piId,
        ]);

        return $inscripcion;
    }

    private function eventoReembolso($piId, $refunded)
    {
        return [
            'id'   => 'evt_refund_' . $piId,
            'type' => 'charge.refunded',
            'data' => ['object' => [
                'id'              => 'ch_' . $piId,
                'object'          => 'charge',
                'payment_intent'  => $piId,
                'refunded'        => $refunded,
                'amount'          => 4500,
                'amount_refunded' => $refunded ? 4500 : 1000,
            ]],
        ];
    }

    /** @test */
    public function charge_refunded_total_vuelve_la_inscripcion_a_impaga()
    {
        $pais        = $this->paisConStripe();
        $web         = $this->inscripcionPagada($pais, 'pi_web_refund', 'stripe');
        $app         = $this->inscripcionPagada($pais, 'pi_app_refund', 'stripe_api');

        $this->postWebhook($pais->id, $this->eventoReembolso('pi_web_refund', true))->assertStatus(200);
        $this->postWebhook($pais->id, $this->eventoReembolso('pi_app_refund', true))->assertStatus(200);

        foreach ([[$web, 'pi_web_refund'], [$app, 'pi_app_refund']] as list($inscripcion, $pi)) {
            $this->assertDatabaseHas('Inscripcion', [
                'idInscripcion'            => $inscripcion->idInscripcion,
                'pago'                     => 0,
                'fechaPago'                => null,
                'metodo_pago'              => null,
                'stripe_payment_intent_id' => $pi,
            ]);
        }
    }

    /** @test */
    public function charge_refunded_parcial_no_cambia_el_pago()
    {
        $pais        = $this->paisConStripe();
        $inscripcion = $this->inscripcionPagada($pais, 'pi_parcial', 'stripe');

        $this->postWebhook($pais->id, $this->eventoReembolso('pi_parcial', false))->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', ['idInscripcion' => $inscripcion->idInscripcion, 'pago' => 1]);
    }

    /** @test */
    public function charge_refunded_no_toca_pagos_que_no_son_de_stripe()
    {
        $pais        = $this->paisConStripe();
        $inscripcion = $this->inscripcionPagada($pais, 'pi_manual', 'transferencia');

        $this->postWebhook($pais->id, $this->eventoReembolso('pi_manual', true))->assertStatus(200);

        $this->assertDatabaseHas('Inscripcion', ['idInscripcion' => $inscripcion->idInscripcion, 'pago' => 1]);
    }
}
