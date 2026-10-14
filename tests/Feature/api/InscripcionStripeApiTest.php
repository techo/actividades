<?php

namespace Tests\Feature\api;

use App\ActividadFactory;
use App\Donation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Tests\Concerns\FakesStripe;
use Tests\TestCase;

/**
 * Tarea #30 (Etapa 1) — Cobertura del flujo de cobro Stripe mobile:
 * InscripcionStripeController@createPaymentIntent (POST
 * /api/inscripciones/{id}/stripe/payment-intent).
 *
 * El controller llama estáticamente a \Stripe\PaymentIntent::create/retrieve;
 * se inyecta un HTTP client falso (FakesStripe) para no tocar la red.
 * La confirmación del pago (payment_intent.succeeded/failed) llega por el
 * webhook y se cubre en StripeWebhookWebTest.
 */
class InscripcionStripeApiTest extends TestCase
{
    use RefreshDatabase, FakesStripe;

    protected function tearDown(): void
    {
        $this->resetStripeHttpClient();
        parent::tearDown();
    }

    private function paisConStripe()
    {
        return factory('App\Pais')->create([
            'config_pago' => json_encode(['stripe_secret' => 'sk_test_x']),
        ]);
    }

    /** Crea una inscripción impaga de $persona en una actividad con pago en el país dado. */
    private function inscripcionImpaga($persona, $pais, array $actividadExtra = [])
    {
        $actividad = app(ActividadFactory::class)
            ->conPais($pais->id)
            ->agregarPuntoConInscriptos(0)
            ->create($actividadExtra);

        return factory('App\Inscripcion')->create([
            'idActividad'      => $actividad->idActividad,
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idPersona'        => $persona->idPersona,
            'pago'             => 0,
        ]);
    }

    /** @test */
    public function crea_un_payment_intent_y_persiste_la_donation()
    {
        $persona = factory('App\Persona')->create();
        Passport::actingAs($persona);
        $pais        = $this->paisConStripe();
        $inscripcion = $this->inscripcionImpaga($persona, $pais);

        $this->fakeStripeHttp([
            [['id' => 'pi_test_1', 'object' => 'payment_intent', 'client_secret' => 'pi_test_1_secret_abc', 'status' => 'requires_payment_method'], 200],
        ]);

        $this->postJson('/api/inscripciones/' . $inscripcion->idInscripcion . '/stripe/payment-intent')
            ->assertStatus(200)
            ->assertJson([
                'client_secret' => 'pi_test_1_secret_abc',
                'intent_id'     => 'pi_test_1',
                'amount'        => 10000, // montoMin 100.00 * 100
                'currency'      => 'ars',
            ]);

        $this->assertDatabaseHas('donations', [
            'inscripcion_id'           => $inscripcion->idInscripcion,
            'person_id'                => $persona->idPersona,
            'stripe_payment_intent_id' => 'pi_test_1',
            'status'                   => Donation::STATUS_PENDING,
            'source'                   => 'inscripcion',
        ]);

        // El PI queda guardado en la inscripción para que el webhook lo encuentre.
        $this->assertDatabaseHas('Inscripcion', [
            'idInscripcion'            => $inscripcion->idInscripcion,
            'stripe_payment_intent_id' => 'pi_test_1',
        ]);
    }

    /** @test */
    public function reutiliza_el_payment_intent_pendiente_existente_sin_crear_otra_donation()
    {
        $persona = factory('App\Persona')->create();
        Passport::actingAs($persona);
        $pais        = $this->paisConStripe();
        $inscripcion = $this->inscripcionImpaga($persona, $pais);

        // Ya existe una donation pendiente con un PI para esta inscripción.
        Donation::create([
            'person_id'                => $persona->idPersona,
            'inscripcion_id'           => $inscripcion->idInscripcion,
            'stripe_payment_intent_id' => 'pi_existing',
            'amount'                   => 10000,
            'currency'                 => 'ars',
            'mode'                     => 'one_time',
            'status'                   => Donation::STATUS_PENDING,
            'source'                   => 'inscripcion',
            'idempotency_key'          => 'idem-existing',
        ]);

        // El controller hace retrieve (no create) del PI existente.
        $this->fakeStripeHttp([
            [['id' => 'pi_existing', 'object' => 'payment_intent', 'client_secret' => 'pi_existing_secret'], 200],
        ]);

        $this->postJson('/api/inscripciones/' . $inscripcion->idInscripcion . '/stripe/payment-intent')
            ->assertStatus(200)
            ->assertJson(['intent_id' => 'pi_existing', 'client_secret' => 'pi_existing_secret']);

        // No se creó una segunda donation.
        $this->assertEquals(1, Donation::where('inscripcion_id', $inscripcion->idInscripcion)->count());
    }

