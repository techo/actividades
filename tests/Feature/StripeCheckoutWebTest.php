<?php

namespace Tests\Feature;

use App\ActividadFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\FakesStripe;
use Tests\TestCase;

/**
 * Tarea #30 (Etapa 1) — Cobertura del flujo de cobro Stripe web:
 * StripeController@createCheckout (POST /stripe/{idInscripcion}/checkout).
 *
 * createCheckout llama estáticamente a \Stripe\Checkout\Session::create; se
 * inyecta un HTTP client falso (FakesStripe) para no tocar la red. La
 * confirmación del pago llega por webhook (StripeWebhookWebTest).
 */
class StripeCheckoutWebTest extends TestCase
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

    private function inscripcionImpaga($persona, $pais)
    {
        // Las vistas (pagada / errores 403-404) renderizan el header, que
        // consulta el permiso ver_backoffice; hay que sembrarlo.
        $this->seed('PermisosSeeder');

        $actividad = app(ActividadFactory::class)
            ->conPais($pais->id)
            ->agregarPuntoConInscriptos(0)
            ->create();

        return factory('App\Inscripcion')->create([
            'idActividad'      => $actividad->idActividad,
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idPersona'        => $persona->idPersona,
            'pago'             => 0,
        ]);
    }

    /** @test */
    public function crea_la_checkout_session_guarda_el_id_y_redirige()
    {
        $persona     = factory('App\Persona')->create();
        $pais        = $this->paisConStripe();
        $inscripcion = $this->inscripcionImpaga($persona, $pais);

        $this->fakeStripeHttp([
            [['id' => 'cs_test_1', 'object' => 'checkout.session', 'url' => 'https://checkout.stripe.com/c/pay/cs_test_1'], 200],
        ]);

        $this->actingAs($persona)
            ->post('/stripe/' . $inscripcion->idInscripcion . '/checkout')
            ->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_1');

        $this->assertDatabaseHas('Inscripcion', [
            'idInscripcion'     => $inscripcion->idInscripcion,
            'stripe_session_id' => 'cs_test_1',
        ]);
    }

    /** @test */
    public function no_se_puede_iniciar_el_pago_de_la_inscripcion_de_otro()
    {
        $persona     = factory('App\Persona')->create();
        $otra        = factory('App\Persona')->create();
        $pais        = $this->paisConStripe();
        $inscripcion = $this->inscripcionImpaga($otra, $pais);

        $this->actingAs($persona)
            ->post('/stripe/' . $inscripcion->idInscripcion . '/checkout')
            ->assertStatus(403);
    }

    /** @test */
    public function inscripcion_ya_pagada_no_vuelve_a_cobrar()
    {
        $persona     = factory('App\Persona')->create();
        $pais        = $this->paisConStripe();
        $inscripcion = $this->inscripcionImpaga($persona, $pais);
        $inscripcion->update(['pago' => 1]);

        // No se encola ninguna respuesta de Stripe: si intentara cobrar, el fake
        // client lanzaría por cola vacía. Un 200 sin llamada confirma el short-circuit.
        $this->fakeStripeHttp([]);

        $this->actingAs($persona)
            ->post('/stripe/' . $inscripcion->idInscripcion . '/checkout')
            ->assertStatus(200);

        $this->assertCount(0, $this->fakeStripe->requests);
    }

    /** @test */
    public function pais_sin_stripe_configurado_devuelve_404()
    {
        $persona     = factory('App\Persona')->create();
        $pais        = factory('App\Pais')->create(['config_pago' => json_encode([])]);
        $inscripcion = $this->inscripcionImpaga($persona, $pais);

        $this->actingAs($persona)
            ->post('/stripe/' . $inscripcion->idInscripcion . '/checkout')
            ->assertStatus(404);
    }

    /** @test */
    public function requiere_autenticacion()
    {
        $this->post('/stripe/1/checkout')
            ->assertRedirect(); // guard web → redirige al login
    }
}
