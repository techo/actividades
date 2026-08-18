<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\ActividadFactory;

/**
 * Regresión — la vista de pago (inscripciones/pagar-paso-1) debe respetar
 * Actividad.metodos_pago (JSON { tarjeta, transferencia, link_pix }).
 *
 * Bug original: la tarjeta (Stripe) se mostraba solo por tener el país Stripe
 * configurado, ignorando metodos_pago.tarjeta → aparecía aunque el coordinador
 * NO habilitó tarjeta para la actividad.
 *
 * Reglas implementadas:
 *  - tarjeta: ESTRICTA (solo si metodos_pago.tarjeta === true; null la oculta).
 *  - transferencia/link: se respeta metodos_pago cuando está definido; null
 *    (legacy) conserva el comportamiento previo (no dejar sin forma de pago).
 *
 * Señal: el panel de tarjeta usa id="pago-content-card", que solo se renderiza
 * cuando la tarjeta está habilitada.
 */
class MetodosPagoVistaTest extends TestCase
{
    use RefreshDatabase;

    private function paisStripe()
    {
        return factory('App\Pais')->create([
            'config_pago' => '{"payment_class":"Stripe","stripe_secret":"sk_test_dummy","stripe_webhook_secret":"whsec_dummy"}',
        ]);
    }

    private function actividadConMetodos($pais, $metodosPago)
    {
        return app(ActividadFactory::class)
            ->conEstado('con pago')
            ->conPais($pais->id)
            ->agregarPuntoConInscriptos(0)
            ->create(['metodos_pago' => $metodosPago]);
    }

    private function verPaginaDePago($actividad)
    {
        $jose = factory('App\Persona')->create(['recibirMails' => 1]);
        factory('App\Inscripcion')->create([
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad'      => $actividad->idActividad,
            'idPersona'        => $jose->idPersona,
        ]);

        return $this->actingAs($jose)
            ->get('/inscripciones/actividad/' . $actividad->idActividad . '/confirmar/donacion');
    }

    /** @test */
    public function tarjeta_no_se_muestra_si_el_coordinador_no_la_habilito()
    {
        $this->seed('PermisosSeeder');
        $pais = $this->paisStripe();
        $actividad = $this->actividadConMetodos($pais, ['tarjeta' => false, 'transferencia' => true, 'link_pix' => false]);

        $this->verPaginaDePago($actividad)
            ->assertOk()
            ->assertDontSee('pago-content-card');
    }

    /** @test */
    public function tarjeta_se_muestra_si_el_coordinador_la_habilito_y_hay_stripe()
    {
        $this->seed('PermisosSeeder');
        $pais = $this->paisStripe();
        $actividad = $this->actividadConMetodos($pais, ['tarjeta' => true, 'transferencia' => false, 'link_pix' => false]);

        $this->verPaginaDePago($actividad)
            ->assertOk()
            ->assertSee('pago-content-card');
    }

    /** @test */
    public function tarjeta_oculta_en_actividad_legacy_con_metodos_pago_null()
    {
        $this->seed('PermisosSeeder');
        $pais = $this->paisStripe();
        // metodos_pago null (actividad previa al rediseño): tarjeta estricta => oculta.
        $actividad = app(ActividadFactory::class)
            ->conEstado('con pago')
            ->conPais($pais->id)
            ->agregarPuntoConInscriptos(0)
            ->create(['metodos_pago' => null]);

        $this->verPaginaDePago($actividad)
            ->assertOk()
            ->assertDontSee('pago-content-card');
    }
}