    /** PI PIX confirmado server-side, tal como lo devuelve Stripe (requires_action + QR). */
    private function piPix(string $id, int $expiresAt): array
    {
        return [
            'id'            => $id,
            'object'        => 'payment_intent',
            'client_secret' => $id . '_secret',
            'status'        => 'requires_action',
            'next_action'   => [
                'type'                => 'pix_display_qr_code',
                'pix_display_qr_code' => [
                    'data'          => 'br.gov.bcb.pix-' . $id,
                    'image_url_png' => 'https://qr.stripe.com/' . $id . '.png',
                    'expires_at'    => $expiresAt,
                ],
            ],
        ];
    }

    /** @test */
    public function pix_confirma_server_side_y_devuelve_el_bloque_pix()
    {
        $persona = factory('App\Persona')->create(['nombres' => 'Ana', 'apellidoPaterno' => 'Souza', 'dni' => '12345678909']);
        Passport::actingAs($persona);
        $pais        = $this->paisConStripe();
        $inscripcion = $this->inscripcionImpaga($persona, $pais, ['moneda' => 'BRL']);

        $expira = now()->addDay()->timestamp;
        $fake   = $this->fakeStripeHttp([[$this->piPix('pi_pix_1', $expira), 200]]);

        $this->postJson('/api/inscripciones/' . $inscripcion->idInscripcion . '/stripe/payment-intent', ['paymentMethod' => 'pix'])
            ->assertStatus(200)
            ->assertJson([
                'intent_id' => 'pi_pix_1',
                'currency'  => 'brl',
                'pix'       => [
                    'copy_paste_code' => 'br.gov.bcb.pix-pi_pix_1',
                    'qr_code_url'     => 'https://qr.stripe.com/pi_pix_1.png',
                    'expires_at'      => \Carbon\Carbon::createFromTimestamp($expira)->toIso8601String(),
                ],
            ]);

        $params = $fake->requests[0]['params'];
        $this->assertEquals(['pix'], $params['payment_method_types']);
        $this->assertTrue((bool) $params['confirm']);
        $this->assertEquals('Ana Souza', $params['payment_method_data']['billing_details']['name']);
        $this->assertEquals($inscripcion->idInscripcion, $params['metadata']['inscripcion_id']);
        $this->assertArrayNotHasKey('automatic_payment_methods', $params);

        $this->assertDatabaseHas('donations', [
            'inscripcion_id'           => $inscripcion->idInscripcion,
            'stripe_payment_intent_id' => 'pi_pix_1',
            'payment_method_type'      => 'pix',
            'status'                   => Donation::STATUS_PENDING,
        ]);
    }

    /** @test */
    public function pix_en_moneda_distinta_de_brl_devuelve_422()
    {
        $persona = factory('App\Persona')->create();
        Passport::actingAs($persona);
        $pais        = $this->paisConStripe();
        $inscripcion = $this->inscripcionImpaga($persona, $pais); // ars

        $this->postJson('/api/inscripciones/' . $inscripcion->idInscripcion . '/stripe/payment-intent', ['paymentMethod' => 'pix'])
            ->assertStatus(422);

        $this->assertDatabaseMissing('donations', ['inscripcion_id' => $inscripcion->idInscripcion]);
    }

