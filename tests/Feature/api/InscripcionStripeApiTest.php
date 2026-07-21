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
