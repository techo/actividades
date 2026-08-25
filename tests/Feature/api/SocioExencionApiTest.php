<?php

namespace Tests\Feature\api;

use App\ActividadFactory;
use App\Donation;
use App\Services\Salesforce\SocioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Mockery;
use Tests\Concerns\FakesStripe;
use Tests\TestCase;

/**
 * Exención de pago por socio — defensa server-side en el endpoint de cobro móvil
 * (InscripcionStripeController@createPaymentIntent).
 *
 * Un socio NUNCA debe llegar a Stripe: el endpoint responde `exento` y no crea
 * PaymentIntent ni Donation. Un no-socio sigue el flujo de cobro normal.
 *
 * SocioService se stubea (la conexión real a Salesforce se cubre por unidad en
 * SocioServiceTest); acá se verifica el comportamiento del controller.
 */
class SocioExencionApiTest extends TestCase
{
    use RefreshDatabase, FakesStripe;

    protected function tearDown(): void
    {
        $this->resetStripeHttpClient();
        Mockery::close();
        parent::tearDown();
    }

    private function stubSocio(bool $esSocio)
    {
        $mock = Mockery::mock(SocioService::class);
        $mock->shouldReceive('esSocio')->andReturn($esSocio);
        $this->app->instance(SocioService::class, $mock);
    }

    private function inscripcionImpagaPaga($persona, $pais)
    {
        $actividad = app(ActividadFactory::class)
            ->conPais($pais->id)
            ->agregarPuntoConInscriptos(0)
            ->create(['pago' => 1, 'montoMin' => 100]);

        return factory('App\Inscripcion')->create([
            'idActividad'      => $actividad->idActividad,
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idPersona'        => $persona->idPersona,
            'pago'             => 0,
        ]);
    }

    /** @test */
    public function socio_no_paga_no_se_crea_payment_intent_ni_donation()
    {
        $this->stubSocio(true);

        $persona = factory('App\Persona')->create();
        Passport::actingAs($persona);
        $pais        = factory('App\Pais')->create(['config_pago' => json_encode(['stripe_secret' => 'sk_test_x'])]);
        $inscripcion = $this->inscripcionImpagaPaga($persona, $pais);

        $this->postJson('/api/inscripciones/' . $inscripcion->idInscripcion . '/stripe/payment-intent')
            ->assertStatus(200)
            ->assertJson(['exento' => true, 'motivo' => 'socio_salesforce']);

        // La inscripción quedó marcada exenta y no se creó ninguna Donation.
        $this->assertDatabaseHas('Inscripcion', [
            'idInscripcion' => $inscripcion->idInscripcion,
            'exento_pago'   => 1,
            'exento_motivo' => 'socio_salesforce',
        ]);
        $this->assertDatabaseMissing('donations', ['inscripcion_id' => $inscripcion->idInscripcion]);
    }

    /** @test */
    public function no_socio_sigue_el_flujo_de_cobro_normal()
    {
        $this->stubSocio(false);

        $persona = factory('App\Persona')->create();
        Passport::actingAs($persona);
        $pais        = factory('App\Pais')->create(['config_pago' => json_encode(['stripe_secret' => 'sk_test_x'])]);
        $inscripcion = $this->inscripcionImpagaPaga($persona, $pais);

        $this->fakeStripeHttp([
            [['id' => 'pi_test_1', 'object' => 'payment_intent', 'client_secret' => 'pi_test_1_secret', 'status' => 'requires_payment_method'], 200],
        ]);

        $this->postJson('/api/inscripciones/' . $inscripcion->idInscripcion . '/stripe/payment-intent')
            ->assertStatus(200)
            ->assertJson(['intent_id' => 'pi_test_1']);

        $this->assertDatabaseHas('donations', [
            'inscripcion_id' => $inscripcion->idInscripcion,
            'status'         => Donation::STATUS_PENDING,
        ]);
        $this->assertDatabaseHas('Inscripcion', [
            'idInscripcion' => $inscripcion->idInscripcion,
            'exento_pago'   => 0,
        ]);
    }
}