    /** @test */
    public function metodo_de_pago_invalido_devuelve_422()
    {
        $persona = factory('App\Persona')->create();
        Passport::actingAs($persona);
        $pais        = $this->paisConStripe();
        $inscripcion = $this->inscripcionImpaga($persona, $pais);

        $this->postJson('/api/inscripciones/' . $inscripcion->idInscripcion . '/stripe/payment-intent', ['paymentMethod' => 'boleto'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('paymentMethod');
    }

    private function donationPendiente($persona, $inscripcion, string $piId, string $metodo)
    {
        return Donation::create([
            'person_id'                => $persona->idPersona,
            'inscripcion_id'           => $inscripcion->idInscripcion,
            'stripe_payment_intent_id' => $piId,
            'amount'                   => 10000,
            'currency'                 => 'brl',
            'mode'                     => 'one_time',
            'payment_method_type'      => $metodo,
            'status'                   => Donation::STATUS_PENDING,
            'source'                   => 'inscripcion',
            'idempotency_key'          => 'idem-' . $piId,
        ]);
    }

    /** @test */
    public function pix_vigente_se_reutiliza_sin_crear_otro()
    {
        $persona = factory('App\Persona')->create();
        Passport::actingAs($persona);
        $pais        = $this->paisConStripe();
        $inscripcion = $this->inscripcionImpaga($persona, $pais, ['moneda' => 'BRL']);
        $this->donationPendiente($persona, $inscripcion, 'pi_pix_old', 'pix');

        $this->fakeStripeHttp([[$this->piPix('pi_pix_old', now()->addHours(3)->timestamp), 200]]);

        $this->postJson('/api/inscripciones/' . $inscripcion->idInscripcion . '/stripe/payment-intent', ['paymentMethod' => 'pix'])
            ->assertStatus(200)
            ->assertJson(['intent_id' => 'pi_pix_old', 'pix' => ['copy_paste_code' => 'br.gov.bcb.pix-pi_pix_old']]);

        $this->assertEquals(1, Donation::where('inscripcion_id', $inscripcion->idInscripcion)->count());
    }

    /** @test */
    public function pix_vencido_genera_uno_nuevo()
    {
        $persona = factory('App\Persona')->create();
        Passport::actingAs($persona);
        $pais        = $this->paisConStripe();
        $inscripcion = $this->inscripcionImpaga($persona, $pais, ['moneda' => 'BRL']);
        $this->donationPendiente($persona, $inscripcion, 'pi_pix_old', 'pix');

        $this->fakeStripeHttp([
            [$this->piPix('pi_pix_old', now()->subHour()->timestamp), 200], // retrieve: vencido
            [$this->piPix('pi_pix_new', now()->addDay()->timestamp), 200],  // create
        ]);

        $this->postJson('/api/inscripciones/' . $inscripcion->idInscripcion . '/stripe/payment-intent', ['paymentMethod' => 'pix'])
            ->assertStatus(200)
            ->assertJson(['intent_id' => 'pi_pix_new']);

        $this->assertDatabaseHas('Inscripcion', [
            'idInscripcion'            => $inscripcion->idInscripcion,
            'stripe_payment_intent_id' => 'pi_pix_new',
        ]);
    }

    /** @test */
    public function un_pix_pendiente_no_se_devuelve_cuando_se_pide_tarjeta()
    {
        $persona = factory('App\Persona')->create();
        Passport::actingAs($persona);
        $pais        = $this->paisConStripe();
        $inscripcion = $this->inscripcionImpaga($persona, $pais, ['moneda' => 'BRL']);
        $this->donationPendiente($persona, $inscripcion, 'pi_pix_old', 'pix');

        $this->fakeStripeHttp([
            [['id' => 'pi_card_1', 'object' => 'payment_intent', 'client_secret' => 'pi_card_1_secret', 'status' => 'requires_payment_method'], 200],
        ]);

        $this->postJson('/api/inscripciones/' . $inscripcion->idInscripcion . '/stripe/payment-intent')
            ->assertStatus(200)
            ->assertJson(['intent_id' => 'pi_card_1'])
            ->assertJsonMissing(['pix']);
    }

    /** @test */
    public function error_de_stripe_al_crear_el_intent_devuelve_502()
    {
        $persona = factory('App\Persona')->create();
        Passport::actingAs($persona);
        $pais        = $this->paisConStripe();
        $inscripcion = $this->inscripcionImpaga($persona, $pais);

        // Stripe responde error → el SDK lanza ApiErrorException → 502.
        $this->fakeStripeHttp([
            [['error' => ['type' => 'api_error', 'message' => 'Stripe caído']], 500],
        ]);

        $this->postJson('/api/inscripciones/' . $inscripcion->idInscripcion . '/stripe/payment-intent')
            ->assertStatus(502)
            ->assertJson(['message' => 'No se pudo iniciar el pago.']);

        $this->assertDatabaseMissing('donations', [
            'inscripcion_id' => $inscripcion->idInscripcion,
        ]);
    }

    /** @test */
    public function inscripcion_ya_pagada_devuelve_422()
    {
        $persona = factory('App\Persona')->create();
        Passport::actingAs($persona);
        $pais        = $this->paisConStripe();
        $inscripcion = $this->inscripcionImpaga($persona, $pais);
        $inscripcion->update(['pago' => 1]);

        $this->postJson('/api/inscripciones/' . $inscripcion->idInscripcion . '/stripe/payment-intent')
            ->assertStatus(422)
            ->assertJson(['message' => 'Esta inscripción ya fue pagada.']);
    }

    /** @test */
    public function pais_sin_stripe_configurado_devuelve_422()
    {
        $persona = factory('App\Persona')->create();
        Passport::actingAs($persona);
        $pais        = factory('App\Pais')->create(['config_pago' => json_encode([])]);
        $inscripcion = $this->inscripcionImpaga($persona, $pais);

        $this->postJson('/api/inscripciones/' . $inscripcion->idInscripcion . '/stripe/payment-intent')
            ->assertStatus(422)
            ->assertJson(['message' => 'Este país no tiene Stripe configurado.']);
    }

    /** @test */
    public function no_se_puede_pagar_la_inscripcion_de_otra_persona()
    {
        $persona = factory('App\Persona')->create();
        $otra    = factory('App\Persona')->create();
        Passport::actingAs($persona);
        $pais        = $this->paisConStripe();
        $inscripcion = $this->inscripcionImpaga($otra, $pais); // pertenece a $otra

        // firstOrFail scope-ado por idPersona → 404 para quien no es dueño.
        $this->postJson('/api/inscripciones/' . $inscripcion->idInscripcion . '/stripe/payment-intent')
            ->assertStatus(404);
    }

    /** @test */
    public function requiere_autenticacion()
    {
        $this->postJson('/api/inscripciones/1/stripe/payment-intent')
            ->assertStatus(401);
    }
}
