<?php

namespace Tests\Feature;

use App\ActividadFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Link del mail de pago abierto logueado con una cuenta SIN inscripción a la
 * actividad (típico: la persona tiene dos cuentas). Antes: firstOrFail →
 * ModelNotFound → pantalla "Error 500 · Algo salió mal". Ahora: redirige al show
 * de la actividad con un aviso que dice con qué cuenta está logueada.
 */
class PagoSinInscripcionTest extends TestCase
{
    use RefreshDatabase;

    private function actividadConPago()
    {
        $pais = factory('App\Pais')->create([
            'config_pago' => '{"payment_class": "DefaultPago"}',
        ]);

        return app(ActividadFactory::class)
            ->conPais($pais->id)
            ->conEstado('con pago')
            ->agregarPuntoConInscriptos(0)
            ->create();
    }

    /** @test */
    public function sin_inscripcion_redirige_al_show_con_aviso_en_vez_de_error()
    {
        $this->seed('PermisosSeeder');
        $actividad = $this->actividadConPago();
        $otraCuenta = factory('App\Persona')->create();

        $this->actingAs($otraCuenta)
            ->get('/inscripciones/actividad/' . $actividad->idActividad . '/confirmar/donacion')
            ->assertRedirect('/actividades/' . $actividad->idActividad)
            ->assertSessionHas('aviso_pago', __('frontend.pago_sin_inscripcion', ['mail' => $otraCuenta->mail]));
    }

    /** @test */
    public function checkout_sin_inscripcion_tambien_redirige()
    {
        $this->seed('PermisosSeeder');
        $actividad = $this->actividadConPago();
        $otraCuenta = factory('App\Persona')->create();

        $this->actingAs($otraCuenta)
            ->post('/inscripciones/actividad/' . $actividad->idActividad . '/confirmar/donacion/checkout', ['monto' => 100])
            ->assertRedirect('/actividades/' . $actividad->idActividad)
            ->assertSessionHas('aviso_pago');
    }

    /** @test */
    public function con_inscripcion_sigue_mostrando_la_pagina_de_pago()
    {
        $this->seed('PermisosSeeder');
        $actividad = $this->actividadConPago();
        $persona = factory('App\Persona')->create();
        factory('App\Inscripcion')->create([
            'idPuntoEncuentro' => $actividad->puntosEncuentro[0]->idPuntoEncuentro,
            'idActividad' => $actividad->idActividad,
            'idPersona' => $persona->idPersona,
            'pago' => 0,
        ]);

        $this->actingAs($persona)
            ->get('/inscripciones/actividad/' . $actividad->idActividad . '/confirmar/donacion')
            ->assertOk()
            ->assertViewIs('inscripciones.pagar-paso-1');
    }
}
