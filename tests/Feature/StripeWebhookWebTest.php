<?php

namespace Tests\Feature;

use App\ActividadFactory;
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
}
